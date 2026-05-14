<?php
// views/layouts/admin.php
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php?page=login');
    exit;
}

global $pdo;
$isTrial = false;
$trialDaysLeft = 0;
if (!empty($_SESSION['entreprise_id'])) {
    try {
        $stmtTrial = $pdo->prepare("SELECT statut, expiration_date FROM entreprises WHERE id = ?");
        $stmtTrial->execute([$_SESSION['entreprise_id']]);
        $entInfo = $stmtTrial->fetch();
        if ($entInfo) {
            $isTrial = ($entInfo['statut'] === 'Trial');
            if ($isTrial && !empty($entInfo['expiration_date'])) {
                $now = new DateTime();
                $now->setTime(0, 0, 0);
                $exp = new DateTime($entInfo['expiration_date']);
                $exp->setTime(0, 0, 0);
                $diff = $now->diff($exp);
                $trialDaysLeft = $diff->invert ? 0 : $diff->days;
            }
        }

        // Récupérer les fonctionnalités du plan
        $stmtPlan = $pdo->prepare("SELECT p.* FROM entreprises e JOIN plans p ON e.plan_id = p.id WHERE e.id = ?");
        $stmtPlan->execute([$_SESSION['entreprise_id']]);
        $planFeatures = $stmtPlan->fetch();
        $hasMessaging = (bool) ($planFeatures['has_messaging'] ?? true);

    } catch (Exception $e) { /* Silencieux */
    }
}
?>
<?php if (isset($_SESSION['original_admin_id']) && isset($_SESSION['entreprise_nom_impersonate'])): ?>
    <div
        style="background:var(--color-danger); color:white; padding:10px; text-align:center; position:fixed; width:100%; z-index:9999; top:0; left:0;">
        Mode Support : Vous visualisez actuellement
        <strong><?= htmlspecialchars($_SESSION['entreprise_nom_impersonate']) ?></strong>.
        <a href="index.php?page=superadmin_stop_impersonate"
            style="color:#fff; text-decoration:underline; font-weight:bold; margin-left:15px;">[Quitter ce mode]</a>
    </div>
    <style>
        body {
            margin-top: 40px;
        }
    </style>
