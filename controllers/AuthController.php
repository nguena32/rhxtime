<?php
class AuthController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Helper privé : rend la vue login dans le layout public.
     * Accepte un message d'erreur optionnel via $error.
     */
    private function renderLogin($error = null) {
        global $pdo;
        $page_title = "Connexion | RHXtimes";
        ob_start();
        require 'views/login.php';
        $content = ob_get_clean();
        // Injecter directement dans le buffer existant d'index.php (déjà vidé par routes.php)
        require 'views/layouts/auth.php';
    }

    public function loginView() {
        if (!isset($_COOKIE['washmore_device_id'])) {
            $deviceId = 'dev_' . bin2hex(random_bytes(16));
            $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
            setcookie('washmore_device_id', $deviceId, [
                'expires' => time() + (10 * 365 * 24 * 60 * 60),
                'path' => '/',
                'domain' => '',
                'secure' => $isSecure,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            $_COOKIE['washmore_device_id'] = $deviceId;
        }

        if (isset($_SESSION['admin_id'])) { header('Location: index.php?page=admin_dashboard'); exit; }
        if (isset($_SESSION['user_id'])) { header('Location: index.php?page=employee_scan'); exit; }

        $this->renderLogin();
    }

    public function login() {
        Security::enforceCsrfGuard();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $identifiant = trim($_POST['identifiant'] ?? '');
            $password = $_POST['password'] ?? '';
            $clientDeviceId = $_COOKIE['washmore_device_id'] ?? '';

            // --- RATE LIMITING SECURITY ---
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
            $stmtRate = $this->pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE ip_address = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
            $stmtRate->execute([$ip]);
            if ($stmtRate->fetchColumn() >= 5) {
                $error = "Trop de tentatives échouées. Veuillez réessayer dans 15 minutes.";
                $this->renderLogin($error);
                return;
            }
            // --- END RATE LIMITING ---

            $adminModel = new Admin($this->pdo);
            $userModel = new User($this->pdo);

            $admin = $adminModel->findByEmail($identifiant);

            if ($admin) {
                if (password_verify($password, $admin['password'])) {
                    
                    // ── Guard email_verified_at ──────────────────────────────
                    // Les super-admins ne sont jamais bloqués (leur compte est géré manuellement)
                    if ($admin['role'] !== 'super-admin' && empty($admin['email_verified_at'])) {
                        $error = "Votre adresse email n'a pas encore été vérifiée. Consultez votre boîte mail.";
                        // Stocker l'email en session temporaire pour pré-remplir le formulaire de renvoi
                        $_SESSION['pending_verification_email'] = $admin['email'];
                        header('Location: index.php?page=verify_email_pending&email=' . urlencode($admin['email']) . '&from=login');
                        exit;
                    }
                    // ── Fin Guard ────────────────────────────────────────────

                    // Réinitialiser les tentatives
                    $this->pdo->prepare("DELETE FROM login_attempts WHERE ip_address = ?")->execute([$ip]);

                    // RBAC - Admins (Levels 1, 2, 3)
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_email'] = $admin['email'];
                    $_SESSION['user_role'] = $admin['role'] ?? 'owner';
                    $_SESSION['entreprise_id'] = $admin['entreprise_id'];
                    $_SESSION['admin_lieu_id'] = $admin['lieu_id'] ?? null;

                    // Définition de l'auth_level pour le filtrage
                    if ($_SESSION['user_role'] === 'super-admin') {
                        $_SESSION['auth_level'] = 1; // Accès total
                    } elseif ($_SESSION['user_role'] === 'owner') {
                        $_SESSION['auth_level'] = 2; // Accès financier
                    } elseif ($_SESSION['user_role'] === 'manager') {
                        $_SESSION['auth_level'] = 3; // Accès opérationnel
                    } else {
                        $_SESSION['auth_level'] = 2; // Fallback
                    }

                    if (session_status() === PHP_SESSION_ACTIVE) {
                        session_regenerate_id(true);
                    }

                    // Redirection selon le niveau admin
                    if ($_SESSION['auth_level'] === 1) {
                         header('Location: index.php?page=superadmin_dashboard');
                    } else {
                         header('Location: index.php?page=admin_dashboard');
                    }
                    exit;

                } else {
                    $this->pdo->prepare("INSERT INTO login_attempts (ip_address, identifiant) VALUES (?, ?)")->execute([$ip, $identifiant]);
                    $error = "Mot de passe Admin incorrect.";
                    $this->renderLogin($error);
                    return;
                }
            }

            $user = $userModel->findByEmailOrPhone($identifiant);

            if (!$user) {
                $this->pdo->prepare("INSERT INTO login_attempts (ip_address, identifiant) VALUES (?, ?)")->execute([$ip, $identifiant]);
                $error = "Utilisateur inconnu (vérifiez l'email ou le téléphone).";
                $this->renderLogin($error);
                return;
            }

            if (!password_verify($password, $user['password'])) {
                $this->pdo->prepare("INSERT INTO login_attempts (ip_address, identifiant) VALUES (?, ?)")->execute([$ip, $identifiant]);
                $error = "Mot de passe incorrect.";
                $this->renderLogin($error);
                return;
            }

            if (isset($user['is_active']) && $user['is_active'] == 0) {
                $error = "Compte suspendu par l'administrateur.";
                $this->renderLogin($error);
                return;
            }

            if (empty($clientDeviceId)) {
                $error = "Erreur technique : Impossible d'identifier l'appareil (Cookies désactivés ?).";
                $this->renderLogin($error);
                return;
            }

            /* COMMENTÉ : Autoriser la connexion sur n'importe quel machine
            if (empty($user['device_id'])) {
                $userModel->updateDeviceId($user['id'], $clientDeviceId);
            } else {
                if ($user['device_id'] !== $clientDeviceId) {
                    $error = "Sécurité : Ce compte est déjà lié à un autre téléphone. Demandez un Reset Appareil à l'admin.";
                    $this->renderLogin($error);
                    return;
                }
            }
            */

            // Réinitialiser les tentatives
            $this->pdo->prepare("DELETE FROM login_attempts WHERE ip_address = ?")->execute([$ip]);

            // RBAC - Employés (Level 4)
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nom'] = $user['prenom'];
            $_SESSION['user_lieu_id'] = $user['lieu_id'];
            
            $_SESSION['user_role'] = 'employee';
            $_SESSION['entreprise_id'] = $user['entreprise_id'];
            $_SESSION['auth_level'] = 4; // Accès scan uniquement
            
            if (session_status() === PHP_SESSION_ACTIVE) {
                // Régénération de session pour prévenir la fixation de session
                session_regenerate_id(true);
            }

            header('Location: index.php?page=employee_scan');
            exit;
        }
    }
    
    public function logout() {
        session_destroy();
        header('Location: index.php?page=login');
        exit;
    }

    public function updatePassword() {
        Security::enforceCsrfGuard();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old_pass = $_POST['old_password'];
            $new_pass = $_POST['new_password'];
            $confirm_pass = $_POST['confirm_password'];

            if ($new_pass !== $confirm_pass) {
                $this->redirectBack("Les nouveaux mots de passe ne correspondent pas.", null);
            }

            if (isset($_SESSION['admin_id'])) {
                $id = $_SESSION['admin_id'];
                $redirect = 'index.php?page=admin_dashboard';
                $model = new Admin($this->pdo);
                $userData = $model->findById($id);
                $currentHash = $userData['password'];
            } elseif (isset($_SESSION['user_id'])) {
                $id = $_SESSION['user_id'];
                $redirect = 'index.php?page=employee_scan';
                $model = new User($this->pdo);
                $userData = $model->findById($id);
                $currentHash = $userData['password'];
            } else {
                header('Location: index.php?page=login');
                exit;
            }

            if (!password_verify($old_pass, $currentHash)) {
                $this->redirectBack("L'ancien mot de passe est incorrect.", $redirect);
            }

            $newHash = password_hash($new_pass, PASSWORD_DEFAULT);
            $model->updatePassword($id, $newHash);

            header("Location: $redirect&success=password_updated");
            exit;
        }
    }

    private function redirectBack($error, $url = null) {
        $target = 'index.php'; // Failback hardcodé sans Open Redirect direct
        if ($url !== null) {
            $target = $url;
        }
        
        if (strpos($target, '?') !== false) {
            header("Location: $target&error=" . urlencode($error));
        } else {
            header("Location: $target?error=" . urlencode($error));
        }
        exit;
    }
}