<?php
// controllers/RegistrationController.php

class RegistrationController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function handleSignup() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=register');
            exit;
        }

        $token = $_POST['csrf_token'] ?? '';
        Security::verifyCsrfToken($token);

        $nomEntreprise = trim($_POST['nom_entreprise'] ?? '');
        $secteur = trim($_POST['secteur'] ?? '');
        $nomResponsable = trim($_POST['nom_responsable'] ?? '');
        $telephone = trim($_POST['telephone'] ?? '');
        $pays = trim($_POST['pays'] ?? '');
        $ville = trim($_POST['ville'] ?? '');
        $localisation = trim($_POST['localisation'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $captcha = $_POST['g-recaptcha-response'] ?? 'VALID'; // Simulation Turnstile/reCAPTCHA

        // ... existing domain check ...
        $blacklist = ['yopmail.com', 'mailinator.com', 'tempmail.com', 'guerrillamail.com'];
        $domain = substr(strrchr($email, "@"), 1);
        if (in_array($domain, $blacklist)) {
            Security::logAnomaly("Tentative d'inscription avec domaine banni : $email");
            header('Location: index.php?page=register&error=' . urlencode("Ce service d'email n'est pas autorisé."));
            exit;
        }

        // ... existing IP check ...
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $stmtIp = $this->pdo->prepare("SELECT COUNT(*) FROM entreprises WHERE ip_registration = ? AND Created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)");
        $stmtIp->execute([$ip]);
        $count = $stmtIp->fetchColumn();

        if ($count >= 5) {
             Security::logAnomaly("Tentative d'inscription multiple (Limite de 5 atteinte) : $ip");
             header('Location: index.php?page=register&error=' . urlencode("Limite d'inscription atteinte pour aujourd'hui, patientez 24h avant de créer à nouveau, Merci"));
             exit;
        }

        $acceptTerms = $_POST['accept_terms'] ?? null;
        
        if (empty($nomEntreprise) || empty($secteur) || empty($nomResponsable) || empty($telephone) || empty($pays) || empty($ville) || empty($localisation) || empty($email) || empty($password)) {
            header('Location: index.php?page=register&error=' . urlencode('Tous les champs sont requis.'));
            exit;
        }

        // ── Validation de la force du mot de passe ──
        if (strlen($password) < 8) {
            header('Location: index.php?page=register&error=' . urlencode('Le mot de passe doit contenir au moins 8 caractères.'));
            exit;
        }

        if ($password !== $confirmPassword) {
            header('Location: index.php?page=register&error=' . urlencode('Les mots de passe ne correspondent pas.'));
            exit;
        }

        if (!$acceptTerms) {
            header('Location: index.php?page=register&error=' . urlencode('Vous devez accepter les conditions d\'utilisation.'));
            exit;
        }

        // ── Normalisation du téléphone (suppression des espaces/tirets) ──
        $telephone = preg_replace('/[^0-9+]/', '', $telephone);

        try {
            $this->pdo->beginTransaction();

            // Vérifier email existant
            $stmt = $this->pdo->prepare("SELECT id FROM admins WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $this->pdo->rollBack();
                header('Location: index.php?page=register&error=' . urlencode('Cet email est déjà utilisé.'));
                exit;
            }

            // Déterminer le plan (Starter ID 1 par défaut)
            $plan_id = intval($_GET['plan'] ?? 1);
            if ($plan_id <= 0) $plan_id = 1;

            // ── Vérifier si le plan existe réellement ──
            $stmtCheckPlan = $this->pdo->prepare("SELECT id, trial_days FROM plans WHERE id = ?");
            $stmtCheckPlan->execute([$plan_id]);
            $planData = $stmtCheckPlan->fetch();

            if (!$planData) {
                // Fallback sur le plan par défaut (1) si l'ID n'existe pas
                $plan_id = 1;
                $stmtPlanDefault = $this->pdo->prepare("SELECT trial_days FROM plans WHERE id = 1");
                $stmtPlanDefault->execute();
                $trialDays = $stmtPlanDefault->fetchColumn() ?: 14;
            } else {
                $trialDays = $planData['trial_days'] ?: 14;
            }
            
            // Créer entreprise (Status = Trial)
            $stmtEnt = $this->pdo->prepare("INSERT INTO entreprises (nom, secteur, email, plan_id, expiration_date, statut, has_had_trial, ip_registration, nom_responsable, telephone, pays, ville, localisation, Created_at) VALUES (?, ?, ?, ?, DATE_ADD(CURRENT_DATE(), INTERVAL ? DAY), 'Trial', 1, ?, ?, ?, ?, ?, ?, NOW())");
            $stmtEnt->execute([$nomEntreprise, $secteur, $email, $plan_id, $trialDays, $ip, $nomResponsable, $telephone, $pays, $ville, $localisation]);
            $entreprise_id = $this->pdo->lastInsertId();

            // Générer et enregistrer la signature de sécurité
            $stmtExp = $this->pdo->prepare("SELECT expiration_date FROM entreprises WHERE id = ?");
            $stmtExp->execute([$entreprise_id]);
            $fetchExp = $stmtExp->fetchColumn();
            
            $signature = Security::generateExpirationSignature($entreprise_id, $fetchExp);
            $stmtUpdSig = $this->pdo->prepare("UPDATE entreprises SET security_signature = ? WHERE id = ?");
            $stmtUpdSig->execute([$signature, $entreprise_id]);

            // Créer admin owner
            $hashedPassword = password_hash($password, PASSWORD_ARGON2ID);
            $stmtAdm = $this->pdo->prepare("INSERT INTO admins (entreprise_id, email, password, role, nom) VALUES (?, ?, ?, 'owner', ?)");
            $stmtAdm->execute([$entreprise_id, $email, $hashedPassword, $nomResponsable]);
            $admin_id = $this->pdo->lastInsertId();

            // ── Générer le token de vérification (256 bits, usage unique) ──
            $verificationToken = bin2hex(random_bytes(32)); // 64 chars hex
            $stmtToken = $this->pdo->prepare(
                "UPDATE admins SET verification_token = ?, email_verified_at = NULL WHERE id = ?"
            );
            $stmtToken->execute([$verificationToken, $admin_id]);

            $this->pdo->commit();

            // ── Construire l'URL de vérification ────────────────────────────
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
            $verificationUrl = "{$protocol}://{$host}{$basePath}/index.php?page=verify_email&token={$verificationToken}";

            // ── Envoyer l'email de vérification (Immédiat pour l'expérience utilisateur) ──
            require_once 'utils/NotificationService.php';
            NotificationService::sendEmailVerification($email, [
                'nom'              => $nomResponsable,
                'verification_url' => $verificationUrl,
            ], $this->pdo, true);

            // ── Redirection vers la page d'attente (PAS d'auto-logon) ───────
            header('Location: index.php?page=verify_email_pending&email=' . urlencode($email));
            exit;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log($e->getMessage());
            header('Location: index.php?page=register&error=' . urlencode('Erreur technique lors de l\'inscription.'));
            exit;
        }
    }
}
