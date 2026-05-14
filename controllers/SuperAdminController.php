<?php
// controllers/SuperAdminController.php
class SuperAdminController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        // Protection Strict : Autorise si l'utilisateur est Super-Admin 
        // OU s'il est en mode support (impersonnalisation) et souhaite revenir à son état original.
        $roleActuel = $_SESSION['user_role'] ?? '';
        $roleOriginal = $_SESSION['original_user_role'] ?? '';

        if ($roleActuel !== 'super-admin' && $roleOriginal !== 'super-admin') {
            header('Location: index.php?page=login');
            exit;
        }

        // Garantir que les tables système existent dès l'accès à une page Super-Admin
        $this->ensurePlatformSchema();
    }

    public function dashboard() {
        // --- TEMPORARY FIX: Remplir les dates de création manquantes ---
        try {
            $this->pdo->exec("UPDATE entreprises SET Created_at = IF(expiration_date IS NOT NULL, DATE_SUB(expiration_date, INTERVAL 14 DAY), NOW()) WHERE Created_at IS NULL");
            $this->pdo->exec("ALTER TABLE entreprises MODIFY Created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
        } catch (Exception $e) {}
        // ----------------------------------------------------------------

        try {
            // Dashboard snapshot: LIMIT 10 latest
            $stmt = $this->pdo->query("SELECT e.*, p.nom as plan_nom FROM entreprises e LEFT JOIN plans p ON e.plan_id = p.id ORDER BY e.Created_at DESC LIMIT 10");
            $entreprises = $stmt ? $stmt->fetchAll() : [];
        } catch (PDOException $e) {
            $entreprises = [];
        }

        // Vérification d'intégrité des signatures pour l'alerte multi-machine
        $signatureIssues = 0;
        foreach ($entreprises as $e) {
            $expected = Security::generateExpirationSignature($e['id'], $e['expiration_date']);
            if (($e['security_signature'] ?? '') !== $expected) {
                $signatureIssues++;
            }
        }

        $this->generateCsrfToken();

        $cacheFile = __DIR__ . '/../cache/dashboard_stats.json';
        $cacheData = null;

        // TENTATIVE 1 : Récupérer le Snapshot du jour (BDD)
        try {
            $stmtSnap = $this->pdo->prepare("SELECT stats_json FROM platform_stats_snapshots WHERE snapshot_date = CURDATE()");
            $stmtSnap->execute();
            $snap = $stmtSnap->fetchColumn();
            if ($snap) {
                $cacheData = json_decode($snap, true);
            }
        } catch (Exception $e) {}

        // TENTATIVE 2 : Fallback sur le cache fichier si pas de snapshot récent
        if (!$cacheData && file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 300) { 
            $cacheData = json_decode(file_get_contents($cacheFile), true);
        }

        if ($cacheData) {
            extract($cacheData);
        } else {
            // Recalculer et Générer un nouveau Snapshot
            $cacheData = $this->generateStatsSnapshot();
            extract($cacheData);
        }

        require 'views/superadmin/dashboard.php';
    }

    /**
     * Assure que toutes les tables critiques du SuperAdmin existent
     */
    private function ensurePlatformSchema() {
        try {
            // Table PLATFORM SETTINGS
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS platform_settings (
                setting_key VARCHAR(100) NOT NULL PRIMARY KEY,
                setting_value TEXT DEFAULT NULL,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            // Table TICKETS
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS tickets (
                id INT AUTO_INCREMENT PRIMARY KEY,
                admin_id INT NOT NULL,
                entreprise_id INT NOT NULL,
                subject VARCHAR(255) NOT NULL,
                status ENUM('open', 'replied', 'closed') DEFAULT 'open',
                priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_ticket_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            // Table TICKET MESSAGES
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS ticket_messages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                ticket_id INT NOT NULL,
                sender_id INT NOT NULL,
                sender_role ENUM('admin', 'superadmin') NOT NULL,
                message TEXT NOT NULL,
                attachment_path VARCHAR(255) DEFAULT NULL,
                is_read TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_msg_ticket (ticket_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            // Table STATS VISITORS
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS stats_visitors (
                ip VARCHAR(45), 
                visit_date DATE, 
                PRIMARY KEY (ip, visit_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            // Table AUDIT LOGS
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS audit_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                admin_id INT NULL,
                entreprise_id INT NULL,
                action VARCHAR(100),
                old_value TEXT NULL,
                new_value TEXT NULL,
                ip_address VARCHAR(45) NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            // Table STATS SNAPSHOTS (Performance Optimization)
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS platform_stats_snapshots (
                id INT AUTO_INCREMENT PRIMARY KEY,
                snapshot_date DATE NOT NULL UNIQUE,
                stats_json JSON NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        } catch (Exception $e) {
            error_log("Erreur de déploiement schéma : " . $e->getMessage());
        }
    }

    public function entreprisesList() {
        if (($_SESSION['user_role'] ?? '') !== 'super-admin') {
            header('Location: index.php');
            exit;
        }

        $page = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
        $limit = 50;
        $offset = ($page - 1) * $limit;

        $totalEntreprises = $this->pdo->query("SELECT COUNT(*) FROM entreprises")->fetchColumn() ?: 0;
        $totalPages = ceil($totalEntreprises / $limit);

        try {
            $stmt = $this->pdo->prepare("SELECT e.*, p.nom as plan_nom FROM entreprises e LEFT JOIN plans p ON e.plan_id = p.id ORDER BY e.Created_at DESC LIMIT ? OFFSET ?");
            $stmt->bindValue(1, $limit, PDO::PARAM_INT);
            $stmt->bindValue(2, $offset, PDO::PARAM_INT);
            $stmt->execute();
            $entreprises = $stmt->fetchAll();
        } catch (PDOException $e) {
            $entreprises = [];
        }

        $stmtUsers = $this->pdo->query("SELECT entreprise_id, COUNT(*) as count FROM users WHERE is_active = 1 GROUP BY entreprise_id");
        $counts = [];
        while($r = $stmtUsers->fetch()) {
            $counts[$r['entreprise_id']] = $r['count'];
        }

        $stmtAdminIds = $this->pdo->query("SELECT id, entreprise_id, email_verified_at FROM admins WHERE role = 'owner'");
        $adminMapping = [];
        $verificationMapping = [];
        while($ra = $stmtAdminIds->fetch()) {
            if (!isset($adminMapping[$ra['entreprise_id']])) {
                $adminMapping[$ra['entreprise_id']] = $ra['id'];
                $verificationMapping[$ra['entreprise_id']] = $ra['email_verified_at'];
            }
        }

        // Récupérer la liste des plans pour le modal de changement de plan
        $allPlans = $this->pdo->query("SELECT * FROM plans ORDER BY id ASC")->fetchAll();

        require 'views/superadmin/entreprises.php';
    }

    /**
     * Assure l'existence de la table audit_logs
     */
    private function ensureAuditLogsTable() {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS audit_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                admin_id INT NULL,
                entreprise_id INT NULL,
                action VARCHAR(255) NOT NULL,
                old_value VARCHAR(255) NULL,
                new_value VARCHAR(255) NULL,
                ip_address VARCHAR(45) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_audit_entreprise (entreprise_id),
                INDEX idx_audit_admin (admin_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
        
        // Sécurité dynamique: Ajout des index a posteriori si la table existait déjà sans index
        try {
            $this->pdo->exec("ALTER TABLE audit_logs ADD INDEX idx_audit_entreprise (entreprise_id)");
        } catch (\PDOException $e) {}
        try {
            $this->pdo->exec("ALTER TABLE audit_logs ADD INDEX idx_audit_admin (admin_id)");
        } catch (\PDOException $e) {}
    }

    /**
     * Méthode réutilisable pour loguer une action Super-Admin
     */
    private function logAction($admin_id, $entreprise_id, $action, $old_value, $new_value) {
        $this->ensureAuditLogsTable();
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $stmt = $this->pdo->prepare("INSERT INTO audit_logs (admin_id, entreprise_id, action, old_value, new_value, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$admin_id, $entreprise_id, $action, $old_value, $new_value, $ip_address]);
    }

    /**
     * Met à jour la date d'expiration d'une entreprise et journalise l'action
     */
    public function updateExpirationDate() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_SESSION['user_role'] ?? '') !== 'super-admin') {
            header('Location: index.php');
            exit;
        }

        $entreprise_id = $_POST['entreprise_id'] ?? null;
        $new_date = $_POST['new_expiration_date'] ?? null;

        if ($entreprise_id && $new_date !== null) {
            $stmt = $this->pdo->prepare("SELECT expiration_date FROM entreprises WHERE id = ?");
            $stmt->execute([$entreprise_id]);
            $old_date = $stmt->fetchColumn();

            $new_date_sql = !empty($new_date) ? $new_date : null;
            
            // 1. Mise à jour de la date d'abord
            $stmtUpdDate = $this->pdo->prepare("UPDATE entreprises SET expiration_date = ? WHERE id = ?");
            
            if ($stmtUpdDate->execute([$new_date_sql, $entreprise_id])) {
                // 2. Relecture de la date EXACTE formatée par MySQL
                $stmtRead = $this->pdo->prepare("SELECT expiration_date FROM entreprises WHERE id = ?");
                $stmtRead->execute([$entreprise_id]);
                $exact_db_date = $stmtRead->fetchColumn();
                
                // 3. Calcul de la signature correcte avec la chaîne de la base de données
                $signature = Security::generateExpirationSignature($entreprise_id, $exact_db_date);
                
                // 4. Mise à jour de la signature
                $stmtUpdSig = $this->pdo->prepare("UPDATE entreprises SET security_signature = ? WHERE id = ?");
                $stmtUpdSig->execute([$signature, $entreprise_id]);

                // 5. Réactivation si le compte était expiré et que la nouvelle date est dans le futur
                if ($exact_db_date && strtotime($exact_db_date) > time()) {
                    $this->pdo->prepare("UPDATE entreprises SET statut = 'Active' WHERE id = ? AND statut IN ('Expired', 'Suspended')")->execute([$entreprise_id]);
                }

                $this->logAction($_SESSION['admin_id'] ?? null, $entreprise_id, 'MODIF_DATE_EXPIRATION', $old_date, $exact_db_date);
                header('Location: index.php?page=superadmin_entreprises&success=date_updated');
                exit;
            }
        }
        header('Location: index.php?page=superadmin_entreprises&error=update_failed');
        exit;
    }

    /**
     * Change le plan d'une entreprise et journalise l'action
     */
    public function updateEnterprisePlan() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_SESSION['user_role'] ?? '') !== 'super-admin') {
            header('Location: index.php');
            exit;
        }

        $entreprise_id = $_POST['entreprise_id'] ?? null;
        $new_plan_id = $_POST['plan_id'] ?? null;
        $new_date = $_POST['new_expiration_date'] ?? null;

        if ($entreprise_id && $new_plan_id) {
            // Récupérer l'ancien plan pour les logs
            $stmt = $this->pdo->prepare("SELECT plan_id, expiration_date FROM entreprises WHERE id = ?");
            $stmt->execute([$entreprise_id]);
            $currentData = $stmt->fetch();
            $old_plan_id = $currentData['plan_id'];
            $old_date = $currentData['expiration_date'];

            // 1. Mise à jour du plan
            $stmtUpd = $this->pdo->prepare("UPDATE entreprises SET plan_id = ? WHERE id = ?");
            if ($stmtUpd->execute([$new_plan_id, $entreprise_id])) {
                
                $logMsg = "Plan ID $old_plan_id -> Plan ID $new_plan_id";
                
                // 2. Si une nouvelle date est fournie, on l'applique avec la signature sécurisée
                if (!empty($new_date)) {
                    $stmtUpdDate = $this->pdo->prepare("UPDATE entreprises SET expiration_date = ? WHERE id = ?");
                    if ($stmtUpdDate->execute([$new_date, $entreprise_id])) {
                        // Relecture MySQL
                        $stmtRead = $this->pdo->prepare("SELECT expiration_date FROM entreprises WHERE id = ?");
                        $stmtRead->execute([$entreprise_id]);
                        $exact_db_date = $stmtRead->fetchColumn();
                        
                        // Signature
                        $signature = Security::generateExpirationSignature($entreprise_id, $exact_db_date);
                        $this->pdo->prepare("UPDATE entreprises SET security_signature = ? WHERE id = ?")->execute([$signature, $entreprise_id]);
                        
                        // Réactivation
                        if ($exact_db_date && strtotime($exact_db_date) > time()) {
                            $this->pdo->prepare("UPDATE entreprises SET statut = 'Active' WHERE id = ? AND statut IN ('Expired', 'Suspended')")->execute([$entreprise_id]);
                        }
                        
                        $logMsg .= " | Expiration: " . ($old_date ?? 'N/A') . " -> $exact_db_date";
                    }
                }

                $this->logAction($_SESSION['admin_id'] ?? null, $entreprise_id, 'CHANGE_PLAN', "Modif Plan", $logMsg);
                header('Location: index.php?page=superadmin_entreprises&success=plan_updated');
                exit;
            }
        }
        header('Location: index.php?page=superadmin_entreprises&error=plan_update_failed');
        exit;
    }

    public function promoCodesList() {
        if (($_SESSION['user_role'] ?? '') !== 'super-admin') {
            header('Location: index.php');
            exit;
        }

        $page = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
        $limit = 50;
        $offset = ($page - 1) * $limit;

        // Auto-fix : S'assurer que la table existe
        try {
            $this->pdo->query("SELECT 1 FROM promo_codes LIMIT 1");
        } catch (\Exception $e) {
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS promo_codes (
              id int(11) NOT NULL AUTO_INCREMENT,
              code varchar(50) NOT NULL UNIQUE,
              days_to_add int(11) DEFAULT 0,
              usage_limit int(11) DEFAULT 1,
              used_count int(11) DEFAULT 0,
              is_active tinyint(1) DEFAULT 1,
              expires_at datetime DEFAULT NULL,
              created_at datetime DEFAULT current_timestamp(),
              PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        }

        $totalPromos = $this->pdo->query("SELECT COUNT(*) FROM promo_codes")->fetchColumn() ?: 0;
        $totalPages = ceil($totalPromos / $limit);

        $stmt = $this->pdo->prepare("SELECT * FROM promo_codes ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        $promos = $stmt->fetchAll();

        require 'views/superadmin/promos.php';
    }

    public function createPromoCode() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_SESSION['user_role'] ?? '') !== 'super-admin') {
            header('Location: index.php');
            exit;
        }

        Security::verifyCsrfToken($_POST['csrf_token'] ?? '');

        $code = strtoupper(trim($_POST['code'] ?? ''));
        $days = (int)($_POST['days_to_add'] ?? 0);
        $limit = (int)($_POST['usage_limit'] ?? 1);
        $expires = $_POST['expires_at'] ?: null; // Handle empty date correctly

        if ($code && $days > 0 && $limit > 0) {
            try {
                $stmt = $this->pdo->prepare("INSERT INTO promo_codes (code, days_to_add, usage_limit, expires_at) VALUES (?, ?, ?, ?)");
                $stmt->execute([$code, $days, $limit, $expires]);
                $this->logAction($_SESSION['admin_id'] ?? null, null, 'CREATE_PROMO_CODE', null, $code . " (+$days Jours)");
                header('Location: index.php?page=superadmin_promos&success=promo_created');
                exit;
            } catch (PDOException $e) {
                // Ignore code duplicated error conceptually or handle it
                header('Location: index.php?page=superadmin_promos&error=duplicate_promo');
                exit;
            }
        }
        header('Location: index.php?page=superadmin_promos&error=invalid_data');
        exit;
    }

    public function togglePromoCode() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_SESSION['user_role'] ?? '') !== 'super-admin') {
            header('Location: index.php');
            exit;
        }

        Security::verifyCsrfToken($_POST['csrf_token'] ?? '');
        $id = (int)($_POST['id'] ?? 0);

        if ($id > 0) {
            $stmt = $this->pdo->prepare("UPDATE promo_codes SET is_active = NOT is_active WHERE id = ?");
            if ($stmt->execute([$id])) {
                $this->logAction($_SESSION['admin_id'] ?? null, null, 'TOGGLE_PROMO_CODE', null, "Promo ID $id");
                header('Location: index.php?page=superadmin_promos&success=promo_toggled');
                exit;
            }
        }
        header('Location: index.php?page=superadmin_promos&error=toggle_failed');
        exit;
    }

    public function resetAdminPassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $admin_id = $_POST['admin_id'] ?? null;
            $new_pass = $_POST['new_password'] ?? '';
            
            if ($admin_id && !empty($new_pass)) {
                $adminModel = new Admin($this->pdo);
                $hash = password_hash($new_pass, PASSWORD_DEFAULT);
                
                // On utilise une requête directe pour bypasser le filtre entreprise_id de session si besoin
                $stmt = $this->pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
                if ($stmt->execute([$hash, $admin_id])) {
                    header('Location: index.php?page=superadmin_dashboard&success=pass_reset');
                    exit;
                }
            }
        }
        header('Location: index.php?page=superadmin_dashboard&error=reset_failed');
    }

    public function impersonate($id) {
        // Validation que l'entreprise existe
        $stmt = $this->pdo->prepare("SELECT * FROM entreprises WHERE id = ?");
        $stmt->execute([$id]);
        $entreprise = $stmt->fetch();
        
        if ($entreprise) {
            // Sauvegarder le contexte complet du Super-Admin
            $_SESSION['original_admin_id'] = $_SESSION['admin_id'];
            $_SESSION['original_entreprise_id'] = $_SESSION['entreprise_id'] ?? null;
            $_SESSION['original_user_role'] = $_SESSION['user_role'];
            $_SESSION['original_auth_level'] = $_SESSION['auth_level'];
            
            // Basculer vers le contexte de l'entreprise cible
            $_SESSION['entreprise_id'] = $entreprise['id'];
            $_SESSION['entreprise_nom_impersonate'] = $entreprise['nom'];
            $_SESSION['user_role'] = 'owner'; // Simuler le rôle Owner pour la vue
            $_SESSION['auth_level'] = 2;
            
            header("Location: index.php?page=admin_dashboard");
            exit;
        }
        header("Location: index.php?page=superadmin_dashboard");
        exit;
    }

    public function stopImpersonating() {
        if (isset($_SESSION['original_admin_id'])) {
            // Restaurer TOUT le contexte original du Super-Admin
            $_SESSION['admin_id'] = $_SESSION['original_admin_id'];
            $_SESSION['entreprise_id'] = $_SESSION['original_entreprise_id'] ?? null;
            $_SESSION['user_role'] = $_SESSION['original_user_role'] ?? 'super-admin';
            $_SESSION['auth_level'] = $_SESSION['original_auth_level'] ?? 1;
            
            unset($_SESSION['original_admin_id']);
            unset($_SESSION['original_entreprise_id']);
            unset($_SESSION['original_user_role']);
            unset($_SESSION['original_auth_level']);
            unset($_SESSION['entreprise_nom_impersonate']);
            
            header("Location: index.php?page=superadmin_dashboard");
            exit;
        }
        header("Location: index.php");
    }

    // ==========================================
    // CONFIGURATION EMAIL SMTP
    // ==========================================

    /**
     * Assure l'existence de la table platform_settings (key-value store)
     */
    private function ensureSettingsTable() {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS platform_settings (
                setting_key VARCHAR(100) NOT NULL PRIMARY KEY,
                setting_value TEXT DEFAULT NULL,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    /**
     * Clé de chiffrement dérivée - utilise une constante interne pour AES-256
     */
    private function getEncryptionKey() {
        // Clé dérivée du nom de la BDD + sel fixe pour empêcher la lecture en clair
        $dbName = getenv('DB_NAME') ?: 'rhtimes_saas';
        return hash('sha256', 'RHtimes_SMTP_' . $dbName . '_SecretKey2026', true);
    }

    /**
     * Chiffre une valeur sensible (AES-256-CBC)
     */
    private function encryptValue($plaintext) {
        $key = $this->getEncryptionKey();
        $iv = openssl_random_pseudo_bytes(16);
        $encrypted = openssl_encrypt($plaintext, 'AES-256-CBC', $key, 0, $iv);
        return base64_encode($iv . '::' . $encrypted);
    }

    /**
     * Déchiffre une valeur sensible (AES-256-CBC)
     */
    private function decryptValue($ciphertext) {
        $key = $this->getEncryptionKey();
        $data = base64_decode($ciphertext);
        if ($data === false || strpos($data, '::') === false) return $ciphertext; // Fallback si non chiffré
        list($iv, $encrypted) = explode('::', $data, 2);
        $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
        return $decrypted !== false ? $decrypted : $ciphertext;
    }

    /**
     * Récupère un paramètre de la table platform_settings
     */
    private function getSetting($key) {
        $this->ensureSettingsTable();
        $stmt = $this->pdo->prepare("SELECT setting_value FROM platform_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        return $stmt->fetchColumn();
    }

    /**
     * Sauvegarde un paramètre dans platform_settings (UPSERT)
     */
    private function setSetting($key, $value) {
        $this->ensureSettingsTable();
        $stmt = $this->pdo->prepare("
            INSERT INTO platform_settings (setting_key, setting_value) VALUES (?, ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()
        ");
        return $stmt->execute([$key, $value]);
    }

    /**
     * Affiche la page de configuration email SMTP
     */
    public function emailSettings() {
        // Protection stricte : Super-Admin uniquement (pas en mode impersonation)
        if (($_SESSION['user_role'] ?? '') !== 'super-admin') {
            header('Location: index.php');
            exit;
        }

        $smtpSettings = [
            'SMTP_HOST' => $this->getSetting('SMTP_HOST') ?: '',
            'SMTP_USER' => $this->getSetting('SMTP_USER') ?: '',
            'SMTP_PORT' => $this->getSetting('SMTP_PORT') ?: '465',
        ];
        
        // Déchiffrer le mot de passe pour affichage
        $encPass = $this->getSetting('SMTP_PASS');
        $smtpSettings['SMTP_PASS'] = $encPass ? $this->decryptValue($encPass) : '';
        
        require 'views/superadmin/email_settings.php';
    }

    /**
     * Sauvegarde les paramètres email SMTP
     */
    public function saveEmailSettings() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=superadmin_email_settings');
            exit;
        }

        $host = trim($_POST['smtp_host'] ?? '');
        $user = trim($_POST['smtp_user'] ?? '');
        $pass = $_POST['smtp_pass'] ?? '';
        $port = intval($_POST['smtp_port'] ?? 465);

        if (empty($host) || empty($user) || empty($pass) || $port <= 0) {
            header('Location: index.php?page=superadmin_email_settings&error=missing_fields');
            exit;
        }

        try {
            $this->setSetting('SMTP_HOST', $host);
            $this->setSetting('SMTP_USER', $user);
            $this->setSetting('SMTP_PASS', $this->encryptValue($pass)); // Chiffré !
            $this->setSetting('SMTP_PORT', (string)$port);

            header('Location: index.php?page=superadmin_email_settings&success=email_saved');
        } catch (\Exception $e) {
            error_log("Erreur sauvegarde SMTP : " . $e->getMessage());
            header('Location: index.php?page=superadmin_email_settings&error=email_save_failed');
        }
        exit;
    }

    /**
     * Envoie un email de test avec les paramètres fournis
     */
    public function testEmail() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=superadmin_email_settings');
            exit;
        }

        $host = trim($_POST['smtp_host'] ?? '');
        $user = trim($_POST['smtp_user'] ?? '');
        $pass = $_POST['smtp_pass'] ?? '';
        $port = intval($_POST['smtp_port'] ?? 465);

        if (empty($host) || empty($user) || empty($pass) || $port <= 0) {
            header('Location: index.php?page=superadmin_email_settings&error=missing_fields');
            exit;
        }

        // Tentative d'envoi via PHPMailer
        if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
            try {
                $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
                $mail->Timeout = 10;
                $mail->isSMTP();
                $mail->Host       = $host;
                $mail->SMTPAuth   = true;
                $mail->Username   = $user;
                $mail->Password   = $pass;
                $mail->Port       = $port;
                $mail->SMTPSecure = ($port == 465) ? 'ssl' : (($port == 587) ? 'tls' : '');
                $mail->CharSet    = 'UTF-8';

                $mail->setFrom($user, 'RHXtimes Platform');
                $mail->addAddress($user); // Envoyer à soi-même

                $mail->isHTML(true);
                $mail->Subject = '✅ Test SMTP RHXtimes - Configuration validée';
                $mail->Body = '
                    <div style="font-family: Inter, Arial, sans-serif; max-width: 500px; margin: 0 auto; padding: 30px;">
                        <div style="background: linear-gradient(135deg, #4f46e5, #7c3aed); padding: 30px; border-radius: 16px 16px 0 0; text-align: center;">
                            <h1 style="color: #fff; margin: 0; font-size: 22px;">✅ Configuration SMTP</h1>
                            <p style="color: rgba(255,255,255,0.8); margin: 8px 0 0; font-size: 14px;">Test réussi</p>
                        </div>
                        <div style="background: #fff; padding: 25px; border: 1px solid #e2e8f0; border-top: none; border-radius: 0 0 16px 16px;">
                            <p style="color: #334155; font-size: 15px; line-height: 1.6;">Votre configuration SMTP fonctionne correctement. Les alertes et liens de validation seront envoyés depuis cette adresse.</p>
                            <div style="background: #f8fafc; border-radius: 10px; padding: 15px; margin-top: 15px;">
                                <p style="margin: 0; font-size: 13px; color: #64748b;"><strong>Serveur :</strong> ' . htmlspecialchars($host) . '</p>
                                <p style="margin: 5px 0 0; font-size: 13px; color: #64748b;"><strong>Port :</strong> ' . $port . '</p>
                                <p style="margin: 5px 0 0; font-size: 13px; color: #64748b;"><strong>Date :</strong> ' . date('d/m/Y H:i:s') . '</p>
                            </div>
                        </div>
                    </div>';

                $mail->send();
                header('Location: index.php?page=superadmin_email_settings&success=email_test_sent');
                exit;
            } catch (\Exception $e) {
                error_log("Test SMTP échoué : " . $e->getMessage());
                header('Location: index.php?page=superadmin_email_settings&error=email_test_failed');
                exit;
            }
        } else {
            // PHPMailer non installé - Rapporter l'erreur réelle
            error_log("TEST EMAIL ÉCHOUÉ : PHPMailer non trouvé. Host=$host, User=$user, Port=$port");
            header('Location: index.php?page=superadmin_email_settings&error=phpmailer_missing');
            exit;
        }
    }

    /**
     * Méthode statique pour récupérer les paramètres SMTP depuis la BDD
     * Utilisée par NotificationService et d'autres services
     */
    public static function getSmtpConfig($pdo) {
        try {
            $settings = [];
            $keys = ['SMTP_HOST', 'SMTP_USER', 'SMTP_PASS', 'SMTP_PORT'];
            
            $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM platform_settings WHERE setting_key IN (?,?,?,?)");
            $stmt->execute($keys);
            
            while ($row = $stmt->fetch()) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
            
            // Déchiffrer le mot de passe
            if (!empty($settings['SMTP_PASS'])) {
                $dbName = getenv('DB_NAME') ?: 'rhtimes_saas';
                $key = hash('sha256', 'RHtimes_SMTP_' . $dbName . '_SecretKey2026', true);
                $data = base64_decode($settings['SMTP_PASS']);
                if ($data !== false && strpos($data, '::') !== false) {
                    list($iv, $encrypted) = explode('::', $data, 2);
                    $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
                    if ($decrypted !== false) $settings['SMTP_PASS'] = $decrypted;
                }
            }
            
            return $settings;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Supprime définitivement une entreprise et TOUTES ses données
     */
    public function deleteEnterprise() {
        if (($_SESSION['user_role'] ?? '') !== 'super-admin') {
            header('Location: index.php');
            exit;
        }

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            header('Location: index.php?page=superadmin_entreprises&error=invalid_id');
            exit;
        }

        try {
            $this->pdo->beginTransaction();

            // 1. Suppression des messages de support liés aux tickets de l'entreprise
            $this->pdo->prepare("DELETE FROM ticket_messages WHERE ticket_id IN (SELECT id FROM tickets WHERE entreprise_id = ?)")->execute([$id]);

            // 2. Suppression des tickets de support
            $this->pdo->prepare("DELETE FROM tickets WHERE entreprise_id = ?")->execute([$id]);

            // 3. Suppression des pointages des employés de l'entreprise
            $this->pdo->prepare("DELETE FROM pointages WHERE entreprise_id = ?")->execute([$id]);

            // 4. Suppression des justifications d'absence/retard
            $this->pdo->prepare("DELETE FROM justifications WHERE entreprise_id = ?")->execute([$id]);

            // 5. Suppression des plannings
            $this->pdo->prepare("DELETE FROM planning_lieux WHERE entreprise_id = ?")->execute([$id]);

            // 6. Suppression des ajustements de paie
            $this->pdo->prepare("DELETE FROM payroll_adjustments WHERE entreprise_id = ?")->execute([$id]);

            // 7. Suppression des logs d'audit (si la table existe)
            try {
                $this->pdo->prepare("DELETE FROM audit_logs WHERE entreprise_id = ?")->execute([$id]);
            } catch (Exception $e) {}

            // 8. Suppression des transactions
            $this->pdo->prepare("DELETE FROM transactions WHERE entreprise_id = ?")->execute([$id]);

            // 9. Suppression des employés (users)
            $this->pdo->prepare("DELETE FROM users WHERE entreprise_id = ?")->execute([$id]);

            // 10. Suppression des comptes administrateurs
            $this->pdo->prepare("DELETE FROM admins WHERE entreprise_id = ?")->execute([$id]);

            // 11. Suppression des sites (lieux)
            $this->pdo->prepare("DELETE FROM lieux WHERE entreprise_id = ?")->execute([$id]);

            // 12. Enfin, suppression de l'entreprise elle-même
            $this->pdo->prepare("DELETE FROM entreprises WHERE id = ?")->execute([$id]);

            $this->pdo->commit();
            header('Location: index.php?page=superadmin_entreprises&success=delete_ok');
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            error_log("Erreur suppression entreprise $id : " . $e->getMessage());
            header('Location: index.php?page=superadmin_entreprises&error=delete_failed');
        }
        exit;
    }

    // ==========================================
    // SELECTION DE LA PASSERELLE ACTIVE
    // ==========================================

    /**
     * Affiche la page de choix de la passerelle
     */
    public function gatewaySettings() {
        if (($_SESSION['user_role'] ?? '') !== 'super-admin') {
            header('Location: index.php');
            exit;
        }

        $activeGateway = $this->getSetting('ACTIVE_PAYMENT_GATEWAY') ?: 'cinetpay';
        require 'views/superadmin/gateway_settings.php';
    }

    /**
     * Sauvegarde la passerelle active
     */
    public function saveGatewaySettings() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=superadmin_gateway_settings');
            exit;
        }

        $gateway = $_POST['active_gateway'] ?? 'cinetpay';
        $this->setSetting('ACTIVE_PAYMENT_GATEWAY', $gateway);

        header('Location: index.php?page=superadmin_gateway_settings&success=gateway_saved');
        exit;
    }

    public static function getActiveGateway($pdo) {
        $stmt = $pdo->prepare("SELECT setting_value FROM platform_settings WHERE setting_key = 'ACTIVE_PAYMENT_GATEWAY'");
        $stmt->execute();
        return $stmt->fetchColumn() ?: 'cinetpay';
    }

    // ==========================================
    // CONFIGURATION CINETPAY
    // ==========================================

    /**
     * Affiche la page de configuration CinetPay
     */
    public function cinetpaySettings() {
        // Protection stricte : Super-Admin uniquement
        if (($_SESSION['user_role'] ?? '') !== 'super-admin') {
            header('Location: index.php');
            exit;
        }

        $cinetpaySettings = [
            'CINETPAY_API_KEY' => '',
            'CINETPAY_SITE_ID' => '',
        ];

        // Récupérer et déchiffrer les valeurs
        $encApiKey = $this->getSetting('CINETPAY_API_KEY');
        $encSiteId = $this->getSetting('CINETPAY_SITE_ID');

        $cinetpaySettings['CINETPAY_API_KEY'] = $encApiKey ? $this->decryptValue($encApiKey) : '';
        $cinetpaySettings['CINETPAY_SITE_ID'] = $encSiteId ? $this->decryptValue($encSiteId) : '';

        require 'views/superadmin/cinetpay_settings.php';
    }

    /**
     * Affiche la page de configuration Money Fusion
     */
    public function moneyfusionSettings() {
        if (($_SESSION['user_role'] ?? '') !== 'super-admin') {
            header('Location: index.php');
            exit;
        }

        $moneyfusionUrl = $this->getSetting('MONEYFUSION_API_URL');
        $moneyfusionUrl = $moneyfusionUrl ? $this->decryptValue($moneyfusionUrl) : '';

        require 'views/superadmin/moneyfusion_settings.php';
    }

    /**
     * Sauvegarde les paramètres Money Fusion
     */
    public function saveMoneyfusionSettings() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=superadmin_moneyfusion_settings');
            exit;
        }

        $apiUrl = trim($_POST['moneyfusion_api_url'] ?? '');

        if (empty($apiUrl)) {
            header('Location: index.php?page=superadmin_moneyfusion_settings&error=missing_fields');
            exit;
        }

        try {
            $this->setSetting('MONEYFUSION_API_URL', $this->encryptValue($apiUrl));
            header('Location: index.php?page=superadmin_moneyfusion_settings&success=moneyfusion_saved');
        } catch (\Exception $e) {
            error_log("Erreur sauvegarde Money Fusion : " . $e->getMessage());
            header('Location: index.php?page=superadmin_moneyfusion_settings&error=moneyfusion_save_failed');
        }
        exit;
    }

    // ==========================================
    // CONFIGURATION WHATSAPP
    // ==========================================

    public function whatsappSettings() {
        if (($_SESSION['user_role'] ?? '') !== 'super-admin') {
            header('Location: index.php');
            exit;
        }
        $whatsappNumber = $this->getSetting('WHATSAPP_NUMBER') ?: '';
        require 'views/superadmin/whatsapp_settings.php';
    }

    public function saveWhatsappSettings() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_SESSION['user_role'] ?? '') !== 'super-admin') {
            header('Location: index.php');
            exit;
        }

        Security::verifyCsrfToken($_POST['csrf_token'] ?? '');

        $number = trim($_POST['whatsapp_number'] ?? '');
        
        // Validation basique : doit commencer par + et être suivi de 7 à 15 chiffres
        if (!empty($number) && !preg_match('/^\+\d{7,15}$/', $number)) {
            header('Location: index.php?page=superadmin_whatsapp&error=invalid_format');
            exit;
        }

        try {
            $this->setSetting('WHATSAPP_NUMBER', $number);
            $this->logAction($_SESSION['admin_id'] ?? null, null, 'UPDATE_WHATSAPP_NUMBER', null, $number);
            header('Location: index.php?page=superadmin_whatsapp&success=updated');
        } catch (\Exception $e) {
            header('Location: index.php?page=superadmin_whatsapp&error=save_failed');
        }
        exit;
    }

    /**
     * Méthode statique pour récupérer les paramètres CinetPay depuis la BDD
     * Utilisée par SubscriptionController
     * Fallback vers les variables d'environnement (.env)
     */
    public static function getCinetpayConfig($pdo) {
        try {
            $settings = [];
            $keys = ['CINETPAY_API_KEY', 'CINETPAY_SITE_ID'];
            
            $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM platform_settings WHERE setting_key IN (?,?)");
            $stmt->execute($keys);
            
            while ($row = $stmt->fetch()) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
            
            // Déchiffrer les valeurs
            $dbName = getenv('DB_NAME') ?: 'rhtimes_saas';
            $key = hash('sha256', 'RHtimes_SMTP_' . $dbName . '_SecretKey2026', true);

            foreach (['CINETPAY_API_KEY', 'CINETPAY_SITE_ID'] as $k) {
                if (!empty($settings[$k])) {
                    $data = base64_decode($settings[$k]);
                    if ($data !== false && strpos($data, '::') !== false) {
                        list($iv, $encrypted) = explode('::', $data, 2);
                        $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
                        if ($decrypted !== false) $settings[$k] = $decrypted;
                    }
                }
            }

            // Si les valeurs sont présentes en BDD, les retourner
            if (!empty($settings['CINETPAY_API_KEY']) && !empty($settings['CINETPAY_SITE_ID'])) {
                return $settings;
            }
        } catch (\Exception $e) {
            // Silencieux : fallback vers .env
        }

        // Fallback vers les variables d'environnement
        return [
            'CINETPAY_API_KEY' => getenv('CINETPAY_API_KEY') ?: 'DEFAULT_API_KEY',
            'CINETPAY_SITE_ID' => getenv('CINETPAY_SITE_ID') ?: 'DEFAULT_SITE_ID',
        ];
    }

    public static function getMoneyfusionConfig($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT setting_value FROM platform_settings WHERE setting_key = 'MONEYFUSION_API_URL'");
            $stmt->execute();
            $val = $stmt->fetchColumn();
            
            if (!$val) return ['MONEYFUSION_API_URL' => ''];
            
            // Déchiffrer
            $dbName = getenv('DB_NAME') ?: 'rhtimes_saas';
            $key = hash('sha256', 'RHtimes_SMTP_' . $dbName . '_SecretKey2026', true);
            
            $data = base64_decode($val);
            if ($data !== false && strpos($data, '::') !== false) {
                list($iv, $encrypted) = explode('::', $data, 2);
                $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
                if ($decrypted !== false) return ['MONEYFUSION_API_URL' => $decrypted];
            }
        } catch (\Exception $e) {}
        return ['MONEYFUSION_API_URL' => ''];
    }

    // ==========================================
    // SÉCURITÉ CSRF
    // ==========================================

    private function generateCsrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    private function verifyCsrfToken($token) {
        if (!isset($_SESSION['csrf_token']) || empty($token)) return false;
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    // ==========================================
    // GESTION DES FORFAITS (PLANS)
    // ==========================================

    /**
     * Liste des plans
     */
    public function plansList() {
        if (($_SESSION['user_role'] ?? '') !== 'super-admin') {
            header('Location: index.php');
            exit;
        }

        $stmt = $this->pdo->query("SELECT * FROM plans ORDER BY id ASC");
        $plans = $stmt->fetchAll();

        require 'views/superadmin/plans_list.php';
    }

    /**
     * Formulaire de création / édition de plan
     */
    public function planForm() {
        if (($_SESSION['user_role'] ?? '') !== 'super-admin') {
            header('Location: index.php');
            exit;
        }

        $id = $_GET['id'] ?? null;
        $plan = null;

        if ($id) {
            $stmt = $this->pdo->prepare("SELECT * FROM plans WHERE id = ?");
            $stmt->execute([$id]);
            $plan = $stmt->fetch();
        }

        $csrf_token = $this->generateCsrfToken();
        require 'views/superadmin/plan_form.php';
    }

    /**
     * Enregistre ou met à jour un plan
     */
    public function savePlan() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=superadmin_plans');
            exit;
        }

        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            die("Erreur de sécurité : Jeton CSRF invalide.");
        }

        $id = $_POST['id'] ?? null;
        $nom = trim($_POST['nom'] ?? '');
        $montant_mensuel = (float)($_POST['montant_mensuel'] ?? 0);
        $montant_annuel_mensualise = (float)($_POST['montant_annuel_mensualise'] ?? 0);
        $max_employees = (int)($_POST['max_employees'] ?? 10);
        $max_sites = (int)($_POST['max_sites'] ?? 1);
        $max_managers = (int)($_POST['max_managers'] ?? 1);
        $trial_days = (int)($_POST['trial_days'] ?? 14);
        $support_type = $_POST['support_type'] ?? 'normal';

        // Nouvelles fonctionnalités (Feature Flags)
        $has_qr_code = isset($_POST['has_qr_code']) ? 1 : 0;
        $has_direct_scan = isset($_POST['has_direct_scan']) ? 1 : 0;
        $has_gps_precision = isset($_POST['has_gps_precision']) ? 1 : 0;
        $has_advanced_dashboard = isset($_POST['has_advanced_dashboard']) ? 1 : 0;
        $has_payroll_mgmt = isset($_POST['has_payroll_mgmt']) ? 1 : 0;
        $has_auto_payroll = isset($_POST['has_auto_payroll']) ? 1 : 0;
        $has_pdf_export = isset($_POST['has_pdf_export']) ? 1 : 0;
        $has_unlimited_history = isset($_POST['has_unlimited_history']) ? 1 : 0;
        $has_web_mobile = isset($_POST['has_web_mobile']) ? 1 : 0;
        $has_email_alerts = isset($_POST['has_email_alerts']) ? 1 : 0;
        $has_messaging = isset($_POST['has_messaging']) ? 1 : 0;

        if (empty($nom)) {
            header('Location: index.php?page=superadmin_plan_form&error=missing_name' . ($id ? "&id=$id" : ""));
            exit;
        }

        try {
            if ($id) {
                // Update
                $sql = "UPDATE plans SET nom = ?, montant_mensuel = ?, montant_annuel_mensualise = ?, max_employees = ?, max_sites = ?, max_managers = ?, trial_days = ?, support_type = ?, has_qr_code = ?, has_direct_scan = ?, has_gps_precision = ?, has_advanced_dashboard = ?, has_payroll_mgmt = ?, has_auto_payroll = ?, has_pdf_export = ?, has_unlimited_history = ?, has_web_mobile = ?, has_email_alerts = ?, has_messaging = ? WHERE id = ?";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$nom, $montant_mensuel, $montant_annuel_mensualise, $max_employees, $max_sites, $max_managers, $trial_days, $support_type, $has_qr_code, $has_direct_scan, $has_gps_precision, $has_advanced_dashboard, $has_payroll_mgmt, $has_auto_payroll, $has_pdf_export, $has_unlimited_history, $has_web_mobile, $has_email_alerts, $has_messaging, $id]);
            } else {
                // Insert
                $sql = "INSERT INTO plans (nom, montant_mensuel, montant_annuel_mensualise, max_employees, max_sites, max_managers, trial_days, support_type, has_qr_code, has_direct_scan, has_gps_precision, has_advanced_dashboard, has_payroll_mgmt, has_auto_payroll, has_pdf_export, has_unlimited_history, has_web_mobile, has_email_alerts, has_messaging) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$nom, $montant_mensuel, $montant_annuel_mensualise, $max_employees, $max_sites, $max_managers, $trial_days, $support_type, $has_qr_code, $has_direct_scan, $has_gps_precision, $has_advanced_dashboard, $has_payroll_mgmt, $has_auto_payroll, $has_pdf_export, $has_unlimited_history, $has_web_mobile, $has_email_alerts, $has_messaging]);
            }

            header('Location: index.php?page=superadmin_plans&success=plan_saved');
        } catch (\Exception $e) {
            die("ERREUR TECHNIQUE : " . $e->getMessage());
        }
        exit;
    }

    /**
     * Supprime un plan
     */
    public function deletePlan() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: index.php?page=superadmin_plans');
            exit;
        }

        // Vérifier si des entreprises utilisent ce plan
        $stmtCheck = $this->pdo->prepare("SELECT COUNT(*) FROM entreprises WHERE plan_id = ?");
        $stmtCheck->execute([$id]);
        if ($stmtCheck->fetchColumn() > 0) {
            header('Location: index.php?page=superadmin_plans&error=plan_in_use');
            exit;
        }

        try {
            $stmt = $this->pdo->prepare("DELETE FROM plans WHERE id = ?");
            $stmt->execute([$id]);
            header('Location: index.php?page=superadmin_plans&success=plan_deleted');
        } catch (\Exception $e) {
            header('Location: index.php?page=superadmin_plans&error=delete_failed');
        }
        exit;
    }
    /**
     * Répare toutes les signatures de sécurité en se basant sur le SEL actuel
     * Utile en cas de changement de machine ou de migration de .env
     */
    public function repairSignatures() {
        if (($_SESSION['user_role'] ?? '') !== 'super-admin') {
            header('Location: index.php');
            exit;
        }

        try {
            $this->pdo->beginTransaction();
            
            $stmt = $this->pdo->query("SELECT id, expiration_date FROM entreprises");
            $entreprises = $stmt->fetchAll();
            
            $count = 0;
            foreach ($entreprises as $e) {
                $newSignature = Security::generateExpirationSignature($e['id'], $e['expiration_date']);
                $upd = $this->pdo->prepare("UPDATE entreprises SET security_signature = ? WHERE id = ?");
                $upd->execute([$newSignature, $e['id']]);
                $count++;
            }
            
            $this->pdo->commit();
            header('Location: index.php?page=superadmin_security_log&success=' . urlencode("Signatures réparées : $count entreprises synchronisées."));
            exit;
        } catch (\Exception $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            header('Location: index.php?page=superadmin_security_log&error=Erreur de réparation');
            exit;
        }
    }

    /**
     * Affiche les anomalies de sécurité détectées
     */
    public function securityLog() {
        if (($_SESSION['user_role'] ?? '') !== 'super-admin') {
            header('Location: index.php');
            exit;
        }

        $logs = [];
        // On récupère tous les fichiers de log quotidiens
        $logFiles = glob('security_anomalies_*.log');
        
        if ($logFiles) {
            // Trier les fichiers par date (du plus récent au plus ancien)
            array_multisort(array_map('filemtime', $logFiles), SORT_DESC, $logFiles);

            foreach ($logFiles as $logFile) {
                if (file_exists($logFile)) {
                    $content = file_get_contents($logFile);
                    $lines = explode(PHP_EOL, trim($content));
                    foreach ($lines as $line) {
                        if (empty(trim($line))) continue;
                        
                        // Parsing sommaire
                        $logData = [
                            'raw' => $line,
                            'is_critical' => (strpos($line, 'IP Bloquée') !== false || strpos($line, 'replay') !== false || strpos($line, 'CRITICAL') !== false)
                        ];
                        $logs[] = $logData;
                    }
                }
            }
            // Note: les fichiers sont déjà triés par date décroissante, 
            // mais à l'intérieur de chaque fichier les lignes sont chronologiques.
            // On peut inverser l'ensemble si on veut les plus récents en haut.
            $logs = array_reverse($logs);
        }

        require 'views/superadmin/security_log.php';
    }

    /**
     * Génère un snapshot complet des métriques de la plateforme
     */
    public function generateStatsSnapshot() {
        $mrr = 0; $nbActives = 0; $planDistribution = []; $dau = 0; $mau = 0;
        $anomalies = 0; $totalEnt = 1; $activationRate = 0; $trialToPaid = 0;
        $conversionRate = 3.5; $churnRate = 0; $counts = []; $adminMapping = [];

        // 1. MRR & Actives
        try {
            $stmtMrr = $this->pdo->query("SELECT SUM(p.montant_mensuel) as total_mrr FROM entreprises e JOIN plans p ON e.plan_id = p.id WHERE e.statut = 'Active'");
            $mrr = $stmtMrr->fetchColumn() ?: 0;
            $nbActives = $this->pdo->query("SELECT COUNT(*) FROM entreprises WHERE statut = 'Active'")->fetchColumn();
        } catch(Exception $e) {}

        // 2. Plan Distribution
        try {
            $stmtDist = $this->pdo->query("SELECT p.nom, COUNT(*) as nb FROM entreprises e JOIN plans p ON e.plan_id = p.id GROUP BY p.nom");
            $planDistribution = $stmtDist->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch(Exception $e) {}

        // 3. Engagement
        try {
            $dau = $this->pdo->query("SELECT COUNT(DISTINCT user_id) FROM pointages WHERE DATE(heure_pointage) = CURDATE()")->fetchColumn();
            $mau = $this->pdo->query("SELECT COUNT(DISTINCT user_id) FROM pointages WHERE MONTH(heure_pointage) = MONTH(NOW()) AND YEAR(heure_pointage) = YEAR(NOW())")->fetchColumn();
            $anomalies = $this->pdo->query("SELECT COUNT(*) FROM pointages WHERE is_anomaly = 1 AND MONTH(heure_pointage) = MONTH(NOW())")->fetchColumn();
        } catch(Exception $e) {}

        // 4. Conversion & Taux
        try {
            $totalEnt = $this->pdo->query("SELECT COUNT(*) FROM entreprises")->fetchColumn() ?: 1;
            $nbPaid = $this->pdo->query("SELECT COUNT(*) FROM entreprises e JOIN plans p ON e.plan_id = p.id WHERE p.montant_mensuel > 0")->fetchColumn();
            $trialToPaid = ($nbPaid / $totalEnt) * 100;
            $nbExpired = $totalEnt - $nbActives;
            $churnRate = ($nbExpired / $totalEnt) * 100;

            $sqlAct = "SELECT COUNT(DISTINCT e.id) FROM entreprises e JOIN lieux l ON e.id = l.entreprise_id JOIN users u ON e.id = u.entreprise_id WHERE u.is_active = 1";
            $nbActivated = $this->pdo->query($sqlAct)->fetchColumn();
            $activationRate = ($nbActivated / $totalEnt) * 100;
        } catch(Exception $e) {}

        // 5. Visiteurs
        try {
            $nbTotalVisitors = $this->pdo->query("SELECT COUNT(DISTINCT ip) FROM stats_visitors WHERE visit_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn();
            $conversionRate = $nbTotalVisitors > 0 ? ($totalEnt / $nbTotalVisitors) * 100 : 0;
        } catch (Exception $e) { $nbTotalVisitors = 0; }

        // 6. Mapping & Counts
        try {
            $stmtUsers = $this->pdo->query("SELECT entreprise_id, COUNT(*) as count FROM users WHERE is_active = 1 GROUP BY entreprise_id");
            while($r = $stmtUsers->fetch()) { $counts[$r['entreprise_id']] = $r['count']; }

            $stmtAdminIds = $this->pdo->query("SELECT id, entreprise_id FROM admins WHERE role = 'owner'");
            while($ra = $stmtAdminIds->fetch()) {
                if (!isset($adminMapping[$ra['entreprise_id']])) $adminMapping[$ra['entreprise_id']] = $ra['id'];
            }
        } catch(Exception $e) {}

        $data = [
            'mrr' => $mrr, 'nbActives' => $nbActives, 'planDistribution' => $planDistribution,
            'dau' => $dau, 'mau' => $mau, 'anomalies' => $anomalies, 'totalEnt' => $totalEnt,
            'activationRate' => $activationRate, 'trialToPaid' => $trialToPaid, 
            'conversionRate' => $conversionRate, 'churnRate' => $churnRate, 
            'counts' => $counts, 'adminMapping' => $adminMapping,
            'nbTotalVisitors' => $nbTotalVisitors ?? 0,
            'generated_at' => date('Y-m-d H:i:s')
        ];

        // Stocker en BDD pour le Snapshot du jour
        try {
            $stmt = $this->pdo->prepare("INSERT INTO platform_stats_snapshots (snapshot_date, stats_json) VALUES (CURDATE(), ?) ON DUPLICATE KEY UPDATE stats_json = VALUES(stats_json)");
            $stmt->execute([json_encode($data)]);
        } catch(Exception $e) {}

        return $data;
    }

    /**
     * Liste les logs d'audit avec pagination
     */
    public function auditLogsList() {
        if (($_SESSION['user_role'] ?? '') !== 'super-admin') {
            header('Location: index.php');
            exit;
        }

        $page = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
        $limit = 50;
        $offset = ($page - 1) * $limit;

        $totalLogs = $this->pdo->query("SELECT COUNT(*) FROM audit_logs")->fetchColumn() ?: 0;
        $totalPages = ceil($totalLogs / $limit);

        try {
            // Jointure pour avoir les noms lisibles au lieu des IDs
            $sql = "SELECT al.*, a.email as admin_email, e.nom as entreprise_nom 
                    FROM audit_logs al
                    LEFT JOIN admins a ON al.admin_id = a.id
                    LEFT JOIN entreprises e ON al.entreprise_id = e.id
                    ORDER BY al.created_at DESC 
                    LIMIT ? OFFSET ?";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(1, $limit, PDO::PARAM_INT);
            $stmt->bindValue(2, $offset, PDO::PARAM_INT);
            $stmt->execute();
            $logs = $stmt->fetchAll();
        } catch (PDOException $e) {
            $logs = [];
        }

        require 'views/superadmin/audit_logs.php';
    }
}
