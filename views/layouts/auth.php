<?php
// views/layouts/auth.php
// Minimal layout for Auth pages (Login, Register)
$isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
$base_dir = rtrim(dirname($_SERVER['PHP_SELF']), '\\/') . '/';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= $page_title ?? 'RHXtimes - Espace Sécurisé' ?></title>
    
    <meta name="description" content="Connectez-vous à RHXtimes, la solution panafricaine de pointage GPS certifié sans matériel. Gérez vos équipes sur le terrain en toute confiance.">
    <meta property="og:title" content="RHXtimes - Pointage GPS Certifié">
    <meta property="og:description" content="La solution de pointage zéro hardware pour les entreprises modernes.">
    <meta property="og:image" content="<?= $base_dir ?>assets/images/og-auth.png">
    <meta property="og:type" content="website">
    
    <meta name="theme-color" content="#0F172A">
    <link rel="icon" type="image/png" href="<?= $base_dir ?>assets/images/favicon.png">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        :root {
            --color-primary: #4F46E5;
            --color-secondary: #0F172A;
            --color-accent: #f97316;
            --color-bg: #F8FAFC;
            --color-text-main: #1E293B;
            --color-text-muted: #64748B;
            --color-border: #E2E8F0;
            --font-family: 'Inter', sans-serif;
            --radius-xl: 30px;
            --radius-lg: 16px;
            --radius-md: 8px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; outline: none; }
        body {
            font-family: var(--font-family);
            background-color: var(--color-bg);
            color: var(--color-text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            overflow-x: hidden;
        }

        /* Split Screen Container */
        .auth-container {
            display: flex;
            width: 100%;
            max-width: 1100px;
            min-height: 650px;
            background: #ffffff;
            border-radius: var(--radius-xl);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            position: relative;
        }

        /* Left Side (Marketing) */
        .auth-left {
            flex: 1;
            background: linear-gradient(145deg, var(--color-secondary) 0%, #1e293b 100%);
            color: #ffffff;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .auth-left::before {
            content: '';
            position: absolute;
            top: -20%;
            left: -20%;
            width: 140%;
            height: 140%;
            background: radial-gradient(circle, rgba(79, 70, 229, 0.15) 0%, transparent 60%);
            z-index: 0;
        }

        .auth-brand {
            position: relative;
            z-index: 1;
            margin-bottom: 40px;
        }

        .auth-brand img {
            max-width: 180px;
        }

        .auth-marketing {
            position: relative;
            z-index: 1;
        }

        .auth-marketing h1 {
            font-size: 2.2rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 20px;
        }

        .auth-marketing p {
            font-size: 1.1rem;
            color: #cbd5e1;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .trust-badges {
            display: flex;
            gap: 20px;
            margin-top: auto;
            position: relative;
            z-index: 1;
        }

        .trust-badge {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(255, 255, 255, 0.05);
            padding: 10px 15px;
            border-radius: var(--radius-lg);
            font-size: 0.9rem;
            font-weight: 500;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .trust-badge i { color: var(--color-accent); }

        /* Right Side (Form) */
        .auth-right {
            flex: 1;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #ffffff;
            position: relative;
        }

        /* Form Elements */
        .auth-header { margin-bottom: 30px; text-align: center; }
        .auth-header h2 { font-size: 1.8rem; font-weight: 800; color: var(--color-secondary); margin-bottom: 8px; }
        .auth-header p { color: var(--color-text-muted); font-size: 1rem; }

        .form-group { margin-bottom: 20px; text-align: left; }
        .form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--color-secondary);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-control {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid var(--color-border);
            border-radius: var(--radius-md);
            font-size: 1rem;
            font-family: var(--font-family);
            color: var(--color-text-main);
            transition: all 0.3s ease;
            background: #fff;
        }

        .form-control:focus {
            border-color: var(--color-primary);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }
        
        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            padding-right: 40px;
        }

        .password-wrapper { position: relative; }
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--color-text-muted);
            cursor: pointer;
            transition: color 0.3s;
        }
        .password-toggle:hover { color: var(--color-primary); }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            font-size: 0.9rem;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--color-text-muted);
            cursor: pointer;
        }

        .auth-link {
            color: var(--color-text-muted);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .auth-link:hover { color: var(--color-primary); text-decoration: underline; }

        .btn-auth {
            width: 100%;
            padding: 15px;
            background: var(--color-accent); /* ORANGE #f97316 */
            color: white;
            border: none;
            border-radius: var(--radius-md);
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(249, 115, 22, 0.3);
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }

        .btn-auth:hover {
            background: #ea580c;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(249, 115, 22, 0.4);
        }

        .auth-footer {
            margin-top: 30px;
            text-align: center;
            font-size: 0.95rem;
            color: var(--color-text-muted);
        }

        .auth-footer a {
            color: var(--color-secondary);
            font-weight: 700;
            text-decoration: none;
        }

        /* Mobile Adjustments */
        @media (max-width: 900px) {
            .auth-container {
                flex-direction: column;
                min-height: auto;
                border-radius: var(--radius-lg);
            }
            .auth-left {
                padding: 40px 30px;
                text-align: center;
            }
            .auth-brand img {
                margin: 0 auto;
            }
            .auth-marketing h1 {
                font-size: 1.8rem;
            }
            .trust-badges {
                display: none; /* Hide on mobile to save space */
            }
            .auth-right {
                padding: 40px 30px;
            }
        }
        
        @media (max-width: 480px) {
            body { padding: 10px; }
            .auth-container { border-radius: var(--radius-md); }
            .auth-left { padding: 30px 20px; }
            .auth-right { padding: 30px 20px; }
            .auth-header h2 { font-size: 1.5rem; }
        }
    </style>
</head>
<body>

    <div id="auth-loading-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(255,255,255,0.7); z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(4px);">
        <div style="text-align:center;">
            <div class="fa-solid fa-circle-notch fa-spin" style="font-size:3rem; color:var(--color-primary); margin-bottom:15px;"></div>
            <p style="font-weight:700; color:var(--color-secondary);">Traitement en cours...</p>
        </div>
    </div>

    <?= $content ?? '' ?>

    <script>
        // Loading state on form submit
        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (form.method.toLowerCase() === 'post' && !form.getAttribute('target')) {
                const btn = form.querySelector('.btn-auth');
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Patientez...';
                    document.getElementById('auth-loading-overlay').style.display = 'flex';
                }
            }
        });
        // Global SweetAlert config
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true,
            background: '#ffffff',
            color: '#1e293b'
        });

        <?php if (isset($_GET['error']) || isset($error)): 
            $errMsg = $_GET['error'] ?? $error;
        ?>
            document.addEventListener('DOMContentLoaded', () => {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: '<?= addslashes($errMsg) ?>',
                    confirmButtonColor: '#4F46E5',
                    borderRadius: '16px'
                });
                
                const url = new URL(window.location);
                url.searchParams.delete('error');
                window.history.replaceState({}, '', url);
            });
        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>
            document.addEventListener('DOMContentLoaded', () => {
                Toast.fire({
                    icon: 'success',
                    title: 'Opération réussie !'
                });
                const url = new URL(window.location);
                url.searchParams.delete('success');
                window.history.replaceState({}, '', url);
            });
        <?php endif; ?>
    </script>
</body>
</html>
