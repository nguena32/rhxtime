<?php
class AdminController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        if (!isset($_SESSION['admin_id'])) {
            header('Location: index.php?page=login');
            exit;
        }
    }

    public function dashboard() {
        $user_id = $_GET['user_id'] ?? '';
        $date_start = $_GET['date_start'] ?? '';
        $date_end = $_GET['date_end'] ?? '';
        
        $page = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
        $limit = 50;
        $offset = ($page - 1) * $limit;

        $pointageModel = new Pointage($this->pdo);
        $userModel = new User($this->pdo);
        $lieuModel = new Lieu($this->pdo);

        $totalHistory = $pointageModel->countDashboardFeed($user_id, $date_start, $date_end);
        $totalPages = ceil($totalHistory / $limit);

        $feed = $pointageModel->getDashboardFeed($user_id, $date_start, $date_end, $limit, $offset);
        $nb_users = $userModel->countActive();
        $nb_lieux = $lieuModel->countAll();
        
        // --- NOUVEAUX COMPTEURS DASHBOARD (AVEC CACHE 5 MIN) ---
        $cacheKey = "dashboard_counters_" . $_SESSION['entreprise_id'] . "_" . date('Y-m-d');
        $cachedCounters = class_exists('Cache') ? Cache::get($cacheKey, 300) : false;

        if ($cachedCounters !== false) {
            $nb_presents = $cachedCounters['presents'];
            $nb_departures = $cachedCounters['departures'];
            $nb_lates = $cachedCounters['lates'];
            $nb_absents = $cachedCounters['absents'];
        } else {
            $todayXtimes = $pointageModel->getXtimesDaily(date('Y-m-d'));
            $nb_presents = 0;
            $nb_departures = 0;
            $nb_lates = 0;
            $nb_absents = 0;

            foreach($todayXtimes as $row) {
                if ($row['heure_entree'] !== '--:--') $nb_presents++;
                if ($row['heure_depart'] !== '--:--') $nb_departures++;
                if ($row['statut'] === 'En retard' || $row['statut'] === 'Retard justifié') $nb_lates++;
                if ($row['statut'] === 'Absent' || $row['statut'] === 'Absence justifiée') $nb_absents++;
            }
            if (class_exists('Cache')) {
                Cache::set($cacheKey, [
                    'presents' => $nb_presents,
                    'departures' => $nb_departures,
                    'lates' => $nb_lates,
                    'absents' => $nb_absents
                ]);
            }
        }
        // -------------------------------------
        
        try {
            $all_users = $userModel->getAllBasic();
        } catch(Exception $e) { $all_users = []; }

        require 'views/admin/dashboard.php';
    }

    public function reportView() {
        $user_id = $_GET['user_id'] ?? null;
        $date_start = $_GET['date_start'] ?? date('Y-m-d', strtotime('-7 days'));
        $date_end = $_GET['date_end'] ?? date('Y-m-d');

        $pointageModel = new Pointage($this->pdo);
        $userModel = new User($this->pdo);
        
        $reportData = $pointageModel->getPeriodReport($date_start, $date_end, $user_id);
        
        // Filtrage manuel par statut (puisque getPeriodReport agrège des résultats quotidiens)
        $status_filter = $_GET['status_filter'] ?? null;
        if (!empty($status_filter)) {
            $reportData = array_filter($reportData, function($row) use ($status_filter) {
                return $row['statut'] === $status_filter;
            });
        }

        try {
            $all_users = $userModel->getAllBasic();
        } catch(Exception $e) { $all_users = []; }
        
        require 'views/admin/reports.php';
    }

    public function listUsers() {
        $page = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
        $limit = 50;
        $offset = ($page - 1) * $limit;
        
        $search = $_GET['search'] ?? '';

        $lieuModel = new Lieu($this->pdo);
        $userModel = new User($this->pdo);
        
        $totalUsers = $userModel->countAll($search);
        $totalPages = ceil($totalUsers / $limit);

        $lieux = $lieuModel->getAll();
        $users = $userModel->getAllWithLieu($limit, $offset, $search);

        // Fetch quotas for Employees
        $stmtPlan = $this->pdo->prepare("SELECT p.max_employees FROM entreprises e JOIN plans p ON e.plan_id = p.id WHERE e.id = ?");
        $stmtPlan->execute([$_SESSION['entreprise_id']]);
        $maxEmployees = $stmtPlan->fetchColumn() ?: 3;
        $currentEmployees = $userModel->countAll('');

        require 'views/admin/users.php';
    }

    public function listLieux() {
        $pdo = $this->pdo; // Requis par la vue pour vérifier le forfait
        $lieuModel = new Lieu($this->pdo);
        $lieux = $lieuModel->getAll();

        // Fetch quotas for Sites
        $stmtPlan = $this->pdo->prepare("SELECT p.max_sites FROM entreprises e JOIN plans p ON e.plan_id = p.id WHERE e.id = ?");
        $stmtPlan->execute([$_SESSION['entreprise_id']]);
        $maxSites = $stmtPlan->fetchColumn() ?: 1;
        $currentSites = $lieuModel->countAll();

        require 'views/admin/lieux.php';
    }

    public function listAdmins() {
        // Seul le propriétaire peut gérer les autres admins
        if (($_SESSION['user_role'] ?? '') !== 'owner') {
            header('Location: index.php?page=admin_dashboard&error=access_denied');
            exit;
        }

        $adminModel = new Admin($this->pdo);
        $lieuModel = new Lieu($this->pdo);

        $admins = $adminModel->getAllByEntreprise();
        $lieux = $lieuModel->getAll();

        // Fetch quotas for Managers
        $stmtPlan = $this->pdo->prepare("SELECT p.max_managers FROM entreprises e JOIN plans p ON e.plan_id = p.id WHERE e.id = ?");
        $stmtPlan->execute([$_SESSION['entreprise_id']]);
        $maxManagers = $stmtPlan->fetchColumn() ?: 1;
        
        $stmtCount = $this->pdo->prepare("SELECT COUNT(*) FROM admins WHERE entreprise_id = ?");
        $stmtCount->execute([$_SESSION['entreprise_id']]);
        $currentManagers = $stmtCount->fetchColumn();

        require 'views/admin/admins.php';
    }

    public function createAdmin() {
        Security::enforceCsrfGuard();
        if (($_SESSION['user_role'] ?? '') !== 'owner') {
            header('Location: index.php?page=admin_dashboard&error=access_denied');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $password = $_POST['password'];
            $role = $_POST['role'] ?? 'manager';
            $lieu_id = !empty($_POST['lieu_id']) ? $_POST['lieu_id'] : null;

            try {
                $this->pdo->beginTransaction();

                // Lock for quota check
                $stmtLock = $this->pdo->prepare("SELECT plan_id FROM entreprises WHERE id = ? FOR UPDATE");
                $stmtLock->execute([$_SESSION['entreprise_id']]);

                $stmtPlan = $this->pdo->prepare("SELECT p.* FROM entreprises e JOIN plans p ON e.plan_id = p.id WHERE e.id = ?");
                $stmtPlan->execute([$_SESSION['entreprise_id']]);
                $planDetails = $stmtPlan->fetch();

                $limit = $planDetails['max_managers'] ?? 1;

                $stmtCount = $this->pdo->prepare("SELECT COUNT(*) FROM admins WHERE entreprise_id = ?");
                $stmtCount->execute([$_SESSION['entreprise_id']]);
                $countManagers = $stmtCount->fetchColumn();

                if ($countManagers >= $limit) {
                    $this->pdo->rollBack();
                    $errorMsg = "Limite du plan atteinte ($limit managers maximum).";
                    header('Location: index.php?page=admin_admins&error=' . urlencode($errorMsg));
                    exit;
                }

                // --- VÉRIFICATION DOUBLON EMAIL GLOBAL ---
                $stmtCheckEmail = $this->pdo->prepare("SELECT id FROM admins WHERE email = ?");
                $stmtCheckEmail->execute([$email]);
                if ($stmtCheckEmail->fetch()) {
                    $this->pdo->rollBack();
                    header('Location: index.php?page=admin_admins&error=' . urlencode("Cet email est déjà utilisé par un autre administrateur."));
                    exit;
                }

                $adminModel = new Admin($this->pdo);
                $adminModel->create($email, $password, $role, $lieu_id);
                $this->pdo->commit();

                header('Location: index.php?page=admin_admins&success=admin_created');
                exit;
            } catch (Exception $e) {
                if ($this->pdo->inTransaction()) $this->pdo->rollBack();
                error_log($e->getMessage());
                header('Location: index.php?page=admin_admins&error=Erreur technique');
                exit;
            }
        }
    }

    public function deleteAdmin() {
        Security::verifyCsrfGet();
        if (($_SESSION['user_role'] ?? '') !== 'owner') {
            header('Location: index.php?page=admin_dashboard&error=access_denied');
            exit;
        }
        if (isset($_GET['id'])) {
            $adminModel = new Admin($this->pdo);
            $adminModel->delete($_GET['id']);
            header('Location: index.php?page=admin_admins&success=deleted');
            exit;
        }
    }

    public function xtimesPointage() {
        $dateFilter = $_GET['date'] ?? date('Y-m-d');
        
        $page = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
        $limit = 50;
        $offset = ($page - 1) * $limit;

        $pointageModel = new Pointage($this->pdo);
        $userModel = new User($this->pdo);

        $totalUsers = $userModel->countAll(''); // Ou countActive si disponible
        $totalPages = ceil($totalUsers / $limit);

        $lignes = $pointageModel->getXtimesDaily($dateFilter, $limit, $offset);
        require 'views/admin/xtimes.php';
    }

    public function justifyAbsence() {
        Security::enforceCsrfGuard();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user_id = $_POST['user_id'];
            $date = $_POST['date'];
            $type = $_POST['type']; // RETARD ou ABSENCE
            $pointageModel = new Pointage($this->pdo);
            $pointageModel->addJustification($user_id, $date, $type);
            header("Location: index.php?page=admin_xtimes&date=$date&success=justified");
            exit;
        }
    }

    public function messagesView() {
        require 'views/admin/messages.php';
    }

    public function sendMessage() {
        require_once 'models/Message.php';
        Security::enforceCsrfGuard();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['content']) && !empty($_POST['user_id'])) {
            $msgModel = new Message($this->pdo);
            $msgModel->sendMessage($_POST['user_id'], 'to_employee', $_POST['content']);
            header('Location: index.php?page=admin_messages&user_id=' . $_POST['user_id']);
            exit;
        }
        header('Location: index.php?page=admin_messages');
        exit;
    }

    public function createUser() {
        Security::enforceCsrfGuard();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = $_POST['nom'];
            $prenom = $_POST['prenom'];
            $fonction = $_POST['fonction'];
            $tel = $_POST['telephone'];
            $email = !empty($_POST['email']) ? $_POST['email'] : null;
            $lieu_id = $_POST['lieu_id'];
            $salaire = $_POST['salaire_mensuel'] ?? 0;
            $pass = password_hash($_POST['password'], PASSWORD_ARGON2ID);

            if (empty($lieu_id)) {
                header('Location: index.php?page=admin_users&error=Lieu manquant');
                exit;
            }

            // --- VÉRIFICATION LIMITES DU FORFAIT (SAAS DYNAMIQUE AVEC TRANSACTION) ---
            try {
                $this->pdo->beginTransaction();
                
                // Verrouiller la ligne entreprise pour éviter la Race Condition sur le quota
                $stmtLock = $this->pdo->prepare("SELECT plan_id FROM entreprises WHERE id = ? FOR UPDATE");
                $stmtLock->execute([$_SESSION['entreprise_id']]);

                $stmtPlan = $this->pdo->prepare("SELECT p.* FROM entreprises e JOIN plans p ON e.plan_id = p.id WHERE e.id = ?");
                $stmtPlan->execute([$_SESSION['entreprise_id']]);
                $planDetails = $stmtPlan->fetch();
                
                $planName = $planDetails['nom'] ?? 'Starter';
                $limit = $planDetails['max_employees'] ?? 3;

                $userModel = new User($this->pdo);
                $countEmployees = $userModel->countActive();

                if ($countEmployees >= $limit) {
                    $this->pdo->rollBack();
                    $errorMsg = "Limite du plan $planName atteinte ($limit employés maximum). Veuillez upgrader vers un plan supérieur via la page Billing.";
                    header('Location: index.php?page=admin_users&error=' . urlencode($errorMsg));
                    exit;
                }

                // --- VÉRIFICATION DOUBLON GLOBAL AVANT CRÉATION ---
                $stmtCheck = $this->pdo->prepare("SELECT id FROM users WHERE (telephone = ? OR (email IS NOT NULL AND email = ?))");
                $stmtCheck->execute([$tel, $email]);
                if ($stmtCheck->fetch()) {
                    $this->pdo->rollBack();
                    header('Location: index.php?page=admin_users&error=' . urlencode("Ce numéro de téléphone ou cet email est déjà utilisé par un employé sur la plateforme."));
                    exit;
                }

                $userModel->create($nom, $prenom, $fonction, $tel, $email, $pass, $lieu_id, $salaire);
                $this->pdo->commit();

                header('Location: index.php?page=admin_users&success=user_created');
                exit;
            } catch (Exception $e) {
                if ($this->pdo->inTransaction()) $this->pdo->rollBack();
                error_log($e->getMessage());
                $rawError = $e->getMessage();
                $msg = "Erreur technique lors de la création.";
                
                if (strpos($rawError, 'Duplicate entry') !== false) {
                    $msg = "Erreur de base de données (Doublon détecté).";
                    Security::logAnomaly("Echec création (Doublon) : $rawError");
                } else {
                    Security::logAnomaly("Echec création (Quotas/DB) : $rawError");
                }
                header('Location: index.php?page=admin_users&error=' . urlencode($msg));
                exit;
            }
        }
    }

    public function updateUser() {
        Security::enforceCsrfGuard();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $nom = $_POST['nom'];
            $prenom = $_POST['prenom'];
            $fonction = $_POST['fonction'];
            $tel = $_POST['telephone'];
            $email = !empty($_POST['email']) ? $_POST['email'] : null;
            $lieu_id = $_POST['lieu_id'];
            $salaire = $_POST['salaire_mensuel'] ?? 0;

            try {
                $userModel = new User($this->pdo);

                // --- VÉRIFICATION DOUBLON GLOBAL AVANT MISE À JOUR ---
                $stmtCheck = $this->pdo->prepare("SELECT id FROM users WHERE (telephone = ? OR (email IS NOT NULL AND email = ?)) AND id != ?");
                $stmtCheck->execute([$tel, $email, $id]);
                if ($stmtCheck->fetch()) {
                    header('Location: index.php?page=admin_users&error=' . urlencode("Ce numéro de téléphone ou cet email est déjà utilisé par un autre employé."));
                    exit;
                }

                $userModel->update($id, $nom, $prenom, $fonction, $tel, $email, $lieu_id, $salaire);

                if (!empty($_POST['password'])) {
                    $pass = password_hash($_POST['password'], PASSWORD_ARGON2ID);
                    $userModel->updatePassword($id, $pass);
                }

                header('Location: index.php?page=admin_users&success=updated');
                exit;
            } catch (PDOException $e) {
                error_log($e->getMessage());
                header('Location: index.php?page=admin_users&error=' . urlencode("Erreur lors de la mise à jour."));
                exit;
            }
        }
    }

    public function toggleUser() {
        Security::verifyCsrfGet();
        if (isset($_GET['id'])) {
            $userModel = new User($this->pdo);
            $user = $userModel->findById($_GET['id']);

            if (!$user) {
                header('Location: index.php?page=admin_users&error=user_not_found');
                exit;
            }

            // Si on s'apprête à ACTIVER l'utilisateur
            if ($user['is_active'] == 0) {
                // Vérifier le quota
                $stmtPlan = $this->pdo->prepare("SELECT p.max_employees, p.nom FROM entreprises e JOIN plans p ON e.plan_id = p.id WHERE e.id = ?");
                $stmtPlan->execute([$_SESSION['entreprise_id']]);
                $plan = $stmtPlan->fetch();
                
                $limit = $plan['max_employees'] ?? 3;
                $currentActive = $userModel->countActive();

                if ($currentActive >= $limit) {
                    $msg = "Quota atteint (" . $plan['nom'] . " : $limit actifs). Désactivez un autre employé pour activer celui-ci.";
                    header('Location: index.php?page=admin_users&error=' . urlencode($msg));
                    exit;
                }
            }

            $userModel->toggleActive($_GET['id']);
            header('Location: index.php?page=admin_users&success=status_updated');
            exit;
        }
    }

    public function resetDevice() {
        Security::verifyCsrfGet();
        if (isset($_GET['id'])) {
            $userModel = new User($this->pdo);
            $userModel->clearDeviceId($_GET['id']);
            header('Location: index.php?page=admin_users&success=device_reset');
            exit;
        }
    }
    
    public function createLieu() {
        Security::enforceCsrfGuard();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($_POST['nom_lieu'] ?? '');
            $lat = trim($_POST['gps_lat'] ?? '');
            $lng = trim($_POST['gps_lng'] ?? '');

            if (empty($nom) || empty($lat) || empty($lng)) {
                header('Location: index.php?page=admin_lieux&error=donnees_incompletes');
                exit;
            }

            $token   = bin2hex(random_bytes(16));
            $lieuModel = new Lieu($this->pdo);
            $tolerance = isset($_POST['tolerance_retard']) ? (int)$_POST['tolerance_retard'] : 0;
            $methode   = in_array($_POST['methode_pointage'] ?? '', ['QR_CODE', 'DIRECT']) ? $_POST['methode_pointage'] : 'DIRECT';
            $rayon     = (int)($_POST['rayon_metre'] ?? 50);

            if ($lieuModel->create($nom, $lat, $lng, $rayon, $token, $tolerance, $methode)) {
                header('Location: index.php?page=admin_lieux&success=lieu_cree');
            } else {
                header('Location: index.php?page=admin_lieux&error=erreur_creation');
            }
            exit;
        }
    }

    public function updateLieu() {
        Security::enforceCsrfGuard();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id  = $_POST['id'] ?? '';
            $nom = trim($_POST['nom_lieu'] ?? '');
            $lat = trim($_POST['gps_lat'] ?? '');
            $lng = trim($_POST['gps_lng'] ?? '');

            if (empty($id) || empty($nom) || empty($lat) || empty($lng)) {
                header('Location: index.php?page=admin_lieux&error=donnees_incompletes');
                exit;
            }

            $rayon     = (int)($_POST['rayon_metre'] ?? 50);
            $tolerance = isset($_POST['tolerance_retard']) ? (int)$_POST['tolerance_retard'] : 0;
            $methode   = in_array($_POST['methode_pointage'] ?? '', ['QR_CODE', 'DIRECT']) ? $_POST['methode_pointage'] : 'QR_CODE';
            
            $lieuModel = new Lieu($this->pdo);
            if ($lieuModel->update($id, $nom, $lat, $lng, $rayon, $tolerance, $methode)) {
                header('Location: index.php?page=admin_lieux&success=updated');
            } else {
                header('Location: index.php?page=admin_lieux&error=erreur_maj');
            }
            exit;
        }
    }

    public function deleteLieu() {
        Security::verifyCsrfGet();
        if (isset($_GET['id'])) {
            $lieuModel = new Lieu($this->pdo);
            try {
                $lieuModel->delete($_GET['id']);
                header('Location: index.php?page=admin_lieux&success=deleted');
            } catch (PDOException $e) {
                header('Location: index.php?page=admin_lieux&error=Erreur suppression');
            }
            exit;
        }
    }

    public function pub() {
        $pubFile = 'config/pub.json';
        $pub = ['image' => '', 'link' => ''];
        if(file_exists($pubFile)) {
            $data = json_decode(file_get_contents($pubFile), true);
            if(is_array($data)) { $pub = array_merge($pub, $data); }
        }
        require 'views/admin/pub.php';
    }

    public function savePub() {
        Security::enforceCsrfGuard();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pubFile = 'config/pub.json';
            $pub = ['image' => '', 'link' => ''];
            $pub['link'] = $_POST['link'] ?? '';
            // Upload logic...
            file_put_contents($pubFile, json_encode($pub));
            header('Location: index.php?page=admin_pub&success=updated');
            exit;
        }
    }

    public function billing() {
        $pdo = $this->pdo;
        $entreprise_id = $_SESSION['entreprise_id'] ?? 1;

        // Mapping des fonctionnalités "à la carte" pour l'affichage des tarifs (identique à landing.php)
        $featureMap = [
            'has_qr_code'            => 'Pointage QR Code',
            'has_direct_scan'        => 'Pointage direct (One-Tap)',
            'has_gps_precision'      => 'Vérification GPS Haute Précision',
            'has_advanced_dashboard' => 'Tableau de bord avancé',
            'has_payroll_mgmt'       => 'Gestion de la paie',
            'has_auto_payroll'       => 'Calcul de paie automatique',
            'has_pdf_export'         => 'Export PDF bulletin de paie',
            'has_unlimited_history'  => 'Historique de présence illimité',
            'has_web_mobile'         => 'Accès Web & Mobile',
            'has_email_alerts'       => 'Alertes e-mail',
            'has_messaging'          => 'Messagerie interne'
        ];

        // Infos entreprise et son plan
        $stmt = $this->pdo->prepare("SELECT e.*, p.nom as plan_nom, p.* FROM entreprises e LEFT JOIN plans p ON e.plan_id = p.id WHERE e.id = ?");
        $stmt->execute([$entreprise_id]);
        $entreprise = $stmt->fetch();

        // Transactions
        $page = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        $stmtTotal = $this->pdo->prepare("SELECT COUNT(*) FROM transactions WHERE entreprise_id = ? AND statut = 'ACCEPTED'");
        $stmtTotal->execute([$entreprise_id]);
        $totalTransactions = $stmtTotal->fetchColumn() ?: 0;
        $totalPages = ceil($totalTransactions / $limit);

        $stmtTrans = $this->pdo->prepare("SELECT * FROM transactions WHERE entreprise_id = ? ORDER BY date_paiement DESC LIMIT ? OFFSET ?");
        $stmtTrans->bindValue(1, $entreprise_id, PDO::PARAM_INT);
        $stmtTrans->bindValue(2, $limit, PDO::PARAM_INT);
        $stmtTrans->bindValue(3, $offset, PDO::PARAM_INT);
        $stmtTrans->execute();
        $transactions = $stmtTrans->fetchAll();

        // Tous les plans disponibles pour upgrade
        $allPlans = $this->pdo->query("SELECT * FROM plans WHERE montant_mensuel > 0 ORDER BY montant_mensuel ASC")->fetchAll();

        require 'views/admin/billing.php';
    }

    public function savePlanning() {
        Security::enforceCsrfGuard();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $lieu_id = $_POST['id'];
            $days = $_POST['days'] ?? []; 
            $formatted = [];
            for($i=1; $i<=7; $i++) {
                $formatted[] = [
                    'jour' => $i,
                    'debut' => !empty($days[$i]['debut']) ? $days[$i]['debut'] : '08:00',
                    'fin' => !empty($days[$i]['fin']) ? $days[$i]['fin'] : '18:00',
                    'repos' => isset($days[$i]['repos']) ? 1 : 0
                ];
            }
            $lieuModel = new Lieu($this->pdo);
            $lieuModel->updatePlanning($lieu_id, $formatted);
            header('Location: index.php?page=admin_config_lieu&id=' . $lieu_id . '&success=planning_saved');
            exit;
        }
    }

    public function payrollReport() {
        $month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
        $year  = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
        $lieu_id = !empty($_GET['lieu_id']) ? (int)$_GET['lieu_id'] : null;

        $lieuModel = new Lieu($this->pdo);
        $pointageModel = new Pointage($this->pdo);

        $lieux = $lieuModel->getAll();
        $report = $pointageModel->getMonthlyPayrollReport($month, $year, $lieu_id);

        require 'views/admin/payroll_report.php';
    }
    
    public function payrollLedger() {
        $month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
        $year  = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
        $lieu_id = !empty($_GET['lieu_id']) ? (int)$_GET['lieu_id'] : null;

        $lieuModel = new Lieu($this->pdo);
        $pointageModel = new Pointage($this->pdo);

        $lieux = $lieuModel->getAll();
        $report = $pointageModel->getMonthlyPayrollReport($month, $year, $lieu_id);

        // Infos entreprise pour l'en-tête du livre de paie
        $stmt = $this->pdo->prepare("SELECT * FROM entreprises WHERE id = ?");
        $stmt->execute([$_SESSION['entreprise_id']]);
        $entreprise = $stmt->fetch();

        require 'views/admin/payroll_ledger.php';
    }
    
    public function listGeneratedBulletins() {
        $month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
        $year  = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
        $search = $_GET['search'] ?? '';

        $pointageModel = new Pointage($this->pdo);
        $report = $pointageModel->getMonthlyPayrollReport($month, $year);

        $bulletins = [];
        foreach($report as $r) {
            $fullName = $r['user']['nom'] . ' ' . $r['user']['prenom'];
            if (!empty($search) && stripos($fullName, $search) === false) continue;

            $bulletins[] = [
                'user_id' => $r['user']['id'],
                'nom' => $r['user']['nom'],
                'prenom' => $r['user']['prenom'],
                'month' => $month,
                'year' => $year,
                'net_to_pay' => $r['net_a_payer'],
                'unique_id' => 'RHX-' . str_pad($r['user']['id'], 3, '0', STR_PAD_LEFT) . '-' . sprintf('%02d%s', $month, substr($year, 2)) . '-' . strtoupper(substr(md5($r['user']['id'] . $month . $year . 'RHXS'), 0, 6)),
                'generated_at' => date('Y-m-d H:i:s') // Simulé car calculé à la volée
            ];
        }

        require 'views/admin/payroll_history.php';
    }
    
    public function payrollDetail() {
        $user_id = (int)($_GET['user_id'] ?? 0);
        $month = (int)($_GET['month'] ?? date('m'));
        $year  = (int)($_GET['year'] ?? date('Y'));
        $lieu_id = !empty($_GET['lieu_id']) ? (int)$_GET['lieu_id'] : null;

        $userModel = new User($this->pdo);
        $pointageModel = new Pointage($this->pdo);
        $user = $userModel->findById($user_id);

        if (!$user) {
            header('Location: index.php?page=admin_payroll&error=employe_introuvable');
            exit;
        }

        // --- GESTION POST : SAUVEGARDE DES AJUSTEMENTS ---
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_adjustments') {
            Security::enforceCsrfGuard();
            $primes = (float)($_POST['primes'] ?? 0);
            $retenues = (float)($_POST['retenues_manuelles'] ?? 0);
            $desc = $_POST['adjustment_description'] ?? '';

            $stmt = $this->pdo->prepare("INSERT INTO payroll_adjustments (entreprise_id, user_id, month, year, amount_primes, amount_retenues, description) 
                                        VALUES (?, ?, ?, ?, ?, ?, ?) 
                                        ON DUPLICATE KEY UPDATE amount_primes = VALUES(amount_primes), amount_retenues = VALUES(amount_retenues), description = VALUES(description)");
            $stmt->execute([$_SESSION['entreprise_id'], $user_id, $month, $year, $primes, $retenues, $desc]);
            $success_adj = "Ajustements enregistrés avec succès.";
        }

        // Récupérer le rapport pour cet utilisateur spécifique
        $fullReport = $pointageModel->getMonthlyPayrollReport($month, $year, $user['lieu_id']);
        $userReport = null;
        foreach($fullReport as $r) {
            if ($r['user']['id'] == $user_id) {
                $userReport = $r;
                break;
            }
        }

        if (!$userReport) {
            header('Location: index.php?page=admin_payroll&error=donnees_indisponibles');
            exit;
        }

        // Variables pour la vue
        $theoreticalHours = $userReport['theoretical_hours'];
        $totalDelay = $userReport['total_delay_hours'] * 3600;
        $tauxHoraire = $userReport['taux_horaire'];
        $retenue = $userReport['retenue_retard'];
        $netToPay = $userReport['net_a_payer'];
        
        // Charger l'ajustement actuel
        $stmtAdj = $this->pdo->prepare("SELECT * FROM payroll_adjustments WHERE entreprise_id = ? AND user_id = ? AND month = ? AND year = ?");
        $stmtAdj->execute([$_SESSION['entreprise_id'], $user_id, $month, $year]);
        $adjustment = $stmtAdj->fetch() ?: ['amount_primes' => 0, 'amount_retenues' => 0, 'description' => ''];

        // Historique détaillé des pointages du mois
        $details = $this->getDetailedMonthStats($user_id, $month, $year);

        require 'views/admin/payroll_detail.php';
    }

    private function getDetailedMonthStats($user_id, $month, $year) {
        $daysInMonth = (int)date('t', strtotime("$year-$month-01"));
        $pointageModel = new Pointage($this->pdo);
        $userModel = new User($this->pdo);
        $user = $userModel->findById($user_id);
        
        $stmtPlan = $this->pdo->prepare("SELECT * FROM planning_lieux WHERE lieu_id = ?");
        $stmtPlan->execute([$user['lieu_id']]);
        $plans = [];
        while($p = $stmtPlan->fetch()) $plans[$p['jour_semaine']] = $p;

        $stats = [];
        for ($d=1; $d <= $daysInMonth; $d++) {
            $date = sprintf('%04d-%02d-%02d', $year, $month, $d);
            if (strtotime($date) > time()) continue;

            $dayOfWeek = date('N', strtotime($date));
            $plan = $plans[$dayOfWeek] ?? null;
            $prevu = ($plan && !$plan['is_repos']) ? $plan['heure_debut'] : ($plan ? 'Repos' : 'N/A');

            // Mouvements du jour
            $stmtMvts = $this->pdo->prepare("SELECT type_mouvement as type, heure_pointage as heure FROM pointages WHERE user_id = ? AND DATE(heure_pointage) = ? ORDER BY heure_pointage ASC");
            $stmtMvts->execute([$user_id, $date]);
            $mvts = $stmtMvts->fetchAll();

            $retard_sec = 0;
            $arrivee = null;
            foreach($mvts as $m) {
                if ($m['type'] === 'ARRIVEE' && $arrivee === null) {
                    $arrivee = $m['heure'];
                    if ($plan && !$plan['is_repos']) {
                        $retard_sec = PayrollCalculator::calculateDelaySeconds($arrivee, $plan['heure_debut']);
                        $tolerance = (int)($user['tolerance_retard'] ?? 0) * 60;
                        if ($retard_sec <= $tolerance) $retard_sec = 0;
                        else $retard_sec -= $tolerance;
                    }
                }
            }

            $stats[] = [
                'jour' => $date,
                'prevu' => $prevu,
                'mvts' => $mvts,
                'retard_sec' => $retard_sec,
                'arrivee' => $arrivee
            ];
        }
        return array_reverse($stats);
    }
    
    public function payrollBulletin() {
        $user_id = (int)($_GET['user_id'] ?? 0);
        $month = (int)($_GET['month'] ?? date('m'));
        $year  = (int)($_GET['year'] ?? date('Y'));

        $userModel = new User($this->pdo);
        $pointageModel = new Pointage($this->pdo);
        $user = $userModel->findById($user_id);

        if (!$user) {
            die("Employé introuvable.");
        }

        // Infos entreprise pour l'en-tête
        $stmtEnt = $this->pdo->prepare("SELECT * FROM entreprises WHERE id = ?");
        $stmtEnt->execute([$_SESSION['entreprise_id']]);
        $entreprise = $stmtEnt->fetch();

        // Récupérer le rapport pour cet utilisateur spécifique
        $fullReport = $pointageModel->getMonthlyPayrollReport($month, $year, $user['lieu_id']);
        $userReport = null;
        foreach($fullReport as $r) {
            if ($r['user']['id'] == $user_id) {
                $userReport = $r;
                break;
            }
        }

        if (!$userReport) {
            die("Données indisponibles pour ce mois.");
        }

        // Variables pour la vue
        $theoreticalHours = $userReport['theoretical_hours'];
        $totalDelay = $userReport['total_delay_hours'] * 3600;
        $tauxHoraire = $userReport['taux_horaire'];
        $retenue = $userReport['retenue_retard'];
        $netToPay = $userReport['net_a_payer'];
        
        // Charger l'ajustement actuel
        $stmtAdj = $this->pdo->prepare("SELECT * FROM payroll_adjustments WHERE entreprise_id = ? AND user_id = ? AND month = ? AND year = ?");
        $stmtAdj->execute([$_SESSION['entreprise_id'], $user_id, $month, $year]);
        $adjustment = $stmtAdj->fetch() ?: ['amount_primes' => 0, 'amount_retenues' => 0, 'description' => ''];

        require 'views/admin/payroll_bulletin.php';
    }
    
    public function exportMonthlyPayroll() {
        // Implementation logic...
    }

    public function configLieu() {
        if (!isset($_GET['id'])) {
            header('Location: index.php?page=admin_lieux&error=id_manquant');
            exit;
        }
        $id = $_GET['id'];
        $lieuModel = new Lieu($this->pdo);
        $lieu = $lieuModel->findById($id);

        if (!$lieu) {
            header('Location: index.php?page=admin_lieux&error=lieu_introuvable');
            exit;
        }

        $planning = $lieuModel->getPlanning($id);
        require 'views/admin/lieu_config.php';
    }
}