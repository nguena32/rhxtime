<?php
// views/verify_email_error.php
// Variables attendues : $errorTitle, $errorMessage, $errorEmail (optionnel)
$errorTitle   = $errorTitle   ?? 'Erreur de vérification';
$errorMessage = $errorMessage ?? 'Une erreur est survenue.';
$errorEmail   = $errorEmail   ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RHXtimes — Lien invalide</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #fff1f2 0%, #fdf2f8 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .card {
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(239,68,68,0.10), 0 4px 20px rgba(0,0,0,0.06);
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
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 28px;
            font-size: 36px;
        }
        h1 { font-size: 24px; font-weight: 800; color: #7f1d1d; margin-bottom: 14px; letter-spacing: -0.5px; }
        .subtitle { font-size: 15px; color: #64748b; line-height: 1.7; margin-bottom: 32px; }
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
        .btn-primary {
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
            text-decoration: none;
            display: block;
        }
        .btn-primary:hover { opacity: 0.9; transform: translateY(-1px); }
        .btn-outline {
            display: block;
            margin-top: 12px;
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            color: #475569;
            text-decoration: none;
            transition: all 0.2s;
            text-align: center;
        }
        .btn-outline:hover { border-color: #4f46e5; color: #4f46e5; background: #eef2ff; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-wrap">⚠️</div>

        <h1><?= htmlspecialchars($errorTitle) ?></h1>

        <p class="subtitle"><?= htmlspecialchars($errorMessage) ?></p>

        <?php if (!empty($errorEmail)): ?>
            <div class="divider"></div>
            <p class="resend-label">Demandez un nouveau lien de vérification :</p>
            <form class="resend-form" method="POST" action="index.php?page=resend_verification" onsubmit="this.querySelector('button').disabled=true">
                <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken() ?>">
                <input
                    type="email"
                    name="email"
                    value="<?= htmlspecialchars($errorEmail) ?>"
                    placeholder="votre@email.com"
                    required
                >
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-rotate-right" style="margin-right:8px;"></i>
                    Renvoyer un nouveau lien
                </button>
            </form>
        <?php else: ?>
            <a href="index.php?page=register" class="btn-primary">
                <i class="fa-solid fa-user-plus" style="margin-right:8px;"></i>
                Créer un nouveau compte
            </a>
        <?php endif; ?>

        <a href="index.php?page=login" class="btn-outline">
            <i class="fa-solid fa-arrow-left" style="margin-right:6px;"></i>
            Retour à la connexion
        </a>
    </div>
</body>
</html>
