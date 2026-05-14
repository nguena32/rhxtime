<?php
// controllers/ContactController.php

class ContactController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function handleContact() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php');
            exit;
        }

        // Simplistic CSRF check if possible, but landing page might not have it yet.
        // Let's check logic for CSRF in other controllers.
        
        $prenom = trim($_POST['prenom'] ?? '');
        $email  = trim($_POST['email'] ?? '');
        $tel    = trim($_POST['tel'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if (empty($prenom) || empty($email) || empty($message)) {
            header('Location: index.php?contact_error=missing_fields');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header('Location: index.php?contact_error=invalid_email');
            exit;
        }

        require_once 'utils/NotificationService.php';
        $data = [
            'prenom'  => $prenom,
            'email'   => $email,
            'tel'     => $tel,
            'message' => $message
        ];

        $success = NotificationService::sendContactMessage($data, $this->pdo);

        if ($success) {
            header('Location: index.php?contact_success=1');
        } else {
            header('Location: index.php?contact_error=email_error');
        }
        exit;
    }
}
