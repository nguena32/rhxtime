<?php
class ApiController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function handleScan() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        try {
            if (session_status() === PHP_SESSION_NONE) session_start();

            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Session expirée.']);
                exit;
            }

            $input = json_decode(file_get_contents('php://input'), true);
            $qrToken = $input['qr_token'] ?? '';
            $userLat = $input['gps_lat'] ?? 0;
            $userLng = $input['gps_lng'] ?? 0;
            $type    = $input['type'] ?? 'ARRIVEE';
            $inputToken = $input['csrf_token'] ?? '';
            $offlineTs = $input['offline_timestamp'] ?? null;
            $userId  = $_SESSION['user_id'];

            Security::verifyCsrfToken($inputToken);

            $lieuModel = new Lieu($this->pdo);
            $userModel = new User($this->pdo);
            $pointageModel = new Pointage($this->pdo);

            $lieu = $lieuModel->findByToken($qrToken);

            if (!$lieu) { throw new Exception('QR Code invalide.'); }

            $userProps = $userModel->findById($userId);

            if ($userProps['is_active'] == 0) {
                 throw new Exception("Compte suspendu.");
            }

            if ($userProps['lieu_id'] != $lieu['id']) {
                throw new Exception("Vous n'êtes pas affecté à ce lieu (" . $lieu['nom_lieu'] . ").");
            }

            require_once __DIR__ . '/../utils/Geofence.php';
            $distance = Geofence::getDistance($userLat, $userLng, $lieu['gps_lat'], $lieu['gps_lng']);

            $accuracy = isset($input['accuracy']) ? (float)$input['accuracy'] : 0;
            // Tolerance based on accuracy (capped at 80m to avoid abuse)
            $tolerance = min(80, $accuracy);
            $effectiveRadius = $lieu['rayon_metre'] + $tolerance;

            // ── Géofencing STRICT : refus si hors zone ──
            if ($distance > $effectiveRadius) {
                $dist_arrondie = round($distance);
                $zone_autorisee = round($effectiveRadius);
                throw new Exception(
                    "Distance détectée : {$dist_arrondie}m.\n" .
                    "Zone autorisée : {$zone_autorisee}m (Rayon {$lieu['rayon_metre']}m + Tolérance {$tolerance}m).\n\n" .
                    "Veuillez vous rapprocher de l'entrée du site \"" . $lieu['nom_lieu'] . "\"."
                );
            }

            $last = $pointageModel->getLastPointageToday($userId);

            if ($type == 'ARRIVEE' && $last && $last['type_mouvement'] == 'ARRIVEE') {
                 throw new Exception("Vous avez déjà pointé votre arrivée.");
            }
            if ($type == 'DEPART' && (!$last || $last['type_mouvement'] == 'DEPART')) {
                 throw new Exception("Impossible de partir sans être arrivé.");
            }

            $pointageModel->create($userId, $lieu['id'], $type, $userLat, $userLng, $distance, $offlineTs, 0);

            // Hook: Notification ou traitement tiers
            EventManager::trigger('pointage.created', [
                'user_id' => $userId,
                'lieu_id' => $lieu['id'],
                'type' => $type,
                'lat' => $userLat,
                'lng' => $userLng,
                'distance' => $distance,
                'offline' => !empty($offlineTs)
            ]);

            echo json_encode(['success' => true, 'message' => "Pointage $type validé !"]);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Pointage SANS QR Code : le lieu est résolu automatiquement depuis user.lieu_id
     * Toutes les vérifications sont identiques à handleScan() (GPS, empreinte, cohérence)
     */
    public function handleDirectScan() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        try {
            if (session_status() === PHP_SESSION_NONE) session_start();

            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Session expirée.']);
                exit;
            }

            $input      = json_decode(file_get_contents('php://input'), true);
            $userLat    = $input['gps_lat']    ?? 0;
            $userLng    = $input['gps_lng']    ?? 0;
            $type       = $input['type']       ?? 'ARRIVEE';
            $inputToken = $input['csrf_token'] ?? '';
            $offlineTs  = $input['offline_timestamp'] ?? null;
            $userId     = $_SESSION['user_id'];

            Security::verifyCsrfToken($inputToken);

            $lieuModel     = new Lieu($this->pdo);
            $userModel     = new User($this->pdo);
            $pointageModel = new Pointage($this->pdo);

            // Charger les infos de l'employé
            $userProps = $userModel->findById($userId);

            if (!$userProps) {
                throw new Exception("Compte introuvable.");
            }
            if ($userProps['is_active'] == 0) {
                throw new Exception("Compte suspendu.");
            }
            if (empty($userProps['lieu_id'])) {
                throw new Exception("Aucun lieu de travail associé à votre compte. Contactez l'administrateur.");
            }

            // Résolution automatique du lieu depuis lieu_id de l'employé (pas besoin de QR)
            $lieu = $lieuModel->findById($userProps['lieu_id']);
            if (!$lieu) {
                throw new Exception("Lieu de travail introuvable. Contactez l'administrateur.");
            }

            // ── Géofencing STRICT : refus si hors zone ──
            require_once __DIR__ . '/../utils/Geofence.php';
            $distance = Geofence::getDistance($userLat, $userLng, $lieu['gps_lat'], $lieu['gps_lng']);

            $accuracy = isset($input['accuracy']) ? (float)$input['accuracy'] : 0;
            $tolerance = min(80, $accuracy);
            $effectiveRadius = $lieu['rayon_metre'] + $tolerance;

            if ($distance > $effectiveRadius) {
                $dist_arrondie = round($distance);
                $zone_autorisee = round($effectiveRadius);
                throw new Exception(
                    "Distance détectée : {$dist_arrondie}m.\n" .
                    "Zone autorisée : {$zone_autorisee}m (Rayon {$lieu['rayon_metre']}m + Tolérance {$tolerance}m).\n\n" .
                    "Veuillez vous rapprocher de l'entrée du site \"" . $lieu['nom_lieu'] . "\"."
                );
            }

            // ── Vérification cohérence des mouvements ──
            $last = $pointageModel->getLastPointageToday($userId);

            if ($type == 'ARRIVEE' && $last && $last['type_mouvement'] == 'ARRIVEE') {
                throw new Exception("Vous avez déjà pointé votre arrivée aujourd'hui.");
            }
            if ($type == 'DEPART' && (!$last || $last['type_mouvement'] == 'DEPART')) {
                throw new Exception("Impossible de pointer le départ sans avoir pointé l'arrivée.");
            }

            // ── Enregistrement (toujours propre, sans anomalie) ──
            $pointageModel->create($userId, $lieu['id'], $type, $userLat, $userLng, $distance, $offlineTs, 0);

            // Hook: Notification ou traitement tiers
            EventManager::trigger('pointage.created', [
                'user_id' => $userId,
                'lieu_id' => $lieu['id'],
                'type' => $type,
                'lat' => $userLat,
                'lng' => $userLng,
                'distance' => $distance,
                'offline' => !empty($offlineTs)
            ]);

            echo json_encode(['success' => true, 'message' => "Pointage $type validé avec succès !"]);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    public function getPaginatedPointages() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        try {
            if (session_status() === PHP_SESSION_NONE) session_start();
            if (!isset($_SESSION['admin_id'])) {
                echo json_encode(['success' => false, 'message' => 'Non autorisé']);
                exit;
            }

            $user_id = $_GET['user_id'] ?? null;
            $date_start = $_GET['date_start'] ?? null;
            $date_end = $_GET['date_end'] ?? null;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

            $pointageModel = new Pointage($this->pdo);
            $feed = $pointageModel->getDashboardFeed($user_id, $date_start, $date_end, $limit, $offset);

            // Échappement des données pour le JSON
            $cleanData = [];
            foreach ($feed as $f) {
                $typeP = $f['type_mouvement'] ?? $f['type_pointage'] ?? 'INCONNU';
                $dateObj = strtotime($f['heure_pointage']);
                
                $cleanData[] = [
                    'nom' => Security::html($f['nom']),
                    'prenom' => Security::html($f['prenom']),
                    'initial' => substr(strval($f['prenom'] ?? ''), 0, 1),
                    'type' => $typeP,
                    'is_arrivee' => ($typeP == 'ARRIVEE'),
                    'date' => date('d/m/Y', $dateObj),
                    'heure' => date('H:i', $dateObj),
                    'lieu' => Security::html($f['nom_lieu'] ?? 'Inconnu'),
                    'distance' => round($f['distance_calculee'] ?? 0)
                ];
            }

            echo json_encode([
                'success' => true, 
                'data' => $cleanData,
                'has_more' => (count($feed) == $limit)
            ]);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}