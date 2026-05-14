<?php
class PointageController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function scanView() {
        if (!isset($_SESSION['user_id'])) { header('Location: index.php?page=login'); exit; }

        $user_id = $_SESSION['user_id'];

        $userModel = new User($this->pdo);
        $u = $userModel->findById($user_id);

        if(!$u || !$u['lieu_id']) {
            die("Erreur: Aucun lieu associé à votre compte. Contactez l'admin.");
        }

        // ── Redirection transparente selon la méthode du lieu ──
        require_once 'models/Lieu.php';
        $lieuModel = new Lieu($this->pdo);
        $lieuData  = $lieuModel->findById($u['lieu_id']);

        // Récupérer les fonctionnalités du plan
        $stmtPlan = $this->pdo->prepare("SELECT p.* FROM entreprises e JOIN plans p ON e.plan_id = p.id WHERE e.id = ?");
        $stmtPlan->execute([$u['entreprise_id']]);
        $planFeatures = $stmtPlan->fetch();
        $hasMessaging = (bool)($planFeatures['has_messaging'] ?? true);

        if ($lieuData && ($lieuData['methode_pointage'] ?? 'QR_CODE') === 'DIRECT') {

            // Éviter la boucle infinie si on arrive déjà via scan_direct
            if (($_GET['page'] ?? '') !== 'employee_scan_direct') {
                header('Location: index.php?page=employee_scan_direct');
                exit;
            }
        }

        $pointageModel = new Pointage($this->pdo);
        $last = $pointageModel->getLastPointageToday($user_id);

        $nextAction = 'ARRIVEE';
        if ($last && $last['type_mouvement'] == 'ARRIVEE') {
            $nextAction = 'DEPART';
        }

        // Fetch history (les 10 dernières arrivées)
        $stmtHistory = $this->pdo->prepare("SELECT heure_pointage FROM pointages WHERE user_id = ? AND type_mouvement = 'ARRIVEE' ORDER BY heure_pointage DESC LIMIT 10");
        $stmtHistory->execute([$user_id]);
        $history = $stmtHistory->fetchAll();

        require_once 'models/Message.php';
        $unreadMsgs = (new Message($this->pdo))->countUnreadEmployee($user_id);

        require 'views/employee/scan.php';
    }

    public function directScanView() {
        if (!isset($_SESSION['user_id'])) { header('Location: index.php?page=login'); exit; }

        $user_id = $_SESSION['user_id'];
        $userModel = new User($this->pdo);
        $u = $userModel->findById($user_id);

        if(!$u || !$u['lieu_id']) {
            die("Erreur: Aucun lieu associé à votre compte. Contactez l'admin.");
        }

        // Récupérer le nom du lieu
        require_once 'models/Lieu.php';
        $lieuModel = new Lieu($this->pdo);
        $lieuData  = $lieuModel->findById($u['lieu_id']);
        $lieu_nom  = $lieuData['nom_lieu'] ?? 'Lieu inconnu';

        $pointageModel = new Pointage($this->pdo);
        $last = $pointageModel->getLastPointageToday($user_id);

        $nextAction = 'ARRIVEE';
        if ($last && $last['type_mouvement'] == 'ARRIVEE') {
            $nextAction = 'DEPART';
        }

        $stmtHistory = $this->pdo->prepare("SELECT heure_pointage FROM pointages WHERE user_id = ? AND type_mouvement = 'ARRIVEE' ORDER BY heure_pointage DESC LIMIT 10");
        $stmtHistory->execute([$user_id]);
        $history = $stmtHistory->fetchAll();

        require_once 'models/Message.php';
        $unreadMsgs = (new Message($this->pdo))->countUnreadEmployee($user_id);

        require 'views/employee/scan_direct.php';
    }

    public function messagesView() {
        require_once 'models/Message.php';
        $msgModel = new Message($this->pdo);
        $user_id = $_SESSION['user_id'];
        $msgModel->markAsReadForEmployee($user_id);
        $messages = $msgModel->getConversation($user_id);
        require 'views/employee/messages.php';
    }

    public function sendMessage() {
        require_once 'models/Message.php';
        Security::enforceCsrfGuard();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['content'])) {
            $msgModel = new Message($this->pdo);
            $msgModel->sendMessage($_SESSION['user_id'], 'to_admin', $_POST['content']);
        }
        header('Location: index.php?page=employee_messages');
        exit;
    }
}