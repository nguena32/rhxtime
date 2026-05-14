<?php
// controllers/SupportController.php

class SupportController {
    private $pdo;
    private $ticketModel;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->ticketModel = new Ticket($pdo);

        // Protection de base : Nécessite d'être connecté
        if (!isset($_SESSION['admin_id'])) {
            header('Location: index.php?page=login');
            exit;
        }
    }

    // ==========================================
    // SIDE ADMIN (CLIENT)
    // ==========================================

    public function adminList() {
        $admin_id = $_SESSION['admin_id'];
        $tickets = $this->ticketModel->getByAdmin($admin_id);
        
        $page = 'admin_support';
        ob_start();
        require 'views/admin/support.php';
        $content = ob_get_clean();
        require 'views/layouts/admin.php';
    }

    public function adminCreate() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Security::verifyCsrfToken($_POST['csrf_token'] ?? '');

            $subject = trim($_POST['subject'] ?? '');
            $message = trim($_POST['message'] ?? '');
            $priority = $_POST['priority'] ?? 'medium';
            $admin_id = $_SESSION['admin_id'];
            $entreprise_id = $_SESSION['entreprise_id'];

            if (!empty($subject) && !empty($message)) {
                $ticket_id = $this->ticketModel->create($admin_id, $entreprise_id, $subject, $priority);
                if ($ticket_id) {
                    // Gestion pièce jointe
                    $attachment = $this->handleUpload();
                    $this->ticketModel->addMessage($ticket_id, $admin_id, 'admin', $message, $attachment);
                    
                    // Notification au SuperAdmin
                    try {
                        $stmtSA = $this->pdo->query("SELECT email FROM admins WHERE role = 'super-admin' LIMIT 1");
                        $saEmail = $stmtSA->fetchColumn();
                        if ($saEmail) {
                            $notifMsg = "<p>Nouveau ticket de support reçu : <strong>$subject</strong></p>";
                            $notifMsg .= "<p>Priorité : $priority</p>";
                            $notifMsg .= "<p><a href='https://rhxtimes.marvensgroup.com/index.php?page=superadmin_support_view&id=$ticket_id'>Cliquer ici pour répondre</a></p>";
                            NotificationService::sendSupportNotification($saEmail, "Support : Nouveau Ticket #$ticket_id", $notifMsg, $this->pdo);
                        }
                    } catch (Exception $e) {}

                    header('Location: index.php?page=admin_support&success=ticket_created');
                    exit;
                }
            }
            header('Location: index.php?page=admin_support&error=missing_fields');
            exit;
        }
        
        // Fallback pour les accès en GET (évite la page blanche)
        header('Location: index.php?page=admin_support');
        exit;
    }

    public function adminView() {
        $id = $_GET['id'] ?? 0;
        $admin_id = $_SESSION['admin_id'];
        
        $ticket = $this->ticketModel->getById($id);
        
        // Sécurité : Vérifier que le ticket appartient bien à l'admin
        if (!$ticket || $ticket['admin_id'] != $admin_id) {
            header('Location: index.php?page=admin_support');
            exit;
        }

        // Marquer comme lu
        $this->ticketModel->markAsRead($id, 'superadmin');
        
        $messages = $this->ticketModel->getMessages($id);
        $page = 'admin_support';
        ob_start();
        require 'views/admin/support_view.php';
        $content = ob_get_clean();
        require 'views/layouts/admin.php';
    }

    public function adminReply() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Security::verifyCsrfToken($_POST['csrf_token'] ?? '');
            
            $ticket_id = $_POST['ticket_id'] ?? 0;
            $message = trim($_POST['message'] ?? '');
            $admin_id = $_SESSION['admin_id'];

            $ticket = $this->ticketModel->getById($ticket_id);
            if ($ticket && $ticket['admin_id'] == $admin_id && !empty($message)) {
                $attachment = $this->handleUpload();
                $this->ticketModel->addMessage($ticket_id, $admin_id, 'admin', $message, $attachment);
                $this->ticketModel->updateStatus($ticket_id, 'open'); // Repasse en open pour le support
                
                // Notification au SuperAdmin lors d'une relance client
                try {
                    $stmtSA = $this->pdo->query("SELECT email FROM admins WHERE role = 'super-admin' LIMIT 1");
                    $saEmail = $stmtSA->fetchColumn();
                    if ($saEmail) {
                        $notifMsg = "<p>Nouvelle réponse client sur le ticket <strong>#$ticket_id</strong></p>";
                        $notifMsg .= "<p><a href='https://rhxtimes.marvensgroup.com/index.php?page=superadmin_support_view&id=$ticket_id'>Répondre</a></p>";
                        NotificationService::sendSupportNotification($saEmail, "Support : Réponse Ticket #$ticket_id", $notifMsg, $this->pdo);
                    }
                } catch (Exception $e) {}

                header("Location: index.php?page=admin_support_view&id=$ticket_id&success=sent");
                exit;
            }
        }
        header('Location: index.php?page=admin_support');
    }

    // ==========================================
    // SIDE SUPER-ADMIN (SUPPORT)
    // ==========================================

    public function superAdminList() {
        if (($_SESSION['user_role'] ?? '') !== 'super-admin') exit('Accès refusé');

        $tickets = $this->ticketModel->getAllForSupport();
        
        $page = 'superadmin_support';
        ob_start();
        require 'views/superadmin/support.php';
        $content = ob_get_clean();
        require 'views/layouts/admin.php';
    }

    public function superAdminView() {
        if (($_SESSION['user_role'] ?? '') !== 'super-admin') exit('Accès refusé');
        
        $id = $_GET['id'] ?? 0;
        $ticket = $this->ticketModel->getById($id);
        if (!$ticket) {
            header('Location: index.php?page=superadmin_support');
            exit;
        }

        // Marquer comme lu
        $this->ticketModel->markAsRead($id, 'admin');

        $messages = $this->ticketModel->getMessages($id);
        $page = 'superadmin_support';
        ob_start();
        require 'views/superadmin/support_view.php';
        $content = ob_get_clean();
        require 'views/layouts/admin.php';
    }

    public function superAdminReply() {
        if (($_SESSION['user_role'] ?? '') !== 'super-admin') exit('Accès refusé');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Security::verifyCsrfToken($_POST['csrf_token'] ?? '');
            
            $ticket_id = $_POST['ticket_id'] ?? 0;
            $message = trim($_POST['message'] ?? '');
            $admin_id = $_SESSION['admin_id']; // Ici c'est l'ID du superadmin en session

            if (!empty($message)) {
                $attachment = $this->handleUpload();
                $this->ticketModel->addMessage($ticket_id, $admin_id, 'superadmin', $message, $attachment);
                $this->ticketModel->updateStatus($ticket_id, 'replied');
                
                // Notification au client
                try {
                    $ticket = $this->ticketModel->getById($ticket_id);
                    $stmtAdmin = $this->pdo->prepare("SELECT email FROM admins WHERE id = ?");
                    $stmtAdmin->execute([$ticket['admin_id']]);
                    $adminEmail = $stmtAdmin->fetchColumn();

                    if ($adminEmail) {
                        $notifMsg = "<p>L'équipe support RHXtimes a répondu à votre ticket <strong>#$ticket_id</strong>.</p>";
                        $notifMsg .= "<p>Sujet : {$ticket['subject']}</p>";
                        $notifMsg .= "<p><a href='https://rhxtimes.marvensgroup.com/index.php?page=admin_support_view&id=$ticket_id'>Cliquer ici pour voir la réponse</a></p>";
                        NotificationService::sendSupportNotification($adminEmail, "RHXtimes : Réponse à votre ticket de support", $notifMsg, $this->pdo);
                    }
                } catch (Exception $e) {}

                header("Location: index.php?page=superadmin_support_view&id=$ticket_id&success=sent");
                exit;
            }
        }
        header('Location: index.php?page=superadmin_support');
    }

    public function superAdminClose() {
        if (($_SESSION['user_role'] ?? '') !== 'super-admin') exit('Accès refusé');
        $id = $_GET['id'] ?? 0;
        $this->ticketModel->updateStatus($id, 'closed');
        header('Location: index.php?page=superadmin_support&success=closed');
    }

    // ==========================================
    // UTILS
    // ==========================================

    private function handleUpload() {
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/tickets/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $filename = time() . '_' . basename($_FILES['attachment']['name']);
            $target = $uploadDir . $filename;
            
            $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
            $ext = strtolower(pathinfo($target, PATHINFO_EXTENSION));

            if (in_array($ext, $allowed)) {
                if (move_uploaded_file($_FILES['attachment']['tmp_name'], $target)) {
                    return $target;
                }
            }
        }
        return null;
    }
}
