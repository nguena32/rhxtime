<?php
// views/verify_email_pending.php
$email    = htmlspecialchars($_GET['email'] ?? ($_SESSION['pending_verification_email'] ?? ''));
$fromLogin = isset($_GET['from']) && $_GET['from'] === 'login';
$resent   = isset($_GET['resent']) && $_GET['resent'] === '1';
$tooMany  = isset($_GET['error']) && $_GET['error'] === 'too_many_requests';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RHXtimes — Vérifiez votre email</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #eef2ff 0%, #f5f3ff 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .card {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(79, 70, 229, 0.12), 0 4px 20px rgba(0,0,0,0.06);
            padding: 52px 48px;
            max-width: 520px;
            width: 100%;
            text-align: center;
            animation: fadeUp 0.5s ease;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .icon-wrap {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #eef2ff, #e0e7ff);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 28px;
            font-size: 36px;
            animation: pulse 2.5s infinite;
        }
        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(79,70,229,0.25); }
            50%       { box-shadow: 0 0 0 14px rgba(79,70,229,0); }
        }
        h1 { font-size: 26px; font-weight: 800; color: #1e293b; margin-bottom: 14px; letter-spacing: -0.5px; }
        .subtitle { font-size: 15px; color: #64748b; line-height: 1.7; margin-bottom: 32px; }
        .email-badge {
            display: inline-block;
            background: #eef2ff;
            color: #4f46e5;
            font-weight: 700;
            font-size: 14px;
            padding: 8px 18px;
            border-radius: 30px;
            border: 1.5px solid #c7d2fe;
            margin-bottom: 32px;
        }
        .alert-success {
            background: #f0fdf4;
            border: 1.5px solid #bbf7d0;
            color: #15803d;
            border-radius: 12px;
            padding: 14px 18px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-warning {
            background: #fffbeb;
            border: 1.5px solid #fde68a;
            color: #92400e;
            border-radius: 12px;
            padding: 14px 18px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .divider { height: 1px; background: #f1f5f9; margin: 28px 0; }
        .resend-label { font-size: 13px; color: #94a3b8; margin-bottom: 14px; }
        .resend-form input[type="email"] {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            outline: none;
            transition: border-color 0.2s;
            margin-bottom: 12px;
        }
        .resend-form input[type="email"]:focus { border-color: #4f46e5; }
        .btn-resend {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: opacity 0.2s, transform 0.2s;
            font-family: 'Inter', sans-serif;
        }
        .btn-resend:hover { opacity: 0.9; transform: translateY(-1px); }
        .btn-resend:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
        .link-login { display: block; margin-top: 20px; font-size: 13px; color: #94a3b8; }
        .link-login a { color: #4f46e5; font-weight: 600; text-decoration: none; }
        .link-login a:hover { text-decoration: underline; }
        .steps { display: flex; gap: 10px; justify-content: center; margin-bottom: 32px; }
        .step-dot {
            width: 10px; height: 10px; border-radius: 50%;
            background: #e2e8f0;
        }
        .step-dot.active { background: #4f46e5; }
        .step-dot.done { background: #10b981; }
    </style>
</head>
<body>
    <div class="card">

        <div class="icon-wrap">✉️</div>

        <!-- Étapes visuelles -->
        <div class="steps" title="Progression : 1. Inscrit · 2. Email envoyé · 3. Compte actif">
            <div class="step-dot done"></div>
            <div class="step-dot active"></div>
            <div class="step-dot"></div>
        </div>

        <h1>Vérifiez votre boîte mail</h1>

        <p class="subtitle">
            Un email de confirmation vient d'être envoyé à :
        </p>

        <?php if ($email): ?>
            <span class="email-badge">📧 <?= $email ?></span>
        <?php endif; ?>

        <p class="subtitle" style="margin-top: 0;">
            Cliquez sur le bouton dans cet email pour activer votre compte.<br>
            <strong>Le lien est valide 24 heures.</strong>
        </p>

        <?php if ($resent): ?>
            <div class="alert-success">
                <i class="fa-solid fa-check-circle"></i>
                Un nouvel email a été envoyé ! Vérifiez votre boîte (et les spams).
            </div>
        <?php endif; ?>

        <?php if ($tooMany): ?>
            <div class="alert-warning">
                <i class="fa-solid fa-clock"></i>
                Trop de demandes. Attendez 1 heure avant de renvoyer un email.
            </div>
        <?php endif; ?>

        <div class="divider"></div>

        <p class="resend-label">Vous n'avez pas reçu d'email ? Vérifiez vos spams ou renvoyez-en un :</p>

        <form class="resend-form" method="POST" action="index.php?page=resend_verification" onsubmit="handleSubmit(this)">
            <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken() ?>">
            <input
                type="email"
                name="email"
                placeholder="votre@email.com"
                value="<?= $email ?>"
                required
                id="resendEmailInput"
            >
            <button type="submit" class="btn-resend" id="resendBtn">
                <i class="fa-solid fa-paper-plane" style="margin-right:8px;"></i>
                Renvoyer l'email de vérification
            </button>
        </form>

        <span class="link-login">
            Déjà un compte validé ? <a href="index.php?page=login">Se connecter</a>
        </span>
    </div>

    <script>
    function handleSubmit(form) {
        const btn = document.getElementById('resendBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin" style="margin-right:8px;"></i> Envoi en cours...';
    }
    </script>
</body>
</html>
