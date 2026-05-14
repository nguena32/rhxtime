<?php
// core/routes.php

switch($page) {
    case 'run_migration_support':
            $pdo->exec("CREATE TABLE IF NOT EXISTS tickets (
                id INT AUTO_INCREMENT PRIMARY KEY,
                admin_id INT NOT NULL,
                entreprise_id INT NOT NULL,
                subject VARCHAR(255) NOT NULL,
                status ENUM('open', 'replied', 'closed') DEFAULT 'open',
                priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_ticket_admin (admin_id),
                INDEX idx_ticket_entreprise (entreprise_id),
                INDEX idx_ticket_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
            $pdo->exec("CREATE TABLE IF NOT EXISTS ticket_messages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                ticket_id INT NOT NULL,
                sender_id INT NOT NULL,
                sender_role ENUM('admin', 'superadmin') NOT NULL,
                message TEXT NOT NULL,
                attachment_path VARCHAR(255) DEFAULT NULL,
                is_read TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_msg_ticket (ticket_id),
                INDEX idx_msg_read (is_read),
                FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
            echo "Migration réussie !";
        exit;
        break;
    // --- LOGIQUE DE TRACKING VISITEURS ---
    case 'landing':
        try {
            $visitorIp = $_SERVER['REMOTE_ADDR'];
            $today = date('Y-m-d');
            $stmtTrack = $pdo->prepare("INSERT IGNORE INTO stats_visitors (ip, visit_date) VALUES (?, ?)");
            $stmtTrack->execute([$visitorIp, $today]);
        } catch (Exception $e) { /* Silencieux pour ne pas bloquer l'affichage */ }

        ob_clean();
        ob_start();
        require 'views/landing.php';
        $content = ob_get_clean();
        require 'views/layouts/public.php';
        exit;
        break;
    case 'contact_submit':
        require_once 'controllers/ContactController.php';
        (new ContactController($pdo))->handleContact();
        break;
    case 'pricing':
        ob_clean();
        ob_start();
        require 'views/pricing.php';
        $content = ob_get_clean();
        require 'views/layouts/public.php';
        exit;
        break;
    case 'terms':
        ob_clean();
        ob_start();
        require 'views/terms.php';
        $content = ob_get_clean();
        require 'views/layouts/public.php';
        exit;
        break;
    case 'privacy':
        ob_clean();
        ob_start();
        require 'views/privacy.php';
        $content = ob_get_clean();
        require 'views/layouts/public.php';
        exit;
        break;
    case 'admin_support':
        (new SupportController($pdo))->adminList();
        break;
    case 'admin_support_create':
        (new SupportController($pdo))->adminCreate();
        break;
    case 'admin_support_view':
        (new SupportController($pdo))->adminView();
        break;
    case 'admin_support_reply':
        (new SupportController($pdo))->adminReply();
        break;
    case 'superadmin_support':
        (new SupportController($pdo))->superAdminList();
        break;
    case 'superadmin_support_view':
        (new SupportController($pdo))->superAdminView();
        break;
    case 'superadmin_support_reply':
        (new SupportController($pdo))->superAdminReply();
        break;
    case 'superadmin_support_close':
        (new SupportController($pdo))->superAdminClose();
        break;
    case 'register':
        ob_clean();
        ob_start();
        $page_title = "Créer un compte | RHXtimes";
        require 'views/register.php';
        $content = ob_get_clean();
        require 'views/layouts/auth.php';
        exit;
        break;
    case 'register_submit':
        (new RegistrationController($pdo))->handleSignup();
        break;

    // --- AUTHENTIFICATION ---
    case 'login':
        ob_clean(); // Vide le contenu HTML d'index.php sans fermer le tampon
        (new AuthController($pdo))->loginView();
        exit;
        break;
    case 'auth_login': 
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=login');
            exit;
        }
        (new AuthController($pdo))->login();
        break;
    case 'api_scan':
        (new ApiController($pdo))->handleScan();
        break;
    case 'api_pointages':
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        if (!isset($_SESSION['admin_id'])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Non autorisé']);
            exit;
        }
        (new ApiController($pdo))->getPaginatedPointages();
        exit;
    case 'auth_update_password': // <--- NOUVELLE ROUTE AJOUTÉE
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $target = isset($_SESSION['user_id']) ? 'employee_scan' : (isset($_SESSION['admin_id']) ? 'admin_dashboard' : 'login');
            header('Location: index.php?page=' . $target);
            exit;
        }
        (new AuthController($pdo))->updatePassword();
        break;
    case 'logout':
        (new AuthController($pdo))->logout();
        break;

    // ── EMAIL VERIFICATION ───────────────────────────────────────────────────
    case 'verify_email_pending':
        // Page "Vérifiez votre boîte mail" — affichée après inscription
        require 'views/verify_email_pending.php';
        break;

    case 'verify_email':
        // Clic sur le lien dans l'email — traitement du token
        require_once 'controllers/EmailVerificationController.php';
        (new EmailVerificationController($pdo))->verify();
        break;

    case 'resend_verification':
        // Formulaire de renvoi d'email de vérification (POST uniquement)
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=login');
            exit;
        }
        require_once 'controllers/EmailVerificationController.php';
        (new EmailVerificationController($pdo))->resend();
        break;
    // ── FIN EMAIL VERIFICATION ───────────────────────────────────────────────

    // --- PAIEMENTS / ABONNEMENTS SAAS ---
    case 'subscription_init':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.php'); exit; }
        (new SubscriptionController($pdo))->initPayment();
        break;
    case 'subscription_return':
        (new SubscriptionController($pdo))->returnUrl();
        break;
    case 'subscription_notify':
        (new SubscriptionController($pdo))->cinetpayNotify();
        break;
    case 'subscription_moneyfusion_notify':
        (new SubscriptionController($pdo))->moneyfusionNotify();
        break;

    // --- SUPER ADMIN ---
    case 'superadmin_dashboard':
        ob_start();
        (new SuperAdminController($pdo))->dashboard();
        $content = ob_get_clean();
        require 'views/layouts/admin.php'; // Ou un layout spécifique
        break;
    case 'superadmin_entreprises':
        ob_start();
        (new SuperAdminController($pdo))->entreprisesList();
        $content = ob_get_clean();
        require 'views/layouts/admin.php';
        break;
    case 'superadmin_update_expiration':
        (new SuperAdminController($pdo))->updateExpirationDate();
        break;
    case 'superadmin_update_plan':
        (new SuperAdminController($pdo))->updateEnterprisePlan();
        break;
    case 'superadmin_promos':
        ob_start();
        (new SuperAdminController($pdo))->promoCodesList();
        $content = ob_get_clean();
        require 'views/layouts/admin.php';
        break;
    case 'superadmin_promo_create':
        (new SuperAdminController($pdo))->createPromoCode();
        break;
    case 'superadmin_promo_toggle':
        (new SuperAdminController($pdo))->togglePromoCode();
        break;
    case 'admin_apply_promo_code':
        (new SubscriptionController($pdo))->applyPromoCode();
        break;
    case 'superadmin_impersonate':
        $id = $_GET['id'] ?? null;
        if ($id) (new SuperAdminController($pdo))->impersonate($id);
        break;
    case 'superadmin_stop_impersonate':
        (new SuperAdminController($pdo))->stopImpersonating();
        break;
    case 'superadmin_reset_password':
        (new SuperAdminController($pdo))->resetAdminPassword();
        break;
    case 'superadmin_email_settings':
        ob_start();
        (new SuperAdminController($pdo))->emailSettings();
        $content = ob_get_clean();
        require 'views/layouts/admin.php';
        break;
    case 'superadmin_save_email_settings':
        (new SuperAdminController($pdo))->saveEmailSettings();
        break;
    case 'superadmin_test_email':
        (new SuperAdminController($pdo))->testEmail();
        break;
    case 'superadmin_cinetpay_settings':
        ob_start();
        (new SuperAdminController($pdo))->cinetpaySettings();
        $content = ob_get_clean();
        require 'views/layouts/admin.php';
        break;
    case 'superadmin_save_cinetpay_settings':
        (new SuperAdminController($pdo))->saveCinetpaySettings();
        break;
    case 'superadmin_moneyfusion_settings':
        ob_start();
        (new SuperAdminController($pdo))->moneyfusionSettings();
        $content = ob_get_clean();
        require 'views/layouts/admin.php';
        break;
    case 'superadmin_save_moneyfusion_settings':
        (new SuperAdminController($pdo))->saveMoneyfusionSettings();
        break;
    case 'superadmin_gateway_settings':
        ob_start();
        (new SuperAdminController($pdo))->gatewaySettings();
        $content = ob_get_clean();
        require 'views/layouts/admin.php';
        break;
    case 'superadmin_save_gateway_settings':
        (new SuperAdminController($pdo))->saveGatewaySettings();
        break;
    case 'superadmin_delete_enterprise':
        (new SuperAdminController($pdo))->deleteEnterprise();
        break;
    case 'superadmin_audit_logs':
        ob_start();
        (new SuperAdminController($pdo))->auditLogsList();
        $content = ob_get_clean();
        require 'views/layouts/admin.php';
        break;
    case 'superadmin_plans':
        ob_start();
        (new SuperAdminController($pdo))->plansList();
        $content = ob_get_clean();
        require 'views/layouts/admin.php';
        break;
    case 'superadmin_plan_form':
        ob_start();
        (new SuperAdminController($pdo))->planForm();
        $content = ob_get_clean();
        require 'views/layouts/admin.php';
        break;
    case 'superadmin_plan_save':
        (new SuperAdminController($pdo))->savePlan();
        break;
    case 'superadmin_plan_delete':
        (new SuperAdminController($pdo))->deletePlan();
        break;
    case 'superadmin_security_log':
        ob_start();
        (new SuperAdminController($pdo))->securityLog();
        $content = ob_get_clean();
        require 'views/layouts/admin.php';
        break;
    case 'superadmin_repair_signatures':
        (new SuperAdminController($pdo))->repairSignatures();
        break;
    case 'superadmin_whatsapp':
        ob_start();
        (new SuperAdminController($pdo))->whatsappSettings();
        $content = ob_get_clean();
        require 'views/layouts/admin.php';
        break;
    case 'superadmin_save_whatsapp':
        (new SuperAdminController($pdo))->saveWhatsappSettings();
        break;

    // --- FACTURATION ---
    case 'admin_billing':
        ob_start();
        (new AdminController($pdo))->billing();
        $content = ob_get_clean();
        require 'views/layouts/admin.php';
        break;
    case 'admin_update_enterprise':
        if(!isset($_SESSION['admin_id'])) { header('Location: index.php'); exit; }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.php?page=admin_billing'); exit; }
        (new AdminController($pdo))->updateEnterpriseInfo();
        break;
    case 'download_invoice':
        (new AdminController($pdo))->downloadInvoice();
        break;

    // --- ADMIN : GESTION ÉQUIPE (OWNER ONLY) ---
    case 'admin_admins':
        ob_start();
        (new AdminController($pdo))->listAdmins();
        $content = ob_get_clean();
        require 'views/layouts/admin.php';
        break;
    case 'admin_create_admin':
        if(!isset($_SESSION['admin_id'])) { header('Location: index.php'); exit; }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.php?page=admin_admins'); exit; }
        (new AdminController($pdo))->createAdmin();
        break;
    case 'admin_delete_admin':
        if(!isset($_SESSION['admin_id'])) { header('Location: index.php'); exit; }
        (new AdminController($pdo))->deleteAdmin();
        break;

    // --- EMPLOYÉ ---
    case 'employee_scan':
        if(!isset($_SESSION['user_id'])) { header('Location: index.php?page=login'); exit; }
        (new PointageController($pdo))->scanView();
        break;
    case 'employee_scan_direct':
        if(!isset($_SESSION['user_id'])) { header('Location: index.php?page=login'); exit; }
        (new PointageController($pdo))->directScanView();
        break;
    case 'api_scan_direct':
        // API JSON : pointage sans QR Code
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        if(!isset($_SESSION['user_id'])) {
            http_response_code(403);
            echo json_encode(['success'=>false,'message'=>'Non authentifié']);
            exit;
        }
        require_once 'controllers/ApiController.php';
        (new ApiController($pdo))->handleDirectScan();
        exit;
    case 'employee_messages':
        if(!isset($_SESSION['user_id'])) { header('Location: index.php?page=login'); exit; }
        (new PointageController($pdo))->messagesView();
        break;
    case 'api_msg_poll':
        // API JSON : polling nouveaux messages
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        if(!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) { http_response_code(403); echo json_encode(['error'=>'auth']); exit; }
        require_once 'models/Message.php';
        $msgModel = new Message($pdo);
        $uid    = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : (int)($_GET['user_id'] ?? 0);
        $lastId = (int)($_GET['last_id'] ?? 0);
        if (isset($_SESSION['admin_id']) && $uid) {
            $msgModel->markAsReadForAdmin($uid);
        } elseif (isset($_SESSION['user_id'])) {
            $msgModel->markAsReadForEmployee($uid);
        }
        $msgs = $msgModel->getNewMessages($uid, $lastId);
        echo json_encode(['messages' => $msgs, 'unread_admin' => $msgModel->countUnreadAdmin()]);
        exit;
    case 'api_msg_send':
        // API JSON : envoi d'un message
        ob_clean(); // Vider le buffer HTML de index.php
        header('Content-Type: application/json; charset=utf-8');
        if(!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) { 
            http_response_code(403); 
            echo json_encode(['ok'=>false, 'error'=>'auth', 'message'=>'Non authentifié']); 
            exit; 
        }
        // Vérification CSRF manuellement pour renvoyer du JSON en cas d'échec
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
            http_response_code(403);
            echo json_encode(['ok'=>false, 'error'=>'csrf', 'message'=>'Session expirée, veuillez rafraîchir la page.']);
            exit;
        }
        require_once 'models/Message.php';
        $content = trim($_POST['content'] ?? '');
        if (empty($content)) { echo json_encode(['ok'=>false, 'message'=>'Message vide']); exit; }
        $msgModel = new Message($pdo);
        if (isset($_SESSION['admin_id'])) {
            $uid = (int)($_POST['user_id'] ?? 0);
            $msgModel->sendMessage($uid, 'to_employee', $content);
        } else {
            $uid = (int)$_SESSION['user_id'];
            $msgModel->sendMessage($uid, 'to_admin', $content);
        }
        $msgs = $msgModel->getNewMessages($uid, (int)($_POST['last_id'] ?? 0));
        echo json_encode(['ok'=>true, 'messages'=>$msgs]);
        exit;

    // --- ADMIN : ACTIONS (SANS AFFICHAGE) ---
    case 'admin_create_user':
        if(!isset($_SESSION['admin_id'])) { header('Location: index.php'); exit; }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.php?page=admin_users'); exit; }
        (new AdminController($pdo))->createUser();
        break;
    case 'admin_update_user':
        if(!isset($_SESSION['admin_id'])) { header('Location: index.php'); exit; }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.php?page=admin_users'); exit; }
        (new AdminController($pdo))->updateUser();
        break;
    case 'admin_toggle_user':
        if(!isset($_SESSION['admin_id'])) { header('Location: index.php'); exit; }
        (new AdminController($pdo))->toggleUser();
        break;
    case 'admin_reset_device':
        if(!isset($_SESSION['admin_id'])) { header('Location: index.php'); exit; }
        (new AdminController($pdo))->resetDevice();
        break;
    case 'admin_save_lieu':
        if(!isset($_SESSION['admin_id'])) { header('Location: index.php'); exit; }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.php?page=admin_lieux'); exit; }
        (new AdminController($pdo))->createLieu();
        break;
    case 'admin_save_planning':
        if(!isset($_SESSION['admin_id'])) { header('Location: index.php'); exit; }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.php?page=admin_lieux'); exit; }
        (new AdminController($pdo))->savePlanning();
        break;
    case 'admin_update_lieu':
        if(!isset($_SESSION['admin_id'])) { header('Location: index.php'); exit; }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.php?page=admin_lieux'); exit; }
        (new AdminController($pdo))->updateLieu();
        break;
    case 'admin_delete_lieu':
        if(!isset($_SESSION['admin_id'])) { header('Location: index.php'); exit; }
        (new AdminController($pdo))->deleteLieu();
        break;

     // --- ADMIN : VUES (AVEC MENU) ---
     case 'admin_dashboard':
         ob_start();
         (new AdminController($pdo))->dashboard();
         $content = ob_get_clean();
         require 'views/layouts/admin.php';
         break;
     case 'admin_users':
         ob_start();
         (new AdminController($pdo))->listUsers();
         $content = ob_get_clean();
         require 'views/layouts/admin.php';
         break;
     case 'admin_lieux':
         ob_start();
         (new AdminController($pdo))->listLieux();
         $content = ob_get_clean();
         require 'views/layouts/admin.php';
         break;
     case 'admin_manual':
         if(!isset($_SESSION['admin_id'])) { header('Location: index.php?page=login'); exit; }
         ob_start();
         require 'views/admin/manual.php';
         $content = ob_get_clean();
         require 'views/layouts/admin.php';
         break;
     case 'admin_xtimes':
         ob_start();
         (new AdminController($pdo))->xtimesPointage();
         $content = ob_get_clean();
         require 'views/layouts/admin.php';
         break;
     case 'admin_reports':
         ob_start();
         (new AdminController($pdo))->reportView();
         $content = ob_get_clean();
         require 'views/layouts/admin.php';
         break;
     case 'admin_justify_absence':
         if(!isset($_SESSION['admin_id'])) { header('Location: index.php'); exit; }
         (new AdminController($pdo))->justifyAbsence();
         break;
     case 'admin_messages':
         if(!isset($_SESSION['admin_id'])) { header('Location: index.php?page=login'); exit; }
         ob_start();
         require 'views/admin/messages.php';
         $content = ob_get_clean();
         require 'views/layouts/admin.php';
         break;
     case 'admin_send_message':
         if(!isset($_SESSION['admin_id'])) { header('Location: index.php'); exit; }
         (new AdminController($pdo))->sendMessage();
         break;
     case 'admin_config_lieu':
         ob_start();
         (new AdminController($pdo))->configLieu();
         $content = ob_get_clean();
         require 'views/layouts/admin.php';
         break;
     case 'admin_pub':
         if(!isset($_SESSION['admin_id']) || $_SESSION['user_role'] !== 'super-admin') { header('Location: index.php'); exit; }
         ob_start();
         (new AdminController($pdo))->pub();
         $content = ob_get_clean();
         require 'views/layouts/admin.php';
         break;
     case 'admin_save_pub':
         if(!isset($_SESSION['admin_id']) || $_SESSION['user_role'] !== 'super-admin') { header('Location: index.php'); exit; }
         if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.php?page=admin_pub'); exit; }
         (new AdminController($pdo))->savePub();
         break;
     case 'admin_payroll':
         ob_start();
         (new AdminController($pdo))->payrollReport();
         $content = ob_get_clean();
         require 'views/layouts/admin.php';
         break;
    case 'admin_payroll_history':
        ob_start();
        (new AdminController($pdo))->listGeneratedBulletins();
        $content = ob_get_clean();
        require 'views/layouts/admin.php';
        break;
    case 'admin_payroll_ledger':
        ob_start();
        (new AdminController($pdo))->payrollLedger();
        $content = ob_get_clean();
        require 'views/layouts/admin.php';
        break;
     case 'admin_payroll_detail':
         ob_start();
         (new AdminController($pdo))->payrollDetail();
         $content = ob_get_clean();
         require 'views/layouts/admin.php';
         break;
    case 'admin_payroll_bulletin':
        if(!isset($_SESSION['admin_id'])) { header('Location: index.php'); exit; }
        (new AdminController($pdo))->payrollBulletin();
        break;
    case 'admin_export_payroll':
        if(!isset($_SESSION['admin_id'])) { header('Location: index.php'); exit; }
        (new AdminController($pdo))->exportMonthlyPayroll();
        break;
        
    default:
         header('Location: index.php?page=landing');
         exit;
}
?>
