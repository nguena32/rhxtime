<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accès Refusé | RHXtimes</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&family=Outfit:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --accent: #f59e0b;
            --slate-900: #0f172a;
            --slate-800: #1e293b;
            --slate-700: #334155;
            --slate-400: #94a3b8;
            --danger: #ef4444;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: var(--slate-800);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            overflow: hidden;
            background-image: 
                radial-gradient(at 0% 0%, rgba(79, 70, 229, 0.05) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(245, 158, 11, 0.05) 0px, transparent 50%);
        }

        .container {
            max-width: 540px;
            width: 90%;
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
            position: relative;
            z-index: 10;
            border: 1px solid rgba(226, 232, 240, 0.8);
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .icon-wrapper {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            border-radius: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            color: var(--danger);
            font-size: 42px;
            position: relative;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
            70% { transform: scale(1.05); box-shadow: 0 0 0 15px rgba(239, 68, 68, 0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }

        h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 32px;
            font-weight: 700;
            color: var(--slate-900);
            margin-bottom: 16px;
            letter-spacing: -0.02em;
        }

        p {
            font-size: 16px;
            line-height: 1.6;
            color: var(--slate-700);
            margin-bottom: 35px;
        }

        .badge {
            display: inline-block;
            padding: 6px 12px;
            background: #f1f5f9;
            border-radius: 100px;
            font-size: 12px;
            font-weight: 600;
            color: var(--slate-400);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 20px;
        }

        .actions {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 14px 28px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 15px;
            text-decoration: none;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(79, 70, 229, 0.4);
        }

        .btn-ghost {
            background: transparent;
            color: var(--slate-700);
            border: 1px solid #e2e8f0;
        }

        .btn-ghost:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }

        .footer {
            margin-top: 40px;
            font-size: 13px;
            color: var(--slate-400);
        }

        .logo {
            margin-bottom: 30px;
            height: 32px;
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="assets/images/logo.png" alt="RHXtimes" class="logo" onerror="this.style.display='none'">
        
        <div class="badge">Erreur Sécurité 403</div>
        
        <div class="icon-wrapper">
            <i class="fa-solid fa-shield-halved"></i>
        </div>
        
        <h1>Accès Interrompu</h1>
        
        <p>L'intégrité de votre session ou de votre licence n'a pas pu être validée. Cela peut se produire suite à une modification administrative ou une expiration de compte.</p>
        
        <div class="actions">
            <a href="index.php?page=login" class="btn btn-primary">
                <i class="fa-solid fa-arrow-right-to-bracket" style="margin-right: 10px;"></i>
                Se reconnecter
            </a>
            <a href="index.php?page=landing" class="btn btn-ghost">Retour à l'accueil</a>
        </div>

        <div class="footer">
            &copy; 2026 RHXtimes Platform. Tous droits réservés.
        </div>
    </div>
</body>
</html>
