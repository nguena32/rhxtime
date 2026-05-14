<?php
try {
  $stmtWA = $pdo->prepare("SELECT setting_value FROM platform_settings WHERE setting_key = 'WHATSAPP_NUMBER' LIMIT 1");
  $stmtWA->execute();
  $whatsappNumber = $stmtWA->fetchColumn();
} catch (Exception $e) {
  $whatsappNumber = null;
}

$dashLink = 'index.php?page=admin_dashboard';
if (isset($_SESSION['user_role'])) {
  if ($_SESSION['user_role'] === 'super-admin')
    $dashLink = 'index.php?page=superadmin_dashboard';
  elseif ($_SESSION['user_role'] === 'employee')
    $dashLink = 'index.php?page=employee_scan';
}
$isLoggedIn = isset($_SESSION['admin_id']) || isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>RHXtimes — Le pointage qui ne ment pas</title>
  <meta name="description"
    content="Solution SaaS de pointage GPS zéro hardware pour BTP, Sécurité et Multi-sites en Afrique.">
  <link rel="manifest" href="manifest.json">
  <link rel="icon" type="image/png" href="assets/images/favicon.png">
  <link rel="stylesheet" href="themes/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap">
  <link rel="stylesheet" href="themes/css/aos.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    html {
      scroll-behavior: smooth;
    }

    body {
      font-family: 'Inter', sans-serif;
      color: #1e293b;
      background: #fff;
      line-height: 1.6;
    }

    :root {
      --primary: #0f4c81;
      --accent: #f97316;
      --accent2: #10b981;
      --dark: #0f172a;
      --light: #f8fafc;
      --text: #334155;
      --muted: #64748b;
      --border: #e2e8f0;
      --white: #ffffff;
      --radius: 12px;
      --shadow: 0 4px 24px rgba(0, 0, 0, .08);
    }

    /* UTILITIES */
    .btn-primary-rhx {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: var(--accent);
      color: #fff;
      font-weight: 700;
      padding: 14px 28px;
      border-radius: 50px;
      border: none;
      cursor: pointer;
      font-size: .95rem;
      transition: all .3s;
      text-decoration: none;
    }

    .btn-primary-rhx:hover {
      background: #ea6a0a;
      color: #fff;
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(249, 115, 22, .35);
    }

    .btn-outline-rhx {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: transparent;
      color: var(--primary);
      font-weight: 700;
      padding: 13px 28px;
      border-radius: 50px;
      border: 2px solid var(--primary);
      cursor: pointer;
      font-size: .95rem;
      transition: all .3s;
      text-decoration: none;
    }

    .btn-outline-rhx:hover {
      background: var(--primary);
      color: #fff;
    }

    .btn-white {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: #fff;
      color: var(--primary);
      font-weight: 700;
      padding: 14px 28px;
      border-radius: 50px;
      border: none;
      cursor: pointer;
      font-size: .95rem;
      transition: all .3s;
      text-decoration: none;
    }

    .btn-white:hover {
      background: #f1f5f9;
      color: var(--primary);
    }

    .section-label {
      display: inline-block;
      background: rgba(15, 76, 129, .1);
      color: var(--primary);
      font-weight: 700;
      font-size: .8rem;
      padding: 6px 14px;
      border-radius: 50px;
      margin-bottom: 12px;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    .section-h {
      font-size: clamp(1.6rem, 4vw, 2.4rem);
      font-weight: 800;
      color: var(--dark);
      line-height: 1.25;
    }

    .section-p {
      color: var(--muted);
      font-size: 1rem;
      margin-top: 12px;
      line-height: 1.8;
    }

    .text-accent {
      color: var(--accent);
    }

    .text-primary-rhx {
      color: var(--primary);
    }

    .bg-light-rhx {
      background: var(--light);
    }

    .bg-dark-rhx {
      background: var(--dark);
    }

    .card-rhx {
      background: #fff;
      border-radius: var(--radius);
      border: 1px solid var(--border);
      padding: 30px;
      transition: all .3s;
      box-shadow: var(--shadow);
    }

    .card-rhx:hover {
      transform: translateY(-6px);
      box-shadow: 0 16px 40px rgba(0, 0, 0, .12);
      border-color: var(--primary);
    }

    /* NAVBAR */
    .navbar-rhx {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 1000;
      padding: 0 20px;
      background: rgba(255, 255, 255, .95);
      backdrop-filter: blur(10px);
      border-bottom: 1px solid rgba(0, 0, 0, .06);
      transition: all .3s;
    }

    .navbar-inner {
      max-width: 1200px;
      margin: 0 auto;
      display: flex;
      align-items: center;
      justify-content: space-between;
      height: 68px;
    }

    .nav-logo img {
      height: 40px;
      border-radius: 8px;
    }

    .nav-links {
      display: flex;
      align-items: center;
      gap: 28px;
      list-style: none;
    }

    .nav-links a {
      color: var(--text);
      font-weight: 500;
      font-size: .9rem;
      text-decoration: none;
      transition: color .2s;
    }

    .nav-links a:hover {
      color: var(--primary);
    }

    .nav-cta {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .nav-cta a {
      font-size: .88rem;
      padding: 10px 20px;
    }

    .hamburger {
      display: none;
      flex-direction: column;
      gap: 5px;
      cursor: pointer;
      border: none;
      background: none;
      padding: 5px;
    }

    .hamburger span {
      display: block;
      width: 24px;
      height: 2px;
      background: var(--dark);
      border-radius: 2px;
      transition: all .3s;
    }

    .mobile-nav {
      display: none;
      position: fixed;
      top: 68px;
      left: 0;
      right: 0;
      background: #fff;
      border-bottom: 1px solid var(--border);
      z-index: 999;
      padding: 20px;
      box-shadow: 0 8px 24px rgba(0, 0, 0, .1);
    }

    .mobile-nav.open {
      display: block;
    }

    .mobile-nav ul {
      list-style: none;
      margin-bottom: 16px;
    }

    .mobile-nav ul li {
      padding: 10px 0;
      border-bottom: 1px solid var(--border);
    }

    .mobile-nav ul li a {
      color: var(--text);
      font-weight: 500;
      text-decoration: none;
      font-size: 1rem;
    }

    .mobile-nav-btns {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    /* RESPONSIVE LOGOS */
    .logo-desktop {
      display: block;
    }

    .logo-mobile {
      display: none;
    }

    @media (max-width: 991px) {
      .logo-desktop {
        display: none;
      }

      .logo-mobile {
        display: block;
      }

      .navbar-rhx {
        background: var(--primary);
        border-bottom: none;
      }

      .hamburger span {
        background: #fff;
      }

      .nav-links a {
        color: #fff;
      }

      .navbar-inner {
        height: 60px;
      }
    }


    /* CONTENT AREA ADJUSTMENT */
    .main-content {
      padding-top: 80px;
      /* Space for the fixed navbar */
      min-height: calc(100vh - 300px);
      /* Fill space before footer */
    }

    /* FOOTER */
    .footer {
      background: var(--dark);
      padding: 64px 20px 32px;
      color: rgba(255, 255, 255, .7);
    }

    .footer-inner {
      max-width: 1200px;
      margin: 0 auto;
    }

    .footer-grid {
      display: grid;
      grid-template-columns: 2fr 1fr 1fr 1fr;
      gap: 40px;
      margin-bottom: 48px;
    }

    .footer-brand p {
      font-size: .88rem;
      line-height: 1.8;
      margin-top: 14px;
      color: rgba(255, 255, 255, .55);
    }

    .footer-col h6 {
      color: #fff;
      font-weight: 700;
      margin-bottom: 16px;
      font-size: .9rem;
    }

    .footer-links {
      list-style: none;
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .footer-links a {
      color: rgba(255, 255, 255, .55);
      text-decoration: none;
      font-size: .86rem;
      transition: color .2s;
    }

    .footer-links a:hover {
      color: #fff;
    }

    .footer-bottom {
      border-top: 1px solid rgba(255, 255, 255, .08);
      padding-top: 24px;
      text-align: center;
      color: rgba(255, 255, 255, .35);
      font-size: .82rem;
    }

    /* CONTACT MODAL */
    .modal-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, .6);
      z-index: 9999;
      align-items: center;
      justify-content: center;
      backdrop-filter: blur(4px);
    }

    .modal-overlay.open {
      display: flex;
    }

    .modal-box {
      background: #fff;
      border-radius: 20px;
      width: 95%;
      max-width: 480px;
      overflow: hidden;
      box-shadow: 0 30px 60px rgba(0, 0, 0, .3);
    }

    .modal-head {
      background: linear-gradient(135deg, var(--primary), #1e6fb8);
      padding: 28px 30px;
      position: relative;
    }

    .modal-head h4 {
      color: #fff;
      font-weight: 800;
      margin: 0;
    }

    .modal-head p {
      color: rgba(255, 255, 255, .8);
      margin: 6px 0 0;
      font-size: .88rem;
    }

    .modal-close {
      position: absolute;
      top: 14px;
      right: 14px;
      background: rgba(255, 255, 255, .2);
      border: none;
      color: #fff;
      width: 30px;
      height: 30px;
      border-radius: 50%;
      font-size: 1.1rem;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .modal-body {
      padding: 28px 30px;
    }

    .form-field {
      margin-bottom: 14px;
    }

    .form-field input,
    .form-field textarea {
      width: 100%;
      padding: 12px 16px;
      border: 1px solid var(--border);
      border-radius: 10px;
      font-size: .9rem;
      outline: none;
      font-family: inherit;
      transition: border-color .2s;
    }

    .form-field input:focus,
    .form-field textarea:focus {
      border-color: var(--primary);
    }

    .form-field textarea {
      resize: none;
    }

    /* FLOATING WIDGETS */
    .float-widgets {
      position: fixed;
      bottom: 28px;
      right: 24px;
      z-index: 9000;
      display: flex;
      flex-direction: column;
      gap: 12px;
      align-items: center;
    }

    .fw-btn {
      width: 52px;
      height: 52px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      font-size: 1.3rem;
      text-decoration: none;
      box-shadow: 0 4px 16px rgba(0, 0, 0, .25);
      transition: all .3s;
    }

    .fw-btn:hover {
      transform: scale(1.12);
      color: #fff;
    }

    .fw-wa {
      background: #25d366;
    }

    .fw-mail {
      background: var(--primary);
    }

    .pulse-ring {
      position: relative;
    }

    .pulse-ring::after {
      content: '';
      position: absolute;
      top: -2px;
      right: -2px;
      width: 12px;
      height: 12px;
      background: #ef4444;
      border-radius: 50%;
      border: 2px solid #fff;
      animation: pulse 2s infinite;
    }

    @keyframes pulse {

      0%,
      100% {
        transform: scale(.95);
        box-shadow: 0 0 0 0 rgba(239, 68, 68, .6);
      }

      70% {
        transform: scale(1);
        box-shadow: 0 0 0 8px rgba(239, 68, 68, 0);
      }
    }

    /* RESPONSIVE */
    @media(max-width:991px) {
      .footer-grid {
        grid-template-columns: 1fr 1fr;
      }

      .nav-links {
        display: none;
      }

      .hamburger {
        display: flex;
      }

      .nav-cta {
        display: none;
      }
    }

    @media(max-width:640px) {
      .footer-grid {
        grid-template-columns: 1fr;
      }

      .footer {
        padding: 48px 16px 28px;
      }

      .footer-brand {
        margin-bottom: 8px;
      }

      .main-content {
        padding-top: 68px;
      }
    }

    html {
      overflow-x: hidden;
    }

    body {
      overflow-x: hidden;
    }
  </style>
</head>

<body>

  <!-- NAVBAR -->
  <nav class="navbar-rhx">
    <div class="navbar-inner">
      <div class="nav-logo">
        <a href="index.php">
          <img src="assets/images/logo_texte_bleu.png" alt="RHXtimes" class="logo-desktop">
          <img src="assets/images/logo_texte_blanc.png" alt="RHXtimes" class="logo-mobile">
        </a>
      </div>
      <ul class="nav-links">
        <li><a href="index.php?page=landing#hero">Accueil</a></li>
        <li><a href="index.php?page=landing#pourquoi">Pourquoi ?</a></li>
        <li><a href="index.php?page=landing#comment">Comment ?</a></li>
        <li><a href="index.php?page=landing#pricing-grid">Tarifs</a></li>
        <li><a href="index.php?page=landing#faq">FAQ</a></li>
      </ul>
      <div class="nav-cta">
        <?php if ($isLoggedIn): ?>
          <a href="<?= $dashLink ?>" class="btn-primary-rhx"><i class="fa fa-gauge-high"></i> Dashboard</a>
        <?php else: ?>
          <a href="index.php?page=login" class="btn-outline-rhx"><i class="fa fa-right-to-bracket"></i> Connexion</a>
          <a href="index.php?page=register" class="btn-primary-rhx"><i class="fa fa-rocket"></i> Essai Gratuit</a>
        <?php endif; ?>
      </div>
      <button class="hamburger" id="hamBtn" onclick="toggleMobile()">
        <span></span><span></span><span></span>
      </button>
    </div>
  </nav>

  <!-- MOBILE NAV -->
  <div class="mobile-nav" id="mobileNav">
    <ul>
      <li><a href="index.php?page=landing#hero" onclick="toggleMobile()">Accueil</a></li>
      <li><a href="index.php?page=landing#pourquoi" onclick="toggleMobile()">Pourquoi ?</a></li>
      <li><a href="index.php?page=landing#comment" onclick="toggleMobile()">Comment ?</a></li>
      <li><a href="index.php?page=landing#pricing-grid" onclick="toggleMobile()">Tarifs</a></li>
      <li><a href="index.php?page=landing#faq" onclick="toggleMobile()">FAQ</a></li>
    </ul>
    <div class="mobile-nav-btns">
      <?php if ($isLoggedIn): ?>
        <a href="<?= $dashLink ?>" class="btn-primary-rhx" style="justify-content:center;"><i
            class="fa fa-gauge-high"></i> Dashboard</a>
      <?php else: ?>
        <a href="index.php?page=login" class="btn-outline-rhx" style="justify-content:center;"><i
            class="fa fa-right-to-bracket"></i> Connexion</a>
        <a href="index.php?page=register" class="btn-primary-rhx" style="justify-content:center;"><i
            class="fa fa-rocket"></i> Essai Gratuit</a>
      <?php endif; ?>
    </div>
  </div>

  <div class="main-content">
    <?= $content ?? '' ?>
  </div>

  <!-- FOOTER -->
  <footer class="footer">
    <div class="footer-inner">
      <div class="footer-grid">
        <div class="footer-brand">
          <a href="index.php"><img src="assets/images/logo_texte_blanc.png" alt="RHXtimes" style="height:40px;"></a>
          <p>"Votre présence certifiée, votre croissance sécurisée."<br>Solution SaaS de pointage GPS pour les
            entreprises africaines.</p>
        </div>
        <div>
          <div class="footer-col">
            <h6>Navigation</h6>
          </div>
          <ul class="footer-links">
            <li><a href="index.php?page=landing#hero">Accueil</a></li>
            <li><a href="index.php?page=landing#pourquoi">Pourquoi RHXtimes ?</a></li>
            <li><a href="index.php?page=landing#comment">Comment ça marche</a></li>
            <li><a href="index.php?page=landing#pricing-grid">Tarifs</a></li>
            <li><a href="index.php?page=landing#faq">FAQ</a></li>
          </ul>
        </div>
        <div>
          <div class="footer-col">
            <h6>Légal</h6>
          </div>
          <ul class="footer-links">
            <li><a href="index.php?page=privacy">Confidentialité</a></li>
            <li><a href="index.php?page=terms">Conditions d'utilisation</a></li>
            <li><a href="index.php?page=login">Connexion</a></li>
            <li><a href="index.php?page=register">S'inscrire</a></li>
          </ul>
        </div>
        <div>
          <div class="footer-col">
            <h6>Contact</h6>
          </div>
          <ul class="footer-links">
            <li><i class="fa fa-location-dot" style="width:16px;margin-right:6px;"></i> Yaoundé, Cameroun</li>
            <?php if (!empty($whatsappNumber)): ?>
              <li><i class="fab fa-whatsapp"
                  style="width:16px;margin-right:6px;color:#25d366;"></i><?= htmlspecialchars($whatsappNumber) ?></li>
            <?php endif; ?>
            <li><a href="javascript:void(0);" onclick="openModal()" style="color:var(--accent);font-weight:600;"><i
                  class="fa fa-envelope" style="width:16px;margin-right:6px;"></i>Envoyer un message</a></li>
          </ul>
        </div>
      </div>
      <div class="footer-bottom">
        <p>© <?= date('Y') ?> <strong>RHXtimes</strong> — Marvens Group and Services SARL. Tous droits réservés.</p>
      </div>
    </div>
  </footer>

  <!-- CONTACT MODAL -->
  <div class="modal-overlay" id="contactModal">
    <div class="modal-box">
      <div class="modal-head">
        <h4>Laissez-nous un message</h4>
        <p>Réponse sous 24h.</p>
        <button class="modal-close" onclick="closeModal()"><i class="fa fa-xmark"></i></button>
      </div>
      <div class="modal-body">
        <form action="index.php?page=contact_submit" method="POST">
          <div class="form-field"><input type="text" name="prenom" placeholder="Votre prénom" required></div>
          <div class="form-field"><input type="email" name="email" placeholder="Votre email" required></div>
          <div class="form-field"><input type="tel" name="tel" placeholder="Téléphone (optionnel)"></div>
          <div class="form-field"><textarea name="message" placeholder="Votre message" rows="4" required></textarea>
          </div>
          <button type="submit" class="btn-primary-rhx" style="width:100%;justify-content:center;"><i
              class="fa fa-paper-plane"></i> Envoyer</button>
        </form>
      </div>
    </div>
  </div>

  <!-- FLOATING WIDGETS -->
  <div class="float-widgets">
    <a href="javascript:void(0);" onclick="openModal()" class="fw-btn fw-mail" title="Nous écrire"><i
        class="fa fa-envelope"></i></a>
    <?php if (!empty($whatsappNumber)): ?>
      <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $whatsappNumber) ?>?text=Bonjour%2C+infos+sur+RHXtimes+svp."
        class="fw-btn fw-wa pulse-ring" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
    <?php endif; ?>
  </div>

  <!-- BACK TO TOP -->
  <button onclick="window.scrollTo({top:0,behavior:'smooth'})" id="backTop"
    style="position:fixed;bottom:92px;right:28px;width:42px;height:42px;border-radius:50%;background:var(--primary);color:#fff;border:none;cursor:pointer;display:none;align-items:center;justify-content:center;box-shadow:0 4px 16px rgba(0,0,0,.25);z-index:8999;font-size:1rem;"><i
      class="fa fa-arrow-up"></i></button>

  <script src="themes/js/vendor/jquery-1.12.4.min.js"></script>
  <script>
    // Mobile nav
    function toggleMobile() {
      document.getElementById('mobileNav').classList.toggle('open');
    }

    // Modal
    function openModal() { document.getElementById('contactModal').classList.add('open'); }
    function closeModal() { document.getElementById('contactModal').classList.remove('open'); }
    document.getElementById('contactModal').addEventListener('click', function (e) { if (e.target === this) closeModal(); });

    // Back to top
    window.addEventListener('scroll', function () {
      var btn = document.getElementById('backTop');
      btn.style.display = window.scrollY > 400 ? 'flex' : 'none';
    });

    // Sticky nav
    window.addEventListener('scroll', function () {
      var nav = document.querySelector('.navbar-rhx');
      if (window.scrollY > 10) { nav.style.boxShadow = '0 4px 20px rgba(0,0,0,.1)'; }
      else { nav.style.boxShadow = 'none'; }
    });

    // Service worker PWA
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', function () { navigator.serviceWorker.register('sw.js?v=3').catch(function () { }); });
    }

    // SweetAlert feedback
    <?php $pg = $_GET ?? []; ?>
<?php if (!empty($pg['contact_success'])): ?>Swal.fire({ title: 'Message envoyé !', text: 'Réponse sous 24h.', icon: 'success', confirmButtonColor: '#0f4c81' }); <?php endif; ?>
<?php if (!empty($pg['contact_error'])): ?>Swal.fire({ title: 'Erreur', text: 'Une erreur est survenue.', icon: 'error', confirmButtonColor: '#0f4c81' }); <?php endif; ?>
  </script>
</body>

</html>