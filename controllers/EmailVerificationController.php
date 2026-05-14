<?php
// controllers/EmailVerificationController.php

class EmailVerificationController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // ROUTE : ?page=verify_email&token=XXX
    // Valide le token et active le compte
    // ──────────────────────────────────────────────────────────────────────────
    public function verify() {
        $token = trim($_GET['token'] ?? '');

        if (empty($token) || strlen($token) !== 64) {
            $this->showError('Lien de vérification invalide.', 'Le lien que vous avez suivi est malformé ou incomplet.');
            return;
        }

        // ── Chercher le compte associé à ce token ───────────────────────────
        $stmt = $this->pdo->prepare(
            "SELECT id, email, nom, entreprise_id, email_verified_at, verification_token, created_at 
             FROM admins WHERE verification_token = ? LIMIT 1"
        );
        $stmt->execute([$token]);
        $admin = $stmt->fetch();

        // ── Token introuvable ────────────────────────────────────────────────
        if (!$admin) {
            $this->showError(
                'Lien invalide ou déjà utilisé.',
                'Ce lien de vérification n\'existe pas ou a déjà été consommé. Si vous avez déjà validé votre compte, connectez-vous directement.'
            );
            return;
        }

        // ── Compte déjà vérifié ──────────────────────────────────────────────
        if (!empty($admin['email_verified_at'])) {
            // Rediriger directement vers la connexion avec message
            header('Location: index.php?page=login&info=already_verified');
            exit;
        }

        // ── Vérification de l'expiration (24h) ──────────────────────────────
        // On se base sur created_at du compte (inscription < 24h)
        // Pour plus de précision, on pourrait stocker token_created_at — ici
        // on utilise la date de création de l'admin comme base.
        $createdAt  = strtotime($admin['created_at'] ?? '0');
        $expiresAt  = $createdAt + (24 * 3600);

        if (time() > $expiresAt) {
            $this->showError(
                'Lien expiré (+ de 24h).',
                'Votre lien de vérification a expiré. Demandez un nouveau lien ci-dessous.',
                $admin['email']
            );
            return;
        }

        // ── Token valide : activer le compte ─────────────────────────────────
        $stmtActivate = $this->pdo->prepare(
            "UPDATE admins SET 
                email_verified_at = NOW(), 
                verification_token = NULL 
             WHERE id = ? AND verification_token = ?"
        );
        $stmtActivate->execute([$admin['id'], $token]);

        if ($stmtActivate->rowCount() === 0) {
            // Race condition (double-clic) — déjà consommé
            header('Location: index.php?page=login&info=already_verified');
            exit;
        }

        // ── Auto-connexion après vérification réussie ────────────────────────
        session_regenerate_id(true);
        $_SESSION['admin_id']       = $admin['id'];
        $_SESSION['admin_email']    = $admin['email'];
        $_SESSION['user_role']      = 'owner';
        $_SESSION['entreprise_id']  = $admin['entreprise_id'];
        $_SESSION['auth_level']     = 2;

        header('Location: index.php?page=admin_dashboard&welcome=1');
        exit;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // ROUTE : ?page=resend_verification (POST)
    // Renvoie un email de vérification
    // ──────────────────────────────────────────────────────────────────────────
    public function resend() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=login');
            exit;
        }

        Security::verifyCsrfToken($_POST['csrf_token'] ?? '');

        $email = trim($_POST['email'] ?? '');
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header('Location: index.php?page=verify_email_pending&email=' . urlencode($email) . '&error=invalid_email');
            exit;
        }

        // ── Anti-flood : max 3 renvois par heure par IP ──────────────────────
        // (on peut le faire via email_queue: count des mails PENDING pour cet email)
        $stmtCount = $this->pdo->prepare(
            "SELECT COUNT(*) FROM email_queue 
             WHERE to_email = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)"
        );
        $stmtCount->execute([$email]);
        if ((int)$stmtCount->fetchColumn() >= 3) {
            header('Location: index.php?page=verify_email_pending&email=' . urlencode($email) . '&error=too_many_requests');
            exit;
        }

        // ── Récupérer le compte ──────────────────────────────────────────────
        $stmt = $this->pdo->prepare(
            "SELECT id, nom, email_verified_at FROM admins WHERE email = ? LIMIT 1"
        );
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        // Réponse générique (ne pas révéler si l'email existe ou pas)
        if (!$admin || !empty($admin['email_verified_at'])) {
            header('Location: index.php?page=verify_email_pending&email=' . urlencode($email) . '&resent=1');
            exit;
        }

        // ── Générer un nouveau token ─────────────────────────────────────────
        $newToken = bin2hex(random_bytes(32));

        // Réinitialiser la date de création pour donner 24h supplémentaires
        $this->pdo->prepare(
            "UPDATE admins SET verification_token = ?, created_at = NOW() WHERE id = ?"
        )->execute([$newToken, $admin['id']]);

        // ── Construire l'URL et envoyer ──────────────────────────────────────
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        $verificationUrl = "{$protocol}://{$host}{$basePath}/index.php?page=verify_email&token={$newToken}";

        require_once 'utils/NotificationService.php';
        NotificationService::sendEmailVerification($email, [
            'nom'              => $admin['nom'] ?? 'Responsable',
            'verification_url' => $verificationUrl,
        ], $this->pdo, true);

        header('Location: index.php?page=verify_email_pending&email=' . urlencode($email) . '&resent=1');
        exit;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Helper : afficher la page d'erreur
    // ──────────────────────────────────────────────────────────────────────────
    private function showError(string $title, string $message, string $email = '') {
        $errorTitle   = $title;
        $errorMessage = $message;
        $errorEmail   = $email;
        require 'views/verify_email_error.php';
        exit;
    }
}
