<?php
// models/Pointage.php
class Pointage {
    private $pdo;
    public $entreprise_id;
    private $restricted_lieu_id;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        // Priorité : session admin → session employé → null
        if (isset($_SESSION['entreprise_id'])) {
            $this->entreprise_id = (int)$_SESSION['entreprise_id'];
        } elseif (isset($_SESSION['user_entreprise_id'])) {
            $this->entreprise_id = (int)$_SESSION['user_entreprise_id'];
        }
        $this->restricted_lieu_id = $_SESSION['admin_lieu_id'] ?? null;
    }

    public function getDashboardFeed($user_id = null, $date_start = null, $date_end = null, $limit = 50, $offset = 0) {
        $sql = "SELECT p.*, u.nom, u.prenom, l.nom_lieu 
                FROM pointages p 
                LEFT JOIN users u ON p.user_id = u.id 
                LEFT JOIN lieux l ON p.lieu_id = l.id 
                WHERE p.entreprise_id = ?";
        $params = [$this->entreprise_id];

        $filterSql = "";
        if ($this->restricted_lieu_id) {
            $filterSql .= " AND p.lieu_id = ?";
            $params[] = $this->restricted_lieu_id;
        }
        if (!empty($user_id)) {
            $filterSql .= " AND p.user_id = ?";
            $params[] = $user_id;
        }
        if (!empty($date_start)) {
            $filterSql .= " AND DATE(p.heure_pointage) >= ?";
            $params[] = $date_start;
        }
        if (!empty($date_end)) {
            $filterSql .= " AND DATE(p.heure_pointage) <= ?";
            $params[] = $date_end;
        }

        $sql .= $filterSql . " ORDER BY p.heure_pointage DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countDashboardFeed($user_id = null, $date_start = null, $date_end = null) {
        $sql = "SELECT COUNT(*) FROM pointages p WHERE p.entreprise_id = ?";
        $params = [$this->entreprise_id];

        if ($this->restricted_lieu_id) {
            $sql .= " AND p.lieu_id = ?";
            $params[] = $this->restricted_lieu_id;
        }
        if (!empty($user_id)) {
            $sql .= " AND p.user_id = ?";
            $params[] = $user_id;
        }
        if (!empty($date_start)) {
            $sql .= " AND DATE(p.heure_pointage) >= ?";
            $params[] = $date_start;
        }
        if (!empty($date_end)) {
            $sql .= " AND DATE(p.heure_pointage) <= ?";
            $params[] = $date_end;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    public function getLastPointageToday($user_id) {
        $stmt = $this->pdo->prepare("SELECT type_mouvement FROM pointages WHERE user_id = ? AND entreprise_id = ? AND DATE(heure_pointage) = CURDATE() ORDER BY heure_pointage DESC LIMIT 1");
        $stmt->execute([$user_id, $this->entreprise_id]);
        return $stmt->fetch();
    }

    public function countPresentsToday() {
        $sql = "SELECT COUNT(*) as total FROM (
                    SELECT p.user_id, p.type_mouvement
                    FROM pointages p
                    INNER JOIN (
                        SELECT user_id, MAX(heure_pointage) as max_heure
                        FROM pointages
                        WHERE entreprise_id = ? AND DATE(heure_pointage) = CURDATE()
                        GROUP BY user_id
                    ) latest ON p.user_id = latest.user_id AND p.heure_pointage = latest.max_heure
                    JOIN users u ON p.user_id = u.id
                    WHERE p.entreprise_id = ?";
        $params = [$this->entreprise_id, $this->entreprise_id];
        
        if ($this->restricted_lieu_id) {
            $sql .= " AND u.lieu_id = ?";
            $params[] = $this->restricted_lieu_id;
        }
        $sql .= " ) as subquery WHERE type_mouvement = 'ARRIVEE'";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result ? (int)$result['total'] : 0;
    }

    public function create($user_id, $lieu_id, $type, $lat, $lng, $distance, $offline_ts = null, $is_anomaly = 0) {
        if (empty($this->entreprise_id)) {
            throw new Exception("entreprise_id manquant dans la session. Reconnectez-vous.");
        }
        $sql = "INSERT INTO pointages (entreprise_id, user_id, lieu_id, type_mouvement, gps_detecte_lat, gps_detecte_lng, distance_calculee, is_anomaly, heure_pointage, offline_timestamp) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        
        $final_time = $offline_ts ? date('Y-m-d H:i:s', strtotime($offline_ts)) : date('Y-m-d H:i:s');
        return $stmt->execute([$this->entreprise_id, $user_id, $lieu_id, $type, $lat, $lng, $distance, $is_anomaly, $final_time, $offline_ts]);
    }
    
    public function addJustification($user_id, $date_justif, $type) {
        $stmt = $this->pdo->prepare("INSERT IGNORE INTO justifications (entreprise_id, user_id, date_justif, type) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$this->entreprise_id, $user_id, $date_justif, $type]);
    }

    public function getXtimesDaily($date, $limit = null, $offset = 0, $user_id = null) {
        $dayOfWeek = date('N', strtotime($date));

        $sql = "SELECT u.id, u.nom, u.prenom, l.nom_lieu, l.tolerance_retard, u.lieu_id 
                FROM users u 
                LEFT JOIN lieux l ON u.lieu_id = l.id 
                WHERE u.is_active = 1 AND u.entreprise_id = ?";
        $params = [$this->entreprise_id];
        
        if ($this->restricted_lieu_id) { $sql .= " AND u.lieu_id = ?"; $params[] = $this->restricted_lieu_id; }
        if ($user_id) { $sql .= " AND u.id = ?"; $params[] = $user_id; }
        
        $sql .= " ORDER BY u.nom";
        if ($limit !== null) { $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset; }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll();

        $stmtPlanning = $this->pdo->prepare("SELECT lieu_id, is_repos, heure_debut, heure_fin FROM planning_lieux WHERE entreprise_id = ? AND jour_semaine = ?");
        $stmtPlanning->execute([$this->entreprise_id, $dayOfWeek]);
        $plannings = [];
        while($plan = $stmtPlanning->fetch()) $plannings[$plan['lieu_id']] = $plan;

        // Fallback pour les nouveaux sites ou sites sans planning configuré
        $defaultPlan = [
            'is_repos' => ($dayOfWeek >= 6) ? 1 : 0,
            'heure_debut' => '08:00',
            'heure_fin' => '18:00'
        ];

        $stmtPtgs = $this->pdo->prepare("SELECT user_id, type_mouvement, heure_pointage FROM pointages WHERE entreprise_id = ? AND DATE(heure_pointage) = ?");
        $stmtPtgs->execute([$this->entreprise_id, $date]);
        $pointagesByUsers = [];
        while($ptg = $stmtPtgs->fetch()) {
            $uid = $ptg['user_id'];
            if(!isset($pointagesByUsers[$uid])) $pointagesByUsers[$uid] = ['ARRIVEE' => null, 'DEPART' => null];
            
            if($ptg['type_mouvement'] === 'ARRIVEE' && $pointagesByUsers[$uid]['ARRIVEE'] === null) {
                $pointagesByUsers[$uid]['ARRIVEE'] = $ptg['heure_pointage'];
            }
            if($ptg['type_mouvement'] === 'DEPART') {
                 if ($pointagesByUsers[$uid]['DEPART'] === null || $ptg['heure_pointage'] > $pointagesByUsers[$uid]['DEPART']) {
                     $pointagesByUsers[$uid]['DEPART'] = $ptg['heure_pointage'];
                 }
            }
        }

        $stmtJustifs = $this->pdo->prepare("SELECT user_id, type FROM justifications WHERE entreprise_id = ? AND date_justif = ?");
        $stmtJustifs->execute([$this->entreprise_id, $date]);
        $justifsByUsers = [];
        while($j = $stmtJustifs->fetch()) $justifsByUsers[$j['user_id']][] = $j['type'];

        $results = [];
        $isTodayOrPast = strtotime($date) <= strtotime(date('Y-m-d'));

        foreach($users as $u) {
            $lid = $u['lieu_id'];
            $plan = $plannings[$lid] ?? $defaultPlan;
            $isScheduled = ($plan && !$plan['is_repos']);
            $hasPtgs = isset($pointagesByUsers[$u['id']]);

            if(!$isScheduled && !$hasPtgs) continue; 

            $arr = $hasPtgs ? $pointagesByUsers[$u['id']]['ARRIVEE'] : null;
            $dep = $hasPtgs ? $pointagesByUsers[$u['id']]['DEPART'] : null;
            $justifs = $justifsByUsers[$u['id']] ?? [];
            
            $statut = "-";
            $statut_color = "grey";
            $besoin_justif = null;

            if ($arr) { 
                if ($isScheduled) {
                    $start_scheduled = strtotime($plan['heure_debut']);
                    $arr_time = strtotime(date('H:i:s', strtotime($arr)));
                    $tolerance = (int)($u['tolerance_retard'] ?? 0) * 60;
                    
                    if ($arr_time > ($start_scheduled + $tolerance)) {
                        if (in_array('RETARD', $justifs)) {
                            $statut = "Retard justifié"; $statut_color = "#3b82f6";
                        } else {
                            $statut = "En retard"; $statut_color = "#ef4444"; $besoin_justif = 'RETARD';
                        }
                    } else {
                        $statut = "A l'heure"; $statut_color = "#10b981";
                    }
                } else {
                    $statut = "Non planifié"; $statut_color = "#f59e0b";
                }
            } else { 
                if ($isScheduled && $isTodayOrPast) {
                    if (in_array('ABSENCE', $justifs)) {
                        $statut = "Absence justifiée"; $statut_color = "#3b82f6";
                    } else if ($date == date('Y-m-d') && date('H:i:s') < date('H:i:s', strtotime($plan['heure_debut']))) {
                        $statut = "À venir"; $statut_color = "#64748b";
                    } else {
                        $statut = "Absent"; $statut_color = "#b91c1c"; $besoin_justif = 'ABSENCE';
                    }
                }
            }

            $results[] = [
                'user_id' => $u['id'],
                'nom' => $u['nom'] . ' ' . $u['prenom'],
                'nom_lieu' => $u['nom_lieu'],
                'heure_entree' => $arr ? date('H:i', strtotime($arr)) : '--:--',
                'heure_depart' => $dep ? date('H:i', strtotime($dep)) : '--:--',
                'statut' => $statut,
                'statut_color' => $statut_color,
                'besoin_justif' => $besoin_justif
            ];
        }
        return $results;
    }

    /**
     * Génère un rapport complet sur une période donnée
     */
    public function getPeriodReport($date_start, $date_end, $user_id = null) {
        // 1. Charger les utilisateurs concernés
        $sqlUsers = "SELECT u.id, u.nom, u.prenom, l.nom_lieu, l.tolerance_retard, u.lieu_id 
                     FROM users u 
                     LEFT JOIN lieux l ON u.lieu_id = l.id 
                     WHERE u.is_active = 1 AND u.entreprise_id = ?";
        $paramsUsers = [$this->entreprise_id];
        if ($this->restricted_lieu_id) { $sqlUsers .= " AND u.lieu_id = ?"; $paramsUsers[] = $this->restricted_lieu_id; }
        if ($user_id) { $sqlUsers .= " AND u.id = ?"; $paramsUsers[] = $user_id; }
        
        $stmtUsers = $this->pdo->prepare($sqlUsers);
        $stmtUsers->execute($paramsUsers);
        $users = $stmtUsers->fetchAll();

        // 2. Pré-charger TOUS les plannings de l'entreprise (7 jours par site max)
        $stmtPlan = $this->pdo->prepare("SELECT lieu_id, jour_semaine, is_repos, heure_debut, heure_fin FROM planning_lieux WHERE entreprise_id = ?");
        $stmtPlan->execute([$this->entreprise_id]);
        $allPlannings = [];
        while($p = $stmtPlan->fetch()) {
            $allPlannings[$p['lieu_id']][$p['jour_semaine']] = $p;
        }

        // 3. Charger TOUS les pointages de la période entière (Optimisation SQL via index suggéré)
        $stmtPtgs = $this->pdo->prepare("SELECT user_id, type_mouvement, heure_pointage, DATE(heure_pointage) as date_p 
                                        FROM pointages 
                                        WHERE entreprise_id = ? AND DATE(heure_pointage) BETWEEN ? AND ?");
        $stmtPtgs->execute([$this->entreprise_id, $date_start, $date_end]);
        $pointagesByDateUser = [];
        while($ptg = $stmtPtgs->fetch()) {
            $d = $ptg['date_p'];
            $uid = $ptg['user_id'];
            if(!isset($pointagesByDateUser[$d][$uid])) $pointagesByDateUser[$d][$uid] = ['ARRIVEE' => null, 'DEPART' => null];
            
            if($ptg['type_mouvement'] === 'ARRIVEE' && $pointagesByDateUser[$d][$uid]['ARRIVEE'] === null) {
                $pointagesByDateUser[$d][$uid]['ARRIVEE'] = $ptg['heure_pointage'];
            }
            if($ptg['type_mouvement'] === 'DEPART') {
                 if ($pointagesByDateUser[$d][$uid]['DEPART'] === null || $ptg['heure_pointage'] > $pointagesByDateUser[$d][$uid]['DEPART']) {
                     $pointagesByDateUser[$d][$uid]['DEPART'] = $ptg['heure_pointage'];
                 }
            }
        }

        // 4. Charger TOUTES les justifications de la période
        $stmtJustifs = $this->pdo->prepare("SELECT user_id, date_justif, type FROM justifications WHERE entreprise_id = ? AND date_justif BETWEEN ? AND ?");
        $stmtJustifs->execute([$this->entreprise_id, $date_start, $date_end]);
        $justifsByDateUser = [];
        while($j = $stmtJustifs->fetch()) {
            $justifsByDateUser[$j['date_justif']][$j['user_id']][] = $j['type'];
        }

        // 5. Génération du rapport via traitement mémoire (PHP)
        $start = new DateTime($date_start);
        $end = new DateTime($date_end);
        $interval = new DateInterval('P1D');
        $dateRange = new DatePeriod($start, $interval, $end->modify('+1 day'));

        $allData = [];
        $today = date('Y-m-d');

        foreach ($dateRange as $dateObj) {
            $dateStr = $dateObj->format('Y-m-d');
            $dayOfWeek = $dateObj->format('N');
            $isTodayOrPast = strtotime($dateStr) <= strtotime($today);

            foreach($users as $u) {
                $uid = $u['id'];
                $lid = $u['lieu_id'];
                
                // Résolution du planning (avec fallback par défaut si non configuré)
                $plan = $allPlannings[$lid][$dayOfWeek] ?? [
                    'is_repos' => ($dayOfWeek >= 6) ? 1 : 0,
                    'heure_debut' => '08:00',
                    'heure_fin' => '18:00'
                ];
                
                $isScheduled = ($plan && !$plan['is_repos']);
                $ptgs = $pointagesByDateUser[$dateStr][$uid] ?? null;
                $hasPtgs = ($ptgs !== null);

                // On n'affiche que les journées planifiées OU celles où il y a eu un mouvement
                if(!$isScheduled && !$hasPtgs) continue;

                $arr = $hasPtgs ? $ptgs['ARRIVEE'] : null;
                $dep = $hasPtgs ? $ptgs['DEPART'] : null;
                $justifs = $justifsByDateUser[$dateStr][$uid] ?? [];
                
                $statut = "-";
                $statut_color = "grey";
                $besoin_justif = null;

                if ($arr) { 
                    if ($isScheduled) {
                        $start_scheduled = strtotime($plan['heure_debut']);
                        $arr_time = strtotime(date('H:i:s', strtotime($arr)));
                        $tolerance = (int)($u['tolerance_retard'] ?? 0) * 60;
                        
                        if ($arr_time > ($start_scheduled + $tolerance)) {
                            if (in_array('RETARD', $justifs)) {
                                $statut = "Retard justifié"; $statut_color = "#3b82f6";
                            } else {
                                $statut = "En retard"; $statut_color = "#ef4444"; $besoin_justif = 'RETARD';
                            }
                        } else {
                            $statut = "A l'heure"; $statut_color = "#10b981";
                        }
                    } else {
                        $statut = "Non planifié"; $statut_color = "#f59e0b";
                    }
                } else { 
                    if ($isScheduled && $isTodayOrPast) {
                        if (in_array('ABSENCE', $justifs)) {
                            $statut = "Absence justifiée"; $statut_color = "#3b82f6";
                        } else if ($dateStr == $today && date('H:i:s') < date('H:i:s', strtotime($plan['heure_debut']))) {
                            $statut = "À venir"; $statut_color = "#64748b";
                        } else {
                            $statut = "Absent"; $statut_color = "#b91c1c"; $besoin_justif = 'ABSENCE';
                        }
                    }
                }

                $allData[] = [
                    'date' => $dateStr,
                    'user_id' => $uid,
                    'nom' => $u['nom'] . ' ' . $u['prenom'],
                    'nom_lieu' => $u['nom_lieu'],
                    'heure_entree' => $arr ? date('H:i', strtotime($arr)) : '--:--',
                    'heure_depart' => $dep ? date('H:i', strtotime($dep)) : '--:--',
                    'statut' => $statut,
                    'statut_color' => $statut_color,
                    'besoin_justif' => $besoin_justif
                ];
            }
        }

        // Trier par date décroissante pour l'affichage
        usort($allData, function($a, $b) {
            return strcmp($b['date'], $a['date']);
        });

        return $allData;
    }
    
    public function getMonthlyPayrollReport($month, $year, $lieu_id = null) {
        $sql = "SELECT u.id as id, u.nom, u.prenom, u.salaire_mensuel, u.lieu_id, u.created_at, l.nom_lieu, l.tolerance_retard 
                FROM users u 
                LEFT JOIN lieux l ON u.lieu_id = l.id 
                WHERE u.is_active = 1 AND u.entreprise_id = ?";
        
        $params = [$this->entreprise_id];
        
        // Priorité à la restriction manager sur le filtre UI
        $finalLieuId = $this->restricted_lieu_id ?: $lieu_id;

        if (!empty($finalLieuId)) {
            $sql .= " AND u.lieu_id = ?";
            $params[] = $finalLieuId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll();

        // 1. Pré-charger tous les plannings
        $stmt = $this->pdo->prepare("SELECT * FROM planning_lieux WHERE entreprise_id = ?");
        $stmt->execute([$this->entreprise_id]);
        $plannings = [];
        while($row = $stmt->fetch()) {
            $plannings[$row['lieu_id']][$row['jour_semaine']] = $row;
        }

        // 2. Pré-charger tous les premiers pointages du mois (Optimisé pour index)
        $startDate = sprintf('%04d-%02d-01 00:00:00', $year, $month);
        $endDate = date('Y-m-t 23:59:59', strtotime($startDate));
        
        $sql = "SELECT user_id, DATE(heure_pointage) as jour, MIN(heure_pointage) as premiere_arrivee 
                FROM pointages 
                WHERE entreprise_id = ? AND heure_pointage BETWEEN ? AND ? AND type_mouvement = 'ARRIVEE'
                GROUP BY user_id, DATE(heure_pointage)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$this->entreprise_id, $startDate, $endDate]);
        $allPointages = [];
        while($p = $stmt->fetch()) {
            $allPointages[$p['user_id']][] = $p;
        }

        // 3. Pré-charger tous les ajustements manuels du mois
        $stmtAdj = $this->pdo->prepare("SELECT * FROM payroll_adjustments WHERE entreprise_id = ? AND month = ? AND year = ?");
        $stmtAdj->execute([$this->entreprise_id, $month, $year]);
        $allAdjustments = [];
        while($adj = $stmtAdj->fetch()) {
            $allAdjustments[$adj['user_id']] = $adj;
        }

        // 4. Pré-charger les justifications
        $stmtJustifs = $this->pdo->prepare("SELECT user_id, date_justif, type FROM justifications WHERE entreprise_id = ? AND date_justif BETWEEN ? AND ?");
        $stmtJustifs->execute([$this->entreprise_id, $startDate, $endDate]);
        $allJustifs = [];
        while($j = $stmtJustifs->fetch()) $allJustifs[$j['user_id']][$j['date_justif']][] = $j['type'];

        $report = [];
        require_once 'utils/PayrollCalculator.php';
        $memoTheoretical = [];
        $daysInMonth = (int)date('t', strtotime("$year-$month-01"));

        foreach ($users as $u) {
            $lid = $u['lieu_id'];
            if (!isset($memoTheoretical[$lid])) {
                $memoTheoretical[$lid] = PayrollCalculator::getTheoreticalHoursFromData($plannings[$lid] ?? [], $month, $year);
            }
            $theoreticalHours = $memoTheoretical[$lid];

            $totalDelaySeconds = 0;
            $userPointages = $allPointages[$u['id']] ?? [];
            $ptsByDay = [];
            foreach($userPointages as $p) $ptsByDay[$p['jour']] = $p['premiere_arrivee'];

            $lieuPlanning = $plannings[$lid] ?? [];
            $userJustifs = $allJustifs[$u['id']] ?? [];
            
            // --- LOGIQUE SMART START : Ignorer absences avant le 1er pointage (Mois de recrutement) ---
            $isCreationMonth = (!empty($u['created_at']) && date('m', strtotime($u['created_at'])) == $month && date('Y', strtotime($u['created_at'])) == $year);
            $firstPtgDate = null;
            if (!empty($userPointages)) {
                $days = array_column($userPointages, 'jour');
                $firstPtgDate = min($days);
            }

            for ($d=1; $d <= $daysInMonth; $d++) {
                $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $d);
                if (strtotime($dateStr) > time()) continue; // Skip days in the future

                // Si c'est le mois de création, on ignore tout avant le 1er geste de l'employé
                if ($isCreationMonth) {
                    if ($firstPtgDate === null || $dateStr < $firstPtgDate) {
                        continue;
                    }
                }
                
                $dayOfWeek = date('N', strtotime($dateStr));
                if (isset($lieuPlanning[$dayOfWeek]) && !$lieuPlanning[$dayOfWeek]['is_repos']) {
                    $isJustifiedRetard = in_array('RETARD', $userJustifs[$dateStr] ?? []);
                    $isJustifiedAbsence = in_array('ABSENCE', $userJustifs[$dateStr] ?? []);
                    
                    if (isset($ptsByDay[$dateStr])) {
                        if (!$isJustifiedRetard) {
                            $schedStart = $lieuPlanning[$dayOfWeek]['heure_debut'];
                            $delay = PayrollCalculator::calculateDelaySeconds($ptsByDay[$dateStr], $schedStart);
                            $tolerance = (int)($u['tolerance_retard'] ?? 0) * 60;
                            if ($delay > $tolerance) {
                                $totalDelaySeconds += ($delay - $tolerance);
                            }
                        }
                    } else {
                        // Absent
                        if (!$isJustifiedAbsence) {
                            $schedStart = strtotime($lieuPlanning[$dayOfWeek]['heure_debut']);
                            $schedEnd = strtotime($lieuPlanning[$dayOfWeek]['heure_fin']);
                            $totalDelaySeconds += ($schedEnd - $schedStart);
                        }
                    }
                }
            }
            
            $tauxHoraire = $theoreticalHours > 0 ? ($u['salaire_mensuel'] / $theoreticalHours) : 0;
            $retenue_retard = ($totalDelaySeconds / 3600) * $tauxHoraire;

            // Intégrer les ajustements
            $adj = $allAdjustments[$u['id']] ?? ['amount_primes' => 0, 'amount_retenues' => 0];
            $net = $u['salaire_mensuel'] + $adj['amount_primes'] - $retenue_retard - $adj['amount_retenues'];

            $report[] = [
                'user' => $u,
                'theoretical_hours' => $theoreticalHours,
                'total_delay_hours' => $totalDelaySeconds / 3600,
                'taux_horaire' => $tauxHoraire,
                'retenue_retard' => $retenue_retard,
                'primes' => $adj['amount_primes'],
                'retenues_manuelles' => $adj['amount_retenues'],
                'net_a_payer' => $net
            ];
        }
        return $report;
    }
}
