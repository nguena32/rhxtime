<?php
// 0. Mode Production STRICT (Sécurité Garantie : Logs internes uniquement)
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// -- HEADERS DE SECURITE GLOBAUX --
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-eval' 'unsafe-inline' https://cdn.jsdelivr.net https://unpkg.com https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com; font-src 'self' data: https://fonts.gstatic.com https://cdnjs.cloudflare.com; img-src 'self' data: blob:; object-src 'none'; frame-ancestors 'none';");
header("X-XSS-Protection: 1; mode=block");

// 1. Démarrer le tampon de sortie avec sécurité de session
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_secure' => isset($_SERVER['HTTPS']),
        'cookie_samesite' => 'Lax',
        'use_strict_mode' => true
    ]);
}
ob_start();

// Si vous utilisez un fichier de sécurité externe
if (file_exists('core/Security.php')) {
    require_once 'core/Security.php';
}

if (file_exists('core/EventManager.php')) {
    require_once 'core/EventManager.php';
}

require_once 'config/db.php';

// Chargement des bibliothèques externes (PHPMailer, etc.)
if (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
}

spl_autoload_register(function ($class_name) {
    $directories = ['models/', 'controllers/', 'utils/'];
    foreach ($directories as $dir) {
        $file = $dir . $class_name . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

$csrf_token = Security::generateCsrfToken();

$page = $_GET['page'] ?? 'landing';

// --- IMPERSONATION / SUPPORT ---
$isImpersonating = isset($_SESSION['original_admin_id']);

// --- VERIFICATION TRIAL / ABONNEMENT SAAS ---
$exemptedPages = [
    'landing',
    'pricing',
    'register',
    'register_submit',
    'login',
    'auth_login',
    'logout',
    'terms',
    'privacy',
    'subscription_init',
    'subscription_return',
    'subscription_notify',
    'superadmin_stop_impersonate',
    'superadmin_dashboard',
    'superadmin_impersonate',
    'download_invoice',
    'superadmin_email_settings',
    'superadmin_save_email_settings',
    'superadmin_test_email',
    'superadmin_cinetpay_settings',
    'superadmin_save_cinetpay_settings',
    'superadmin_plans',
    'superadmin_plan_form',
    'superadmin_plan_save',
    'superadmin_plan_delete',
    'superadmin_security_log',
    // ── Email verification ──
    'verify_email_pending',
    'verify_email',
    'resend_verification',
];

if (isset($_SESSION['admin_id']) && !in_array($page, $exemptedPages)) {
    try {
        // Sécurité Multi-Tenant : Un Admin/Super-Admin ne peut pas accéder aux pages 'admin_' 
        // sans avoir une entreprise_id définie (cas du Super-Admin non-impersonnalisé).
        // Exception faite pour les pages de PUBLICITÉ qui sont globales à la plateforme.
        $globalPages = ['admin_pub', 'admin_save_pub'];
        if (strpos($page, 'admin_') === 0 && !in_array($page, $globalPages) && !isset($_SESSION['entreprise_id'])) {
            if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'super-admin') {
                header('Location: index.php?page=superadmin_dashboard&error=select_enterprise_first');
            } else {
                header('Location: index.php?page=login&error=session_corrupted');
            }
            exit;
        }

        // Sécurité : Ne pas exécuter les checks d'expiration si entreprise_id est absent
        // (cas Super-Admin non impersonalisé — déjà géré par le guard ci-dessus)
        if (!empty($_SESSION['entreprise_id'])) {
            $stmtExpr = $pdo->prepare("SELECT expiration_date, security_signature FROM entreprises WHERE id = ?");
            $stmtExpr->execute([$_SESSION['entreprise_id']]);
            $entData = $stmtExpr->fetch();
            $dateExp = $entData['expiration_date'] ?? null;
            $dbSignature = $entData['security_signature'] ?? null;

            // Exception: Le Super-Admin (auth_level == 1) ou en Mode Support bypass
            $isSuperAdmin = (isset($_SESSION['auth_level']) && $_SESSION['auth_level'] == 1) || isset($_SESSION['original_auth_level']);

            $expectedSignature = Security::generateExpirationSignature($_SESSION['entreprise_id'], $dateExp);
            if (!$isSuperAdmin && $dbSignature !== $expectedSignature) {
                Security::logAnomaly("FRAUDE DÉTECTÉE: Falsification de la date d'expiration en base (Signature KO)", $_SESSION['entreprise_id']);
                try {
                    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
                    $pdo->prepare("INSERT INTO audit_logs (admin_id, entreprise_id, action, ip_address) VALUES (?, ?, ?, ?)")->execute([$_SESSION['admin_id'] ?? null, $_SESSION['entreprise_id'], 'FRAUDE_SIGNATURE_EXPIRATION', $ip]);
                } catch (\Exception $e) {
                }
                session_destroy();
                header('HTTP/1.1 403 Forbidden');
                require_once 'views/errors/403.php';
                exit;
            }

            if (!$isSuperAdmin && $dateExp && strtotime($dateExp) < time()) {
                if (isset($_SESSION['admin_id'])) {
                    header('Location: index.php?error=trial_expired#pricing');
                    exit;
                } else {
                    header('HTTP/1.1 403 Forbidden');
                    require_once 'views/errors/403.php';
                    exit;
                }
            }
        }
    } catch (PDOException $e) {
    }
}

// 2. Nettoyer tout contenu envoyé accidentellement avant
ob_clean();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>RHXtimes - Pointage</title>

    <?php
    // Dynamique base URL pour éviter les 404 si on accède sans slash final ("domain.com/rhxtimes")
    $base_dir = rtrim(dirname($_SERVER['PHP_SELF']), '\\/') . '/';
    ?>

    <!-- PWA -->
    <link rel="manifest" href="<?= $base_dir ?>manifest.json">
    <meta name="theme-color" content="#1E293B">
    <!-- iOS PWA -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="RHXtimes">
    <link rel="apple-touch-icon" href="<?= $base_dir ?>assets/images/pwa.png">

    <link rel="icon" type="image/png" href="<?= $base_dir ?>assets/images/favicon.png">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base_dir ?>assets/css/style.css?v=1.6">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .swal2-popup {
            font-family: 'Inter', sans-serif !important;
            border-radius: 20px !important;
        }

        .swal2-styled.swal2-confirm {
            background: var(--color-primary) !important;
            border-radius: 10px !important;
        }

        .swal2-styled.swal2-cancel {
            border-radius: 10px !important;
        }
    </style>
</head>

<body>

    <?php
    require 'core/routes.php';

    // Envoi du tampon de sortie
    ob_end_flush();
    ?>

    <div id="passModal"
        style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:2000; align-items:center; justify-content:center;">
        <div class="card" style="width:90%; max-width:400px; padding:20px; position:relative;">
            <button onclick="document.getElementById('passModal').style.display='none'"
                style="position:absolute; top:15px; right:15px; border:none; background:none; font-size:20px; cursor:pointer;">&times;</button>
            <h3 class="text-h2" style="margin-bottom:20px;">Sécurité</h3>

            <form method="POST" action="index.php?page=auth_update_password">
                <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken() ?>">
                <div class="form-group">
                    <label>Ancien mot de passe</label>
                    <input type="password" name="old_password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Nouveau mot de passe</label>
                    <input type="password" name="new_password" class="form-control" required minlength="6">
                </div>
                <div class="form-group">
                    <label>Confirmer nouveau</label>
                    <input type="password" name="confirm_password" class="form-control" required minlength="6">
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%; margin-top:10px;">Modifier</button>
            </form>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('appSidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            sidebar.classList.toggle('open');
            overlay.classList.toggle('open');
        }

        function openPassModal() {
            document.getElementById('passModal').style.display = 'flex';
        }

        // Premium SweetAlert2 Styling
        const swalConfig = {
            fontFamily: "'Inter', sans-serif",
            confirmButtonColor: '#4F46E5',
            borderRadius: '16px'
        };

        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4500,
            timerProgressBar: true,
            background: '#ffffff',
            color: '#1e293b',
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
                toast.style.borderRadius = '16px';
                toast.style.boxShadow = '0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)';
            }
        });

        // Custom CSS for SWAL Premium Look
        const style = document.createElement('style');
        style.innerHTML = `
        .swal2-popup { border-radius: 24px !important; padding: 2em !important; font-family: 'Inter', sans-serif !important; }
        .swal2-title { font-weight: 800 !important; color: #1e293b !important; }
        .swal2-html-container { color: #64748b !important; line-height: 1.6 !important; }
        .swal2-confirm { border-radius: 12px !important; padding: 12px 30px !important; font-weight: 700 !important; background: linear-gradient(135deg, #4F46E5 0%, #4338ca 100%) !important; box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.3) !important; }
        .swal2-cancel { border-radius: 12px !important; font-weight: 600 !important; }
    `;
        document.head.appendChild(style);

        // Global CSRF for Sync
        const CSRF_TOKEN = '<?= Security::generateCsrfToken() ?>';
    </script>
    
    <!-- Offline & Sync Engine -->
    <script src="assets/js/offline-db.js"></script>
    <script src="assets/js/sync-engine.js"></script>
    <script>
        /**
         * Vérifie si le quota est atteint avant d'ouvrir un formulaire d'ajout
         */
        function checkQuotaAndOpen(type, current, limit, openCallback) {
            if (current >= limit) {
                Swal.fire({
                    title: 'Quota atteint !',
                    html: `<div style="text-align:center;">
                            <p>Vous avez atteint la limite de votre plan actuel (<strong>${limit} ${type}</strong>).</p>
                            <p style="margin-top:10px; color:#64748b;">Souhaitez-vous changer de plan pour augmenter vos quotas ?</p>
                           </div>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fa-solid fa-arrow-up-right-from-square"></i> Changer de plan',
                    cancelButtonText: 'Plus tard',
                    confirmButtonColor: '#4F46E5'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'index.php?page=admin_billing';
                    }
                });
            } else {
                openCallback();
            }
        }
    </script>

    <script>
        <?php if (isset($_GET['error'])):
            $rawError = $_GET['error'];
            $errMsgTemp = str_replace('_', ' ', $rawError);
            if ($rawError === 'select_enterprise_first')
                $errMsgTemp = "Veuillez d'abord sélectionner une entreprise via le Mode Support.";
            $errMsg = Security::html($errMsgTemp);
            ?>
            Swal.fire({
                icon: 'error',
                title: 'Oups !',
                html: `<div style="font-weight:500;"><?= $errMsg ?></div>`,
                confirmButtonText: 'Fermer'
            });

            // Nettoyage immédiat pour éviter la persistence au refresh
            const cleanUrl = new URL(window.location.href);
            cleanUrl.searchParams.delete('error');
            window.history.replaceState({}, '', cleanUrl);
        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>
            Toast.fire({
                icon: 'success',
                title: '<?= Security::html($_GET['success'] === '1' ? 'Opération effectuée !' : str_replace('_', ' ', $_GET['success'])) ?>'
            });

            const cleanUrlSuccess = new URL(window.location.href);
            cleanUrlSuccess.searchParams.delete('success');
            window.history.replaceState({}, '', cleanUrlSuccess);
        <?php endif; ?>

        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('sw.js?v=2').catch(err => { });
            });
        }
    </script>
</body>

<?php
// --- WEB-CRON FALLBACK ---
// Si des emails sont en attente, on tente d'en envoyer quelques-uns à la fin de la requête.
// Cela garantit l'envoi même sans Cron système.
if (isset($pdo)) {
    try {
        $stmtCheck = $pdo->query("SELECT id FROM email_queue WHERE statut = 'PENDING' AND attempts < 3 LIMIT 1");
        if ($stmtCheck->fetch()) {
            include_once 'utils/NotificationService.php';
            $stmtQ = $pdo->prepare("SELECT * FROM email_queue WHERE statut = 'PENDING' AND attempts < 3 ORDER BY created_at ASC LIMIT 3");
            $stmtQ->execute();
            $pendingEmails = $stmtQ->fetchAll();
            
            foreach ($pendingEmails as $mail) {
                $pdo->prepare("UPDATE email_queue SET attempts = attempts + 1 WHERE id = ?")->execute([$mail['id']]);
                $smtp = NotificationService::getSmtpCredentials($pdo);
                $res = NotificationService::sendMail($mail['to_email'], $mail['subject'], $mail['body'], $smtp);
                if ($res['success']) {
                    $pdo->prepare("UPDATE email_queue SET statut = 'SENT', last_error = NULL WHERE id = ?")->execute([$mail['id']]);
                } else {
                    try {
                        $pdo->prepare("UPDATE email_queue SET last_error = ? WHERE id = ?")->execute([$res['error'], $mail['id']]);
                    } catch (Exception $ex) {}
                }
            }
        }
    } catch (Exception $e) {
        // Silencieux
    }
}
?>
</html>