<?php endif; ?>
<div class="app-container">
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>
    <aside class="sidebar" id="appSidebar">
        <div class="brand">
            <img src="assets/images/logo_texte_blanc.png" alt="RHXtimes" style="max-height: 40px;">
            <button onclick="toggleSidebar()" class="mobile-close-btn" style="display:none; margin-left:auto;"><i
                    class="fa-solid fa-xmark"></i></button>
            <style>
                @media(max-width:991px) {
                    .mobile-close-btn {
                        display: block !important;
                    }
                }
            </style>
        </div>
        <ul class="nav-menu">
            <li class="nav-item">
                <?php
                $dashLink = 'index.php?page=admin_dashboard';
                if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'super-admin' && !isset($_SESSION['original_admin_id'])) {
                    $dashLink = 'index.php?page=superadmin_dashboard';
                }
                ?>
                <a href="<?= $dashLink ?>"
                    class="nav-link <?= ($page == 'admin_dashboard' || $page == 'superadmin_dashboard') ? 'active' : '' ?>">
                    <span class="nav-icon"><i class="fa-solid fa-chart-pie"></i></span> Tableau de bord
                </a>
            </li>

            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] !== 'super-admin'): ?>
                <!-- 1b. Manuel d'utilisation -->
                <li class="nav-item">
                    <a href="index.php?page=admin_manual" class="nav-link <?= $page == 'admin_manual' ? 'active' : '' ?>">
                        <span class="nav-icon"><i class="fa-solid fa-book-open"></i></span> Manuel d'utilisation
                    </a>
                </li>

                <!-- 2. Pointage Xtimes -->
                <li class="nav-item">
                    <a href="index.php?page=admin_xtimes" class="nav-link <?= $page == 'admin_xtimes' ? 'active' : '' ?>">
                        <span class="nav-icon"><i class="fa-solid fa-clock-rotate-left"></i></span> Pointage Xtimes
                    </a>
                </li>

                <!-- 3. Lieux & Horaires -->
                <li class="nav-item">
                    <a href="index.php?page=admin_lieux" class="nav-link <?= $page == 'admin_lieux' ? 'active' : '' ?>">
                        <span class="nav-icon"><i class="fa-solid fa-map-location-dot"></i></span> Lieux & Horaires
                    </a>
                </li>

                <!-- 4. Employés -->
                <li class="nav-item">
                    <a href="index.php?page=admin_users" class="nav-link <?= strpos($page, 'user') !== false ? 'active' : '' ?>">
                        <span class="nav-icon"><i class="fa-solid fa-users"></i></span> Employés
                    </a>
                </li>
            <?php endif; ?>

            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'owner'): ?>
                <!-- 5. Managers -->
                <li class="nav-item">
                    <a href="index.php?page=admin_admins" class="nav-link <?= $page == 'admin_admins' ? 'active' : '' ?>">
                        <span class="nav-icon"><i class="fa-solid fa-users-gear"></i></span> Managers
                    </a>
                </li>
            <?php endif; ?>

            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] !== 'super-admin'): ?>
                <!-- 6. Rapports pointages -->
                <li class="nav-item">
                    <a href="index.php?page=admin_reports" class="nav-link <?= $page == 'admin_reports' ? 'active' : '' ?>">
                        <span class="nav-icon"><i class="fa-solid fa-file-contract"></i></span> Rapports pointages
                    </a>
                </li>
            <?php endif; ?>

            <!-- 7. Gestion de la paie -->
            <?php if (isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['owner', 'manager'])): ?>
                <li class="nav-item">
                    <a href="#"
                        class="nav-link <?= (in_array($page, ['admin_payroll', 'admin_payroll_history', 'admin_payroll_ledger'])) ? 'active' : '' ?>"
                        onclick="toggleSubMenu(event, 'payrollSubMenu')">
                        <span class="nav-icon"><i class="fa-solid fa-file-invoice-dollar"></i></span> Gestion de la paie
                        <i class="fa-solid fa-chevron-down"
                            style="font-size:10px; margin-left:auto; transform: <?= (in_array($page, ['admin_payroll', 'admin_payroll_history', 'admin_payroll_ledger'])) ? 'rotate(180deg)' : 'rotate(0deg)' ?>;"></i>
                    </a>
                    <ul id="payrollSubMenu"
                        style="list-style:none; padding-left:45px; margin-top:5px; display: <?= (in_array($page, ['admin_payroll', 'admin_payroll_history', 'admin_payroll_ledger'])) ? 'block' : 'none' ?>;">
                        <li><a href="index.php?page=admin_payroll"
                                style="font-size:13px; color: <?= $page == 'admin_payroll' ? 'var(--color-primary)' : '#64748b' ?>; text-decoration:none; display:block; padding:8px 0;">
                                <i class="fa-solid fa-calculator" style="font-size:11px; margin-right:8px;"></i> Calcul de
                                paie
                            </a></li>
                        <li><a href="index.php?page=admin_payroll_history"
                                style="font-size:13px; color: <?= $page == 'admin_payroll_history' ? 'var(--color-primary)' : '#64748b' ?>; text-decoration:none; display:block; padding:8px 0;">
                                <i class="fa-solid fa-clock-rotate-left" style="font-size:11px; margin-right:8px;"></i>
                                Bulletins générés
                            </a></li>
                        <li><a href="index.php?page=admin_payroll_ledger"
                                style="font-size:13px; color: <?= $page == 'admin_payroll_ledger' ? 'var(--color-primary)' : '#64748b' ?>; text-decoration:none; display:block; padding:8px 0;">
                                <i class="fa-solid fa-book" style="font-size:11px; margin-right:8px;"></i> Livre de Paie
                            </a></li>
                    </ul>
                </li>
            <?php endif; ?>

            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] !== 'super-admin'): ?>
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] !== 'super-admin' && ($hasMessaging ?? true)): ?>
                    <!-- 8. Messagerie App -->
                    <li class="nav-item">
                        <?php
                        require_once 'models/Message.php';
                        $g_unreadMsgsCount = (new Message($pdo))->countUnreadAdmin();
                        ?>
                        <a href="index.php?page=admin_messages" class="nav-link <?= $page == 'admin_messages' ? 'active' : '' ?>"
                            style="display:flex;">
                            <span class="nav-icon"><i class="fa-solid fa-comments"></i></span> Messagerie App
                            <?php if ($g_unreadMsgsCount > 0): ?>
                                <span
                                    style="background:var(--color-primary); color:white; padding:2px 6px; border-radius:10px; font-size:11px; margin-left:auto; font-weight:bold;"><?= $g_unreadMsgsCount ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php elseif (isset($_SESSION['user_role']) && $_SESSION['user_role'] !== 'super-admin'): ?>
                    <li class="nav-item">
                        <a href="index.php?page=admin_billing" class="nav-link" style="opacity:0.6;"
                            title="Option non incluse dans votre forfait">
                            <span class="nav-icon"><i class="fa-solid fa-comments"></i></span> Messagerie <i
                                class="fa-solid fa-lock" style="font-size:10px; margin-left:5px;"></i>
                        </a>
                    </li>
                <?php endif; ?>
            <?php endif; ?>

            <!-- SUPER ADMIN SECTION (ONLY IF SUPER ADMIN) -->
            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'super-admin' && !isset($_SESSION['original_admin_id'])): ?>
                <li class="nav-item">
                    <hr style="border:0; border-top:1px solid rgba(255,255,255,0.1); margin:10px 0;">
                </li>
                <li class="nav-item"><a href="index.php?page=superadmin_dashboard"
                        class="nav-link <?= $page == 'superadmin_dashboard' ? 'active' : '' ?>"><span class="nav-icon"><i
                                class="fa-solid fa-crown"></i></span> Console</a></li>
                <li class="nav-item"><a href="index.php?page=superadmin_entreprises"
                        class="nav-link <?= $page == 'superadmin_entreprises' ? 'active' : '' ?>"><span class="nav-icon"><i
                                class="fa-solid fa-users"></i></span> Gestion Client</a></li>

                <!-- Support SuperAdmin -->
                <?php $unreadSupport = (new Ticket($pdo))->countUnreadForSupport(); ?>
                <li class="nav-item">
                    <a href="index.php?page=superadmin_support"
                        class="nav-link <?= $page == 'superadmin_support' ? 'active' : '' ?>" style="display:flex;">
                        <span class="nav-icon"><i class="fa-solid fa-headset"></i></span> Tickets Support
                        <?php if ($unreadSupport > 0): ?>
                            <span
                                style="background:#ef4444; color:white; padding:2px 6px; border-radius:10px; font-size:11px; margin-left:auto; font-weight:bold;"><?= $unreadSupport ?></span>
                        <?php endif; ?>
                    </a>
                </li>

                <li class="nav-item"><a href="index.php?page=superadmin_promos"
                        class="nav-link <?= $page == 'superadmin_promos' ? 'active' : '' ?>"><span class="nav-icon"><i
                                class="fa-solid fa-ticket"></i></span> Codes Promos</a></li>
                <li class="nav-item"><a href="index.php?page=superadmin_plans"
                        class="nav-link <?= $page == 'superadmin_plans' ? 'active' : '' ?>"><span class="nav-icon"><i
                                class="fa-solid fa-layer-group"></i></span> Gestion Plans</a></li>
                <li class="nav-item">
                    <a href="#"
                        class="nav-link <?= (in_array($page, ['superadmin_gateway_settings', 'superadmin_cinetpay_settings', 'superadmin_moneyfusion_settings'])) ? 'active' : '' ?>"
                        onclick="toggleSubMenu(event, 'paymentSubMenu')">
                        <span class="nav-icon"><i class="fa-solid fa-gear"></i></span> Paramètre de Paiement
                        <i class="fa-solid fa-chevron-down" style="font-size:10px; margin-left:auto;"></i>
                    </a>
                    <ul id="paymentSubMenu"
                        style="list-style:none; padding-left:45px; margin-top:5px; display: <?= (in_array($page, ['superadmin_gateway_settings', 'superadmin_cinetpay_settings', 'superadmin_moneyfusion_settings'])) ? 'block' : 'none' ?>;">
                        <li><a href="index.php?page=superadmin_gateway_settings"
                                style="font-size:13px; color: <?= $page == 'superadmin_gateway_settings' ? 'var(--color-primary)' : '#64748b' ?>; text-decoration:none; display:block; padding:8px 0;">
                                <i class="fa-solid fa-toggle-on" style="font-size:11px; margin-right:8px;"></i> Passerelle
                            </a></li>
                        <li><a href="index.php?page=superadmin_cinetpay_settings"
                                style="font-size:13px; color: <?= $page == 'superadmin_cinetpay_settings' ? 'var(--color-primary)' : '#64748b' ?>; text-decoration:none; display:block; padding:8px 0;">
                                <i class="fa-solid fa-credit-card" style="font-size:11px; margin-right:8px;"></i> CinetPay
                            </a></li>
                        <li><a href="index.php?page=superadmin_moneyfusion_settings"
                                style="font-size:13px; color: <?= $page == 'superadmin_moneyfusion_settings' ? 'var(--color-primary)' : '#64748b' ?>; text-decoration:none; display:block; padding:8px 0;">
                                <i class="fa-solid fa-file-invoice-dollar" style="font-size:11px; margin-right:8px;"></i>
                                Money Fusion
                            </a></li>
                    </ul>
                </li>
                <li class="nav-item"><a href="index.php?page=superadmin_email_settings"
                        class="nav-link <?= $page == 'superadmin_email_settings' ? 'active' : '' ?>"><span class="nav-icon"><i
                                class="fa-solid fa-envelope-circle-check"></i></span> Config. Email</a></li>
                <li class="nav-item"><a href="index.php?page=superadmin_whatsapp"
                        class="nav-link <?= $page == 'superadmin_whatsapp' ? 'active' : '' ?>"><span class="nav-icon"
                            style="color:#25d366;"><i class="fa-brands fa-whatsapp"></i></span> Numéro WhatsApp</a></li>
                <li class="nav-item"><a href="index.php?page=admin_pub"
                        class="nav-link <?= $page == 'admin_pub' ? 'active' : '' ?>"><span class="nav-icon"><i
                                class="fa-solid fa-bullhorn"></i></span> Pub</a></li>
                <li class="nav-item"><a href="index.php?page=superadmin_audit_logs"
                        class="nav-link <?= $page == 'superadmin_audit_logs' ? 'active' : '' ?>"><span class="nav-icon"><i
                                class="fa-solid fa-list-check"></i></span> Audit Logs</a></li>
                <li class="nav-item"><a href="index.php?page=superadmin_security_log"
                        class="nav-link <?= $page == 'superadmin_security_log' ? 'active' : '' ?>"><span class="nav-icon"
                            style="color:#ef4444;"><i class="fa-solid fa-shield-virus"></i></span> Audit Sécurité</a></li>
                <li class="nav-item">
                    <hr style="border:0; border-top:1px solid rgba(255,255,255,0.1); margin:10px 0;">
                </li>
            <?php endif; ?>

            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'owner'): ?>
                <!-- 9. Mon Compte & Facturation -->
                <li class="nav-item">
                    <a href="index.php?page=admin_billing" class="nav-link <?= $page == 'admin_billing' ? 'active' : '' ?>">
                        <span class="nav-icon"><i class="fa-solid fa-file-invoice"></i></span> Mon Compte & Facturation
                    </a>
                </li>
                <!-- 10. Support & Aide (Admin Client) -->
                <?php $unreadAdmin = (new Ticket($pdo))->countUnreadForAdmin($_SESSION['admin_id']); ?>
                <li class="nav-item">
                    <a href="index.php?page=admin_support" class="nav-link <?= $page == 'admin_support' ? 'active' : '' ?>"
                        style="display:flex;">
                        <span class="nav-icon"><i class="fa-solid fa-circle-question"></i></span> Support & Aide
                        <?php if ($unreadAdmin > 0): ?>
                            <span
                                style="background:var(--color-primary); color:white; padding:2px 6px; border-radius:10px; font-size:11px; margin-left:auto; font-weight:bold;"><?= $unreadAdmin ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endif; ?>

            <!-- FOOTER LINKS -->
            <li class="nav-item" style="margin-top:auto;">
                <a href="index.php?page=landing#pricing-grid" class="nav-link" target="_blank"
                    rel="noopener noreferrer">
                    <span class="nav-icon"><i class="fa-solid fa-dollar-sign"></i></span> Nos tarifs
                </a>
            </li>
            <li class="nav-item">
                <a href="index.php?page=terms" class="nav-link" target="_blank" rel="noopener noreferrer">
                    <span class="nav-icon"><i class="fa-solid fa-file-circle-check"></i></span> Conditions d'utilisation
                </a>
            </li>
            <li class="nav-item">
                <a href="index.php?page=privacy" class="nav-link" target="_blank" rel="noopener noreferrer">
                    <span class="nav-icon"><i class="fa-solid fa-user-lock"></i></span> Confidentialité
                </a>
            </li>
            <li class="nav-item">
                <a href="index.php?page=logout" class="nav-link" style="color:var(--color-danger);">
                    <span class="nav-icon"><i class="fa-solid fa-sign-out-alt"></i></span> Déconnexion
                </a>
            </li>
        </ul>
    </aside>

    <main class="main-content">
        <?php if (isset($isTrial) && $isTrial): ?>
            <div
                style="background: var(--color-primary, #4F46E5); color: white; display: flex; justify-content: space-between; align-items: center; padding: 12px 20px; font-weight: 500; font-size: 14px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); margin-bottom: 20px; border-radius: 8px;">
                <div>
                    <i class="fa-solid fa-clock-rotate-left" style="margin-right: 8px;"></i>
                    Il vous reste <strong><?= $trialDaysLeft ?> jour(s)</strong> pour la fin de l'essai.
                </div>
                <a href="index.php?page=admin_billing"
                    style="background: white; color: var(--color-primary, #4F46E5); padding: 8px 16px; border-radius: 6px; text-decoration: none; font-weight: 700; transition: all 0.2s;">
                    <i class="fa-solid fa-credit-card" style="margin-right: 5px;"></i> Payer mon Abonnement
                </a>
            </div>
        <?php endif; ?>

        <header class="top-header">
            <div style="display:flex; align-items:center;">
                <button class="menu-toggle" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>
                <h2 style="font-size:18px; font-weight:700; margin-left:10px;">
                    <?= $page == 'admin_dashboard' ? 'Vue d\'ensemble' : 'Gestion' ?>
                </h2>
            </div>

            <div class="user-profile" onclick="toggleUserDropdown(event)" style="position:relative; cursor:pointer;"
                title="Menu utilisateur">
                <div class="user-info-text">
                    <div style="font-weight:600;"><?= $_SESSION['admin_email'] ?? 'Admin' ?></div>
                    <small class="badge badge-success" style="font-size:10px;">En ligne <i
                            class="fa-solid fa-chevron-down" style="margin-left:5px;"></i></small>
                </div>
                <div class="avatar"><i class="fa-solid fa-user-shield"></i></div>

                <div id="userDropdown" class="user-dropdown-menu"
                    style="display:none; position:absolute; right:0; top:calc(100% + 15px); background:#fff; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,0.1); width:230px; z-index:1000; padding:10px 0; border:1px solid #e2e8f0; text-align:left;">
                    <style>
                        .user-dropdown-menu a {
                            display: flex;
                            align-items: center;
                            padding: 12px 20px;
                            color: var(--slate);
                            text-decoration: none;
                            font-size: 14px;
                            font-weight: 500;
                            transition: all 0.2s;
                        }

                        .user-dropdown-menu a:hover {
                            background: #f8fafc;
                            color: var(--primary);
                            padding-left: 25px;
                        }

                        .user-dropdown-menu a i {
                            width: 22px;
                            margin-right: 10px;
                            color: var(--slate-light);
                            text-align: center;
                            transition: color 0.2s;
                        }

                        .user-dropdown-menu a:hover i {
                            color: var(--primary);
                        }
                    </style>
                    <a
                        href="<?= (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'owner') ? 'index.php?page=admin_billing' : '#' ?>"><i
                            class="fa-solid fa-user"></i> Mon Profil</a>
                    <a href="#"
                        onclick="event.preventDefault(); event.stopPropagation(); openPassModal(); document.getElementById('userDropdown').style.display='none';"><i
                            class="fa-solid fa-lock"></i> Modifier mot de passe</a>
                    <div style="height:1px; background:#e2e8f0; margin:5px 0;"></div>
                    <a href="index.php?page=logout" style="color:#ef4444;"><i class="fa-solid fa-sign-out-alt"
                            style="color:#ef4444;"></i> Se déconnecter</a>
                </div>
            </div>
            <script>
                function toggleUserDropdown(e) {
                    e.stopPropagation();
                    const dropdown = document.getElementById('userDropdown');
                    dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
                }
                document.addEventListener('click', function (e) {
                    const dropdown = document.getElementById('userDropdown');
                    if (dropdown && dropdown.style.display === 'block') {
                        dropdown.style.display = 'none';
                    }
                });
            </script>
        </header>

        <?php echo $content ?? ''; ?>

        <footer>
            <div class="footer-content">
                All Rights Reserved by Marvens Group and Services SARL &copy;2026
            </div>
            <div class="footer-version">
                V1.6
            </div>
        </footer>
    </main>
</div>

<script>
    function toggleSubMenu(e, menuId) {
        e.preventDefault();
        const menu = document.getElementById(menuId);
        const icon = e.currentTarget.querySelector('.fa-chevron-down');
        if (menu.style.display === 'none' || menu.style.display === '') {
            menu.style.display = 'block';
            if (icon) icon.style.transform = 'rotate(180deg)';
        } else {
            menu.style.display = 'none';
            if (icon) icon.style.transform = 'rotate(0deg)';
        }
    }

    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('sw.js')
                .then(registration => console.log('ServiceWorker enregistré avec succès.', registration.scope))
                .catch(error => console.log('Erreur ServiceWorker:', error));
        });
    }
</script>