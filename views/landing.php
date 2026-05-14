<?php
try {
    $stmtPlans = $pdo->query("SELECT * FROM plans ORDER BY montant_mensuel ASC");
    $plans = $stmtPlans->fetchAll();
    
    // Calcul de la réduction maximale pour affichage dynamique dans le toggle
    $maxGlobalDisc = 0;
    foreach($plans as $pl) {
        $m_m = (float)($pl['montant_mensuel'] ?? 0);
        $m_a = (float)($pl['montant_annuel_mensualise'] ?? 0);
        // Valide uniquement si les deux prix sont renseignés et la remise est raisonnable (1-50%)
        if ($m_m > 0 && $m_a > 0 && $m_a < $m_m) {
            $d = round((1 - $m_a / $m_m) * 100);
            if ($d >= 1 && $d <= 50 && $d > $maxGlobalDisc) $maxGlobalDisc = $d;
        }
    }
    // Fallback si aucun plan ne définit une remise annuelle cohérente
    if ($maxGlobalDisc <= 0) $maxGlobalDisc = 20;
} catch (Exception $e) { $plans = []; $maxGlobalDisc = 0; }

// Mapping des fonctionnalités "à la carte" pour l'affichage des tarifs
$featureMap = [
    'has_qr_code'            => 'Pointage QR Code',
    'has_direct_scan'        => 'Pointage direct (One-Tap)',
    'has_gps_precision'      => 'Vérification GPS Haute Précision',
    'has_advanced_dashboard' => 'Tableau de bord avancé',
    'has_payroll_mgmt'       => 'Gestion de la paie',
    'has_auto_payroll'       => 'Calcul de paie automatique',
    'has_pdf_export'         => 'Export PDF bulletin de paie',
    'has_unlimited_history'  => 'Historique de présence illimité',
    'has_web_mobile'         => 'Accès Web & Mobile',
    'has_email_alerts'       => 'Alertes e-mail',
    'has_messaging'          => 'Messagerie interne'
];
?>
<style>
/* ================================================================
   LANDING PAGE — CSS COMPLET & RESPONSIVE (Mobile-First)
   Isolé dans landing.php pour ne pas affecter les autres pages
   ================================================================ */

/* --- RESET & BASE LANDING --- */
.landing-page-wrapper {
  overflow-x: hidden;
  width: 100%;
}

/* --- SECTIONS GÉNÉRIQUES --- */
.section {
  padding: 80px 20px;
  width: 100%;
}
.section-inner {
  max-width: 1200px;
  margin: 0 auto;
}
.section-header {
  text-align: center;
  margin-bottom: 56px;
}
.section-h {
  font-size: clamp(1.5rem, 5vw, 2.4rem);
  font-weight: 800;
  color: #0f172a;
  line-height: 1.25;
}
.section-p {
  color: #64748b;
  font-size: 1rem;
  margin-top: 12px;
  line-height: 1.8;
}
.section-label {
  display: inline-block;
  background: rgba(15,76,129,.1);
  color: #0f4c81;
  font-weight: 700;
  font-size: .8rem;
  padding: 6px 14px;
  border-radius: 50px;
  margin-bottom: 12px;
  text-transform: uppercase;
  letter-spacing: 1px;
}
.text-accent { color: #f97316; }

/* --- HERO --- */
.hero {
  background: linear-gradient(135deg, #0f172a 0%, #0f4c81 60%, #1e6fb8 100%);
  padding: 60px 20px 60px;
  position: relative;
  overflow: hidden;
}
.hero::before {
  content: '';
  position: absolute;
  top: -50%;
  right: -20%;
  width: 600px;
  height: 600px;
  background: radial-gradient(circle, rgba(249,115,22,.15) 0%, transparent 65%);
  pointer-events: none;
}
.hero-inner {
  max-width: 1200px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 60px;
  align-items: center;
}
.hero-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: rgba(249,115,22,.2);
  color: #fdba74;
  padding: 8px 16px;
  border-radius: 50px;
  font-size: .82rem;
  font-weight: 700;
  border: 1px solid rgba(249,115,22,.3);
  margin-bottom: 20px;
}
.hero-h1 {
  font-size: clamp(1.8rem, 5vw, 3.2rem);
  font-weight: 900;
  color: #fff;
  line-height: 1.15;
  margin-bottom: 20px;
  letter-spacing: -.5px;
}
.hero-h1 span { color: #f97316; }
.hero-p {
  color: rgba(255,255,255,.8);
  font-size: clamp(.9rem, 2vw, 1.05rem);
  line-height: 1.8;
  margin-bottom: 32px;
}
.hero-btns {
  display: flex;
  flex-wrap: wrap;
  gap: 14px;
  margin-bottom: 28px;
}
.hero-trust {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  margin-bottom: 40px;
}
.hero-trust-item {
  display: flex;
  align-items: center;
  gap: 7px;
  color: rgba(255,255,255,.75);
  font-size: .85rem;
  font-weight: 500;
}
.hero-trust-item .fa { color: #4ade80; }
.hero-stats {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  background: rgba(255,255,255,.06);
  border: 1px solid rgba(255,255,255,.12);
  border-radius: 16px;
  padding: 20px;
}
.hero-stat { text-align: center; }
.hero-stat .num {
  font-size: clamp(1.4rem, 4vw, 2rem);
  font-weight: 900;
  color: #fff;
  line-height: 1;
}
.hero-stat .num span { font-size: clamp(.9rem, 2vw, 1.2rem); color: #f97316; }
.hero-stat p { color: rgba(255,255,255,.55); font-size: .75rem; margin-top: 4px; }
.hero-img-wrap { position: relative; }
.hero-img-wrap img {
  width: 100%;
  border-radius: 20px;
  box-shadow: 0 30px 60px rgba(0,0,0,.4);
  display: block;
}
.hero-float-badge {
  position: absolute;
  bottom: -20px;
  left: -20px;
  background: #fff;
  border-radius: 14px;
  padding: 14px 18px;
  display: flex;
  align-items: center;
  gap: 12px;
  box-shadow: 0 10px 30px rgba(0,0,0,.2);
}
.hero-float-badge .icon {
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #0f4c81, #1e6fb8);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 1.1rem;
  flex-shrink: 0;
}
.hero-float-badge strong { display: block; color: #0f172a; font-size: .88rem; }
.hero-float-badge span { color: #64748b; font-size: .78rem; }

/* --- TRUST BAR --- */
.trust-bar {
  background: #fff;
  border-top: 1px solid #e2e8f0;
  border-bottom: 1px solid #e2e8f0;
  padding: 20px;
}
.trust-bar-inner {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 24px;
}
.trust-item {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #475569;
  font-size: .88rem;
  font-weight: 600;
  white-space: nowrap;
}
.trust-item .fa { color: #0f4c81; font-size: 1rem; }

/* --- CARTES GÉNÉRIQUES --- */
.card-rhx {
  background: #fff;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  padding: 30px;
  transition: all .3s;
  box-shadow: 0 4px 24px rgba(0,0,0,.08);
}
.card-rhx:hover {
  transform: translateY(-6px);
  box-shadow: 0 16px 40px rgba(0,0,0,.12);
  border-color: #0f4c81;
}

/* --- GRILLE 3 COLONNES --- */
.grid-3 {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 28px;
}

/* --- GRILLE 4 COLONNES --- */
.grid-4 {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 24px;
}

/* --- SECTION 2 : SCEPTIQUES --- */
.skeptic-card { position: relative; }
.s-icon-wrap {
  width: 52px;
  height: 52px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.3rem;
  margin-bottom: 16px;
}
.skeptic-card h5 {
  font-size: 1rem;
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 10px;
}
.skeptic-card p { color: #64748b; font-size: .9rem; line-height: 1.7; }
.verdict-tag {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #f0fdf4;
  color: #166534;
  font-size: .78rem;
  font-weight: 700;
  padding: 6px 12px;
  border-radius: 50px;
  margin-top: 14px;
  border: 1px solid #bbf7d0;
}

/* --- SECTION 3 : COMMENT ÇA MARCHE --- */
.feature-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 60px;
  align-items: center;
}
.steps-list { display: flex; flex-direction: column; gap: 20px; }
.step {
  display: flex;
  align-items: flex-start;
  gap: 16px;
}
.step-num {
  width: 36px;
  height: 36px;
  min-width: 36px;
  background: linear-gradient(135deg, #0f4c81, #1e6fb8);
  color: #fff;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  font-size: .9rem;
}
.step h6 { font-size: .9rem; font-weight: 700; color: #0f172a; margin-bottom: 4px; }
.step p { color: #64748b; font-size: .85rem; line-height: 1.6; }

/* --- SECTION 4 : PILIERS --- */
.pilier-card {
  background: #fff;
  border-radius: 16px;
  padding: 28px;
  border: 1px solid #e2e8f0;
  transition: all .3s;
  box-shadow: 0 4px 24px rgba(0,0,0,.06);
}
.pilier-card:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(0,0,0,.1); }
.p-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.3rem;
  margin-bottom: 14px;
}
.pilier-card h5 { font-size: .95rem; font-weight: 700; color: #0f172a; margin-bottom: 8px; }
.pilier-card p { color: #64748b; font-size: .85rem; line-height: 1.6; margin-bottom: 14px; }
.roi-badge {
  display: inline-block;
  background: rgba(15,76,129,.1);
  color: #0f4c81;
  padding: 5px 12px;
  border-radius: 50px;
  font-size: .75rem;
  font-weight: 700;
}

/* --- SECTION 5 : PRICING --- */
.pricing-section {
  background: linear-gradient(135deg, #0f172a 0%, #0f4c81 100%);
  padding: 80px 20px;
}
.pricing-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 24px;
  margin-top: 40px;
}
.plan-card {
  background: rgba(255,255,255,.08);
  border: 1px solid rgba(255,255,255,.15);
  border-radius: 20px;
  padding: 32px;
  position: relative;
  transition: all .3s;
  color: #fff;
}
.plan-card:hover { background: rgba(255,255,255,.12); transform: translateY(-4px); }
.plan-card.featured {
  background: #fff;
  border-color: #f97316;
  color: #0f172a;
  box-shadow: 0 20px 50px rgba(249,115,22,.3);
}
.featured-tag {
  position: absolute;
  top: -14px;
  left: 50%;
  transform: translateX(-50%);
  background: linear-gradient(90deg, #f97316, #ea580c);
  color: #fff;
  padding: 5px 18px;
  border-radius: 50px;
  font-size: .78rem;
  font-weight: 800;
  white-space: nowrap;
}
.plan-name { font-size: 1rem; font-weight: 700; margin-bottom: 4px; }
.plan-card.featured .plan-name { color: #0f172a; }
.plan-sub { font-size: .82rem; color: rgba(255,255,255,.55); margin-bottom: 20px; }
.plan-card.featured .plan-sub { color: #64748b; }
.plan-price {
  font-size: clamp(1.8rem, 4vw, 2.4rem);
  font-weight: 900;
  margin-bottom: 4px;
  line-height: 1;
}
.plan-card.featured .plan-price { color: #0f172a; }
.plan-period { font-size: .8rem; color: rgba(255,255,255,.5); margin-bottom: 24px; }
.plan-card.featured .plan-period { color: #64748b; }
.plan-feats { list-style: none; margin-bottom: 28px; display: flex; flex-direction: column; gap: 10px; }
.plan-feat-item { font-size: .85rem; color: rgba(255,255,255,.75); display: flex; align-items: center; gap: 8px; }
.plan-card.featured .plan-feat-item { color: #475569; }
.plan-feat-item .fa { color: #4ade80; flex-shrink: 0; }
.billing-toggle {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 14px;
  margin-top: 28px;
}
.billing-label {
  color: rgba(255,255,255,.5);
  font-weight: 600;
  font-size: .9rem;
  cursor: pointer;
  transition: color .2s;
}
.billing-label.active { color: #fff; }
.saving-tag {
  background: rgba(249,115,22,.2);
  color: #fdba74;
  padding: 2px 8px;
  border-radius: 50px;
  font-size: .75rem;
  font-weight: 700;
  border: 1px solid rgba(249,115,22,.3);
}

/* --- MONTANT TOTAL ANNUEL (visible uniquement en mode Annuel) --- */
.annual-total {
  display: none;
  margin: 10px 0 18px;
  background: linear-gradient(135deg, rgba(249,115,22,.22), rgba(234,88,12,.14));
  border: 1px solid rgba(249,115,22,.45);
  border-radius: 12px;
  padding: 12px 16px;
  text-align: center;
}
.annual-total .at-label {
  font-size: .7rem;
  font-weight: 700;
  color: rgba(255,255,255,.55);
  text-transform: uppercase;
  letter-spacing: .8px;
  margin-bottom: 5px;
}
.annual-total .at-amount {
  font-size: 1.3rem;
  font-weight: 900;
  color: #fb923c;
  line-height: 1;
}
.annual-total .at-suffix {
  font-size: .74rem;
  color: rgba(255,255,255,.45);
  margin-top: 4px;
}
/* Adaptation carte featured (fond blanc) */
.plan-card.featured .annual-total {
  background: linear-gradient(135deg, rgba(249,115,22,.1), rgba(234,88,12,.07));
  border-color: rgba(249,115,22,.35);
}
.plan-card.featured .annual-total .at-label  { color: #94a3b8; }
.plan-card.featured .annual-total .at-amount { color: #ea580c; }
.plan-card.featured .annual-total .at-suffix  { color: #94a3b8; }
.toggle-sw { position: relative; display: inline-block; width: 48px; height: 24px; cursor: pointer; }
.toggle-sw input { opacity: 0; width: 0; height: 0; }
.toggle-track {
  position: absolute; inset: 0;
  background: rgba(255,255,255,.2);
  border-radius: 50px;
  transition: .3s;
}
.toggle-track::before {
  content: '';
  position: absolute;
  width: 18px; height: 18px;
  background: #fff; border-radius: 50%;
  left: 3px; top: 3px;
  transition: .3s;
}
.toggle-sw input:checked + .toggle-track { background: #f97316; }
.toggle-sw input:checked + .toggle-track::before { transform: translateX(24px); }

/* --- SECTION 6 : SÉCURITÉ --- */
.security-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 60px;
  align-items: start;
}
.sec-point {
  display: flex;
  align-items: flex-start;
  gap: 16px;
  margin-bottom: 24px;
}
.sec-icon {
  width: 44px;
  height: 44px;
  min-width: 44px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
}
.sec-point h6 { font-size: .9rem; font-weight: 700; color: #0f172a; margin-bottom: 4px; }
.sec-point p { color: #64748b; font-size: .85rem; line-height: 1.6; }
.tech-chips {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 8px;
}
.chip {
  background: rgba(15,76,129,.1);
  color: #0f4c81;
  padding: 6px 14px;
  border-radius: 50px;
  font-size: .78rem;
  font-weight: 700;
  border: 1px solid rgba(15,76,129,.15);
}

/* --- SECTION 7 : FAQ --- */
.faq-list { max-width: 780px; margin: 0 auto; display: flex; flex-direction: column; gap: 12px; }
.faq-item {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  overflow: hidden;
  transition: all .3s;
}
.faq-item.open { border-color: #0f4c81; box-shadow: 0 4px 20px rgba(15,76,129,.1); }
.faq-trigger {
  width: 100%;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  background: none;
  border: none;
  cursor: pointer;
  gap: 16px;
  text-align: left;
}
.faq-trigger span { font-weight: 600; font-size: .95rem; color: #0f172a; line-height: 1.5; flex: 1; }
.faq-icon {
  width: 32px;
  height: 32px;
  min-width: 32px;
  background: rgba(15,76,129,.1);
  color: #0f4c81;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: .8rem;
  transition: all .3s;
}
.faq-item.open .faq-icon {
  background: #0f4c81;
  color: #fff;
  transform: rotate(45deg);
}
.faq-answer {
  max-height: 0;
  overflow: hidden;
  padding: 0 24px;
  color: #64748b;
  font-size: .9rem;
  line-height: 1.7;
  transition: all .4s ease;
}
.faq-item.open .faq-answer {
  max-height: 400px;
  padding: 0 24px 20px;
}

/* --- CTA FINAL --- */
.cta-section {
  background: linear-gradient(135deg, #0f4c81 0%, #1e6fb8 100%);
  padding: 80px 20px;
  text-align: center;
}
.cta-inner {
  max-width: 680px;
  margin: 0 auto;
}

/* ================================================================
   RESPONSIVE — TABLETTE (max 991px)
   ================================================================ */
@media (max-width: 991px) {
  .section { padding: 60px 16px; }
  .section-header { margin-bottom: 40px; }

  /* Hero */
  .hero { padding: 40px 16px 50px; }
  .hero-inner {
    grid-template-columns: 1fr;
    gap: 40px;
    text-align: center;
  }
  .hero-badge { justify-content: center; }
  .hero-btns { justify-content: center; }
  .hero-trust { justify-content: center; }
  .hero-stats { grid-template-columns: repeat(2, 1fr); gap: 12px; }
  .hero-img-wrap { order: -1; max-width: 500px; margin: 0 auto; }
  .hero-float-badge { left: 0; bottom: -15px; }

  /* Grilles */
  .grid-3 { grid-template-columns: 1fr 1fr; }
  .grid-4 { grid-template-columns: 1fr 1fr; }
  .feature-grid { grid-template-columns: 1fr; gap: 40px; }
  .security-grid { grid-template-columns: 1fr; gap: 40px; }
  .pricing-grid { grid-template-columns: 1fr 1fr; }
}

/* ================================================================
   RESPONSIVE — MOBILE (max 640px)
   ================================================================ */
@media (max-width: 640px) {
  .section { padding: 48px 14px; }
  .section-header { margin-bottom: 32px; }

  /* Hero */
  .hero { padding: 32px 14px 44px; }
  .hero-inner { gap: 28px; }
  .hero-h1 { font-size: clamp(1.5rem, 7vw, 2rem); }
  .hero-p { font-size: .9rem; }
  .hero-badge { font-size: .75rem; padding: 6px 12px; }
  .hero-stats { grid-template-columns: repeat(2, 1fr); padding: 14px; gap: 10px; }
  .hero-stat .num { font-size: 1.4rem; }
  .hero-float-badge { position: static; margin-top: 16px; }

  /* Boutons CTA hero */
  .hero-btns { flex-direction: column; align-items: stretch; }
  .hero-btns .btn-primary-rhx,
  .hero-btns .btn-white { justify-content: center; }

  /* Trust bar : wrap sur mobile */
  .trust-bar-inner { gap: 14px; }
  .trust-item { font-size: .8rem; }

  /* Grilles : 1 colonne */
  .grid-3 { grid-template-columns: 1fr; gap: 20px; }
  .grid-4 { grid-template-columns: 1fr; gap: 16px; }
  .pricing-grid { grid-template-columns: 1fr; gap: 20px; }

  /* Piliers */
  .pilier-card { padding: 20px; }

  /* Sécurité */
  .sec-point { gap: 12px; }

  /* FAQ */
  .faq-trigger { padding: 16px 18px; }
  .faq-trigger span { font-size: .88rem; }
  .faq-answer { padding: 0 18px; }
  .faq-item.open .faq-answer { padding: 0 18px 16px; }

  /* CTA */
  .cta-section { padding: 56px 14px; }

  /* Pricing */
  .plan-card { padding: 24px 20px; }
  .plan-card.featured { margin-top: 16px; }

  /* Feature grid */
  .feature-grid { gap: 28px; }
  .step { gap: 12px; }
  .step-num { width: 32px; height: 32px; min-width: 32px; font-size: .8rem; }

  /* Boutons généraux */
  .btn-primary-rhx,
  .btn-outline-rhx,
  .btn-white { padding: 12px 22px; font-size: .88rem; }
}

/* ================================================================
   RESPONSIVE — TRÈS PETIT MOBILE (max 380px)
   ================================================================ */
@media (max-width: 380px) {
  .hero-stats { grid-template-columns: 1fr 1fr; padding: 10px; }
  .hero-trust { flex-direction: column; align-items: flex-start; }
  .trust-bar-inner { justify-content: flex-start; gap: 12px; }
  .billing-toggle { flex-wrap: wrap; gap: 8px; }
}

/* Fix global overflow horizontal */
body { overflow-x: hidden; }
* { max-width: 100%; box-sizing: border-box; }
img { max-width: 100%; height: auto; }
/* --- SECTOR GRID --- */
.sector-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 20px;
  margin-top: 40px;
}
.sector-card {
  text-align: center;
  padding: 24px 16px;
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  transition: all 0.3s;
}
.sector-card:hover {
  transform: translateY(-5px);
  border-color: #0f4c81;
  box-shadow: 0 10px 30px rgba(15,76,129,0.1);
}
.sector-icon {
  width: 50px;
  height: 50px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.4rem;
  margin: 0 auto 14px;
}
.sector-card h6 {
  font-size: 0.9rem;
  font-weight: 700;
  color: #0f172a;
  line-height: 1.3;
}
@media (max-width: 991px) {
  .sector-grid { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 640px) {
  .sector-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
  .sector-card { padding: 16px 10px; }
  .sector-card h6 { font-size: 0.8rem; }
}
</style>

<div class="landing-page-wrapper">
<!-- HERO -->
<section class="hero" id="hero">
  <div class="hero-inner">
    <div class="hero-text" data-aos="fade-right">
      <div class="hero-badge"><i class="fa fa-shield-halved"></i> Zéro Matériel — GPS Certifié</div>
      <h1 class="hero-h1">RHXtimes : <span>Le Pointage Qui Ne Ment Pas</span></h1>
      <p class="hero-p">Mettez fin aux heures fantômes, aux retards camouflés et aux registres papier. Solution <strong>100% Africaine</strong>, zéro matériel, zéro fraude, contrôle total, multi-sites.</p>
      <div class="hero-btns">
        <a href="index.php?page=register" class="btn-primary-rhx"><i class="fa fa-rocket"></i> Essai Gratuit 14 Jours</a>
        <a href="#pricing-grid" class="btn-white"><i class="fa fa-tag"></i> Voir les Tarifs</a>
      </div>
      <div class="hero-trust">
        <div class="hero-trust-item"><i class="fa fa-check-circle"></i> Sans carte bancaire</div>
        <div class="hero-trust-item"><i class="fa fa-check-circle"></i> Orange Money &amp; MTN MoMo</div>
        <div class="hero-trust-item"><i class="fa fa-check-circle"></i> Mode hors-ligne (PWA)</div>
      </div>
      <div class="hero-stats">
        <div class="hero-stat"><div class="num">-20<span>%</span></div><p>Absentéisme réduit</p></div>
        <div class="hero-stat"><div class="num">2<span>j</span></div><p>Gagnés/mois sur la paie</p></div>
        <div class="hero-stat"><div class="num">3<span>s</span></div><p>Pour pointer</p></div>
        <div class="hero-stat"><div class="num">0<span> FCFA</span></div><p>Matériel requis</p></div>
      </div>
    </div>
    <div class="hero-img-wrap" data-aos="fade-left">
      <img src="assets/img/rhx_certified_hero.jpg" alt="Tableau de bord RHXtimes et géolocalisation certifiée">
      <div class="hero-float-badge">
        <div class="icon"><i class="fa fa-location-dot"></i></div>
        <div><strong>Position Certifiée ✓</strong><span>GPS validé à 12m du site</span></div>
      </div>
    </div>
  </div>
</section>

<!-- TRUST BAR -->
<div class="trust-bar">
  <div class="trust-bar-inner">
    <div class="trust-item"><i class="fa fa-mobile-screen"></i> Application légère (PWA)</div>
    <div class="trust-item"><i class="fa fa-wifi-slash"></i> Fonctionne hors-ligne</div>
    <div class="trust-item"><i class="fa fa-map-location-dot"></i> Validation GPS</div>
    <div class="trust-item"><i class="fa fa-file-pdf"></i> Bulletins de paie PDF</div>
    <div class="trust-item"><i class="fa fa-mobile-retro"></i> Orange Money & MTN MoMo</div>
    <div class="trust-item"><i class="fa fa-lock"></i> Données chiffrées SSL/TLS</div>
  </div>
</div>

<!-- SECTION 2 : POURQUOI -->
<section class="section" id="pourquoi" style="background:#f8fafc;">
  <div class="section-inner">
    <div class="section-header">
      <span class="section-label">Le Coin des Sceptiques</span>
      <h2 class="section-h">Vous avez des doutes ? <span class="text-accent">Tant mieux.</span></h2>
      <p class="section-p" style="max-width:600px;margin:12px auto 0;">Voici les 3 vraies objections et pourquoi elles ne tiennent pas face à RHXtimes.</p>
    </div>
    <div class="grid-3">
      <div class="card-rhx skeptic-card" data-aos="fade-up" data-aos-delay="0">
        <div class="s-icon-wrap" style="background:#fff7ed;color:#ea580c;"><i class="fa fa-coins"></i></div>
        <h5>"Ça coûte trop cher"</h5>
        <p>Une badgeuse physique = 150 000 à 400 000 FCFA <em>par site</em> + maintenance + frais internet mensuel. RHXtimes : <strong>zéro investissement matériel</strong>. Vos employés ont déjà leur téléphone.</p>
        <div class="verdict-tag"><i class="fa fa-check-circle"></i> Amorti dès le 2e mois</div>
      </div>
      <div class="card-rhx skeptic-card" data-aos="fade-up" data-aos-delay="100">
        <div class="s-icon-wrap" style="background:#eef2ff;color:#4f46e5;"><i class="fa fa-user-ninja"></i></div>
        <h5>"On peut contourner le système"</h5>
        <p>Impossible. <strong>Double verrou : QR Code + notre Algorithme GPS</strong>. Photo du code envoyée à un collègue ? Refusée — le GPS doit confirmer la présence physique sur site. Compte lié à l'empreinte du téléphone.</p>
        <div class="verdict-tag"><i class="fa fa-check-circle"></i> Fraude techniquement impossible</div>
      </div>
      <div class="card-rhx skeptic-card" data-aos="fade-up" data-aos-delay="200">
        <div class="s-icon-wrap" style="background:#f0fdf4;color:#16a34a;"><i class="fa fa-file-lines"></i></div>
        <h5>"Le papier, c'est plus simple"</h5>
        <p>Un registre papier coûte des <strong>millions en heures fantômes</strong> et est falsifiable. RHXtimes génère un audit numérique <em>inaltérable</em> : horodatage serveur + GPS. Valeur juridique supérieure.</p>
        <div class="verdict-tag"><i class="fa fa-check-circle"></i> Preuve recevable au tribunal</div>
      </div>
    </div>
  </div>
</section>
 
<!-- SECTION SECTEURS D'ACTIVITÉ -->
<section class="section" id="secteurs">
  <div class="section-inner">
    <div class="section-header" data-aos="fade-up">
      <span class="section-label">Universalité de la solution</span>
      <h2 class="section-h">Une solution taillée pour <span class="text-accent">chaque industrie</span></h2>
      <p class="section-p" style="max-width:700px;margin:12px auto 0;">Que vous soyez sur un chantier, dans un magasin ou en déplacement, RHXtimes s'adapte aux contraintes de votre terrain pour certifier la présence de vos équipes.</p>
    </div>
    <div class="sector-grid">
      <div class="sector-card" data-aos="zoom-in" data-aos-delay="0">
        <div class="sector-icon" style="background:#fff7ed;color:#ea580c;"><i class="fa fa-trowel-bricks"></i></div>
        <h6>BTP et Infrastructures</h6>
      </div>
      <div class="sector-card" data-aos="zoom-in" data-aos-delay="50">
        <div class="sector-icon" style="background:#e0f2fe;color:#0369a1;"><i class="fa fa-truck-fast"></i></div>
        <h6>Logistique et Transport</h6>
      </div>
      <div class="sector-card" data-aos="zoom-in" data-aos-delay="100">
        <div class="sector-icon" style="background:#fef2f2;color:#ef4444;"><i class="fa fa-heart-pulse"></i></div>
        <h6>Santé et Bien-être</h6>
      </div>
      <div class="sector-card" data-aos="zoom-in" data-aos-delay="150">
        <div class="sector-icon" style="background:#f0fdf4;color:#16a34a;"><i class="fa fa-cart-shopping"></i></div>
        <h6>Commerce</h6>
      </div>
      <div class="sector-card" data-aos="zoom-in" data-aos-delay="200">
        <div class="sector-icon" style="background:#eef2ff;color:#4f46e5;"><i class="fa fa-hotel"></i></div>
        <h6>Hôtellerie</h6>
      </div>
      <div class="sector-card" data-aos="zoom-in" data-aos-delay="250">
        <div class="sector-icon" style="background:#f8fafc;color:#475569;"><i class="fa fa-industry"></i></div>
        <h6>Industrie et Agriculture</h6>
      </div>
      <div class="sector-card" data-aos="zoom-in" data-aos-delay="300">
        <div class="sector-icon" style="background:#ecfeff;color:#0891b2;"><i class="fa fa-graduation-cap"></i></div>
        <h6>Éducation et Services Publics</h6>
      </div>
      <div class="sector-card" data-aos="zoom-in" data-aos-delay="350">
        <div class="sector-icon" style="background:#f0fdfa;color:#0d9488;"><i class="fa fa-hand-sparkles"></i></div>
        <h6>Services de Proximité</h6>
      </div>
      <div class="sector-card" data-aos="zoom-in" data-aos-delay="400">
        <div class="sector-icon" style="background:#fffbeb;color:#d97706;"><i class="fa fa-building-columns"></i></div>
        <h6>Finance et Services Pro</h6>
      </div>
      <div class="sector-card" data-aos="zoom-in" data-aos-delay="450">
        <div class="sector-icon" style="background:#faf5ff;color:#9333ea;"><i class="fa fa-bullhorn"></i></div>
        <h6>Communication et Médias</h6>
      </div>
    </div>
  </div>
</section>

<!-- SECTION 3 : COMMENT ÇA MARCHE -->
<section class="section" id="comment">
  <div class="section-inner">
    <div class="feature-grid">
      <div data-aos="fade-right">
        <img src="assets/img/rhx_innovation_zero.jpg" alt="Innovation RHXtimes Zéro Matériel" style="width:100%;border-radius:20px;box-shadow:0 20px 50px rgba(0,0,0,.12);" loading="lazy" decoding="async">
      </div>
      <div data-aos="fade-left">
        <span class="section-label">Innovation Zéro Matériel, zéro investissement</span>
        <h2 class="section-h" style="margin-bottom:12px;">Comment ça marche</h2>
        <p class="section-p" style="margin-bottom:32px;">Vos équipes pointent depuis leur smartphone habituel via deux méthodes au choix défini par vous : soit par <strong>QR Code</strong> et soit par <strong>One-Tap</strong>. Aucun boîtier, aucune installation, aucun technicien.</p>
        <div class="steps-list">
          <div class="step">
            <div class="step-num">1</div>
            <div>
              <h6>Pointage par QR Code</h6>
              <p>Générez et imprimez votre QR Code unique sur A4 pour chacun de vos sites afin de permettre à vos employés de scanner avec leur Smartphone. Notre Algorithme GPS certifie alors leur présence dans un rayon défini, rendant tout contournement impossible.</p>
            </div>
          </div>
          <div class="step">
            <div class="step-num">2</div>
            <div>
              <h6>Pointage par One-Tap</h6>
              <p>Vos employés appuient sur un bouton unique dans l'application à travers leur Smartphone et notre Algorithme GPS valide la présence instantanément.</p>
            </div>
          </div>
          <div class="step">
            <div class="step-num">3</div>
            <div>
              <h6><i class="fa-solid fa-cloud-slash" style="margin-right:8px;color:#f97316;"></i>Mode Hors-Ligne Intelligent (PWA)</h6>
              <p>Pas de réseau ? Le pointage est stocké localement et synchronisé automatiquement à la reconnexion. Aucune perte de données, même en zone 3G blanche.</p>
            </div>
          </div>
        </div>
        <div style="margin-top:28px;">
          <a href="index.php?page=register" class="btn-primary-rhx"><i class="fa fa-rocket"></i> Essai Gratuit 14 Jours</a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- SECTION 4 : LES 4 PILIERS -->
<section class="section" style="background:#f8fafc;">
  <div class="section-inner">
    <div class="section-header">
      <span class="section-label">Optimisation Stratégique</span>
      <h2 class="section-h">Les 4 Piliers de votre <span class="text-accent">Rentabilité</span></h2>
      <p class="section-p" style="max-width:600px;margin:12px auto 0;">Chaque pilier génère un retour sur investissement mesurable dès le premier mois.</p>
    </div>
    <div class="grid-4">
      <div class="pilier-card" data-aos="fade-up" data-aos-delay="0">
        <div class="p-icon" style="background:#dbeafe;color:#1d4ed8;"><i class="fa fa-map-location-dot"></i></div>
        <h5>Sécurité GPS Certifiée</h5>
        <p>Notre Algorithme  + Périmètre défini = Zéro fraude = Présence physique certifiée.</p>
        <div class="roi-badge">-15 à -20% absentéisme</div>
      </div>
      <div class="pilier-card" data-aos="fade-up" data-aos-delay="100">
        <div class="p-icon" style="background:#fef9c3;color:#a16207;"><i class="fa fa-file-invoice-dollar"></i></div>
        <h5>Automatisation Paie</h5>
        <p>Bulletins PDF automatiques. Conformité OHADA. Export en 1 clic. Zéro erreur manuelle.</p>
        <div class="roi-badge">2 jours gagnés / mois</div>
      </div>
      <div class="pilier-card" data-aos="fade-up" data-aos-delay="200">
        <div class="p-icon" style="background:#dcfce7;color:#15803d;"><i class="fa fa-building-user"></i></div>
        <h5>Gestion Multi-Sites</h5>
        <p>Supervisez plus de 20 sites depuis un tableau de bord unique. Visibilité totale en temps réel, sans déplacement.</p>
        <div class="roi-badge">Visibilité totale</div>
      </div>
      <div class="pilier-card" data-aos="fade-up" data-aos-delay="300">
        <div class="p-icon" style="background:#fce7f3;color:#be185d;"><i class="fa fa-chart-line"></i></div>
        <h5>Retour sur investissement</h5>
        <p>Solution amortie dès le 2e mois. Coût RH réduit de 40% en moyenne. Mesurable.</p>
        <div class="roi-badge">Amorti en 2 mois</div>
      </div>
    </div>
  </div>
</section>

<!-- SECTION 5 : TARIFICATION -->
<section class="pricing-section" id="pricing-grid">
  <div style="max-width:1200px;margin:0 auto;">
    <div class="section-header">
      <span class="section-label" style="background:rgba(249,115,22,.2);color:#fdba74;">Tarification Transparente</span>
      <h2 class="section-h" style="color:#fff;">Pay-as-you-grow</h2>
      <p class="section-p" style="color:rgba(255,255,255,.7);max-width:560px;margin:12px auto 0;">Ne payez que ce que vous utilisez. Sans engagement. Changez de formule à tout moment.</p>
    </div>
    <div class="billing-toggle">
      <span class="billing-label active" id="lbl-m" style="cursor:pointer;">Mensuel</span>
      <label class="toggle-sw"><input type="checkbox" id="billingTgl"><div class="toggle-track"></div></label>
      <span class="billing-label" id="lbl-a" style="cursor:pointer;">Annuel <span class="saving-tag"><?php if($maxGlobalDisc > 0): ?>-<?= $maxGlobalDisc ?>%<?php else: ?>-20%<?php endif; ?></span></span>
    </div>
    <div class="pricing-grid">
      <?php if(empty($plans)): ?>
        <p style="color:rgba(255,255,255,.7);text-align:center;grid-column:1/-1;">Aucun forfait disponible. <a href="index.php?page=register" style="color:var(--accent);">Contactez-nous.</a></p>
      <?php else: $idx=0; foreach($plans as $p):
        $pm=(float)($p['montant_mensuel']??0);
        $pa=(float)($p['montant_annuel_mensualise']??0);
        $disc=($pm>0&&$pa>0)?round((1-$pa/$pm)*100):0;
        $feat=($idx===1); $idx++;
      ?>
      <div class="plan-card <?= $feat?'featured':'' ?>" data-pm="<?= $pm ?>" data-pa="<?= $pa ?>">
        <?php if($feat): ?><div class="featured-tag">⭐ Le Plus Populaire</div><?php endif; ?>
        <div class="plan-name"><?= htmlspecialchars($p['nom']) ?></div>
        <div class="plan-sub"><?= $pm>0?'Pour les équipes en croissance':'Pour démarrer gratuitement' ?></div>
        <div class="plan-price plan-price-val">
          <?php if($pm>0): ?><span class="pv"><?= number_format($pm,0,',',' ') ?></span><span style="font-size:1rem;font-weight:500;"> FCFA</span>
          <?php else: ?>Gratuit<?php endif; ?>
        </div>
        <div class="plan-period plan-period-val"><?= $pm>0?'/ Mois':'À vie' ?></div>
        <?php if($disc>0): ?>
        <div class="saving-pill" style="display:none;margin-bottom:8px;"><span class="saving-tag">Économisez <?= number_format(($pm-$pa)*12,0,',',' ') ?> FCFA/an</span></div>
        <?php endif; ?>
        <?php if($pm>0 && $pa>0): ?>
        <div class="annual-total">
          <div class="at-label">💳 Total facturé annuellement</div>
          <div class="at-amount"><span class="at-val"></span>&nbsp;FCFA</div>
          <div class="at-suffix">soit <?= number_format($pa,0,',',' ') ?>&nbsp;FCFA/mois — au lieu de <?= number_format($pm,0,',',' ') ?>&nbsp;FCFA</div>
        </div>
        <?php endif; ?>
        <ul class="plan-feats">
          <li class="plan-feat-item"><i class="fa fa-check"></i> <?= $p['max_employees'] ?> employés max</li>
          <li class="plan-feat-item"><i class="fa fa-check"></i> <?= $p['max_sites'] ?> sites inclus</li>
          <li class="plan-feat-item"><i class="fa fa-check"></i> <?= $p['max_managers'] ?> managers / RH inclus</li>
          
          <?php foreach($featureMap as $col => $label): 
            $included = !empty($p[$col]);
          ?>
            <li class="plan-feat-item <?= $included ? '' : 'not-included' ?>" style="<?= $included ? '' : 'opacity: 0.45; color: #64748b;' ?>">
              <?php if($included): ?>
                <i class="fa fa-check" style="color: #10b981;"></i>
              <?php else: ?>
                <i class="fa fa-xmark" style="color: #ef4444; width: 14px; text-align: center;"></i>
              <?php endif; ?>
              <?= $label ?>
            </li>
          <?php endforeach; ?>

          <li class="plan-feat-item"><i class="fa fa-check"></i> Support <?= ($p['support_type']??'')==='prioritaire'?'Prioritaire':'Standard' ?></li>
          <li class="plan-feat-item"><i class="fa fa-check"></i> Mode hors-ligne (PWA)</li>
        </ul>
        <?php if(isset($_SESSION['user_role'])&&$_SESSION['user_role']==='owner'): ?>
          <form action="index.php?page=subscription_init" method="POST">
            <input type="hidden" name="plan_id" value="<?= $p['id'] ?>">
            <input type="hidden" name="billing_cycle" class="cycle-inp" value="mensuel">
            <button type="submit" class="<?= $feat?'btn-primary-rhx':'btn-outline-rhx' ?>" style="width:100%;justify-content:center;">Choisir ce plan</button>
          </form>
        <?php else: ?>
          <a href="index.php?page=register&plan=<?= $p['id'] ?>" class="<?= $feat?'btn-primary-rhx':'btn-white' ?>" style="width:100%;justify-content:center;">
            <?= $pm>0?"Démarrer l'essai":"S'inscrire Gratuitement" ?>
          </a>
        <?php endif; ?>
      </div>
      <?php endforeach; endif; ?>
    </div>
    <p style="text-align:center;color:rgba(255,255,255,.55);margin-top:28px;font-size:.88rem;">
      <i class="fa fa-mobile-screen" style="color:var(--accent);margin-right:6px;"></i>
      Paiement via <strong style="color:#fff;">Orange Money</strong>, <strong style="color:#fff;">MTN MoMo</strong> ou carte bancaire — en <strong style="color:#fff;">FCFA</strong>
    </p>
  </div>
</section>

<!-- SECTION 6 : SÉCURITÉ -->
<section class="section">
  <div class="section-inner">
    <div class="security-grid">
      <div data-aos="fade-right">
        <span class="section-label">Votre Coffre-fort Numérique</span>
        <h2 class="section-h" style="margin-bottom:12px;">RHXtimes protège <span class="text-accent">l'employeur</span> et <span class="text-accent">l'employé</span></h2>
        <p class="section-p" style="margin-bottom:32px;">Ce n'est pas un outil de surveillance. C'est un tiers de confiance, transparent et impartial.</p>
        <div class="sec-point">
          <div class="sec-icon" style="background:#dbeafe;color:#1d4ed8;"><i class="fa fa-users-gear"></i></div>
          <div><h6>Isolation Multi-tenant</h6><p>Chaque entreprise est dans un silo indépendant. Techniquement impossible qu'un client voie les données d'un autre.</p></div>
        </div>
        <div class="sec-point">
          <div class="sec-icon" style="background:#dcfce7;color:#15803d;"><i class="fa fa-location-crosshairs"></i></div>
          <div><h6>GPS Micro-seconde Uniquement</h6><p>La géolocalisation s'active <strong>uniquement</strong> pendant la validation du pointage. Zéro tracking continu. Vie privée préservée.</p></div>
        </div>
        <div class="sec-point">
          <div class="sec-icon" style="background:#fef9c3;color:#a16207;"><i class="fa fa-gavel"></i></div>
          <div><h6>Valeur Juridique Certifiée</h6><p>Audit trail : horodatage serveur + coordonnées GPS + ID terminal. Recevable devant l'Inspection du Travail.</p></div>
        </div>
        <div class="sec-point">
          <div class="sec-icon" style="background:#fce7f3;color:#be185d;"><i class="fa fa-lock"></i></div>
          <div><h6>Vos Données Vous Appartiennent</h6><p>Export complet PDF/Excel à tout moment. Même si vous résiliez, vous repartez avec tout votre historique RH.</p></div>
        </div>
        <div class="sec-point">
          <div class="sec-icon" style="background:#e0e7ff;color:#4338ca;"><i class="fa-brands fa-whatsapp"></i></div>
          <div><h6>Messagerie Interne RH</h6><p>Échangez en temps réel avec vos employés. Une interface fluide et familière comme WhatsApp pour centraliser les consignes et les justificatifs.</p></div>
        </div>
      </div>
      <div data-aos="fade-left">
        <img src="assets/img/rhx_security_new.jpg" alt="RHXtimes Tiers de Confiance et Sécurité" style="width:100%;border-radius:20px;box-shadow:0 20px 50px rgba(0,0,0,.1);" loading="lazy" decoding="async" onerror="this.src='assets/img/rhx_certified_hero.jpg'">
        <div class="tech-chips" style="justify-content: center; margin-top: 24px;">
          <span class="chip">Our Algorithm GPS</span>
          <span class="chip">Device Binding</span>
          <span class="chip">Multi-tenant Isolation</span>
          <span class="chip">SSL/TLS</span>
          <span class="chip">Audit Trail</span>
          <span class="chip">PWA Offline</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- SECTION 7 : FAQ -->
<section class="section" id="faq" style="background:#f8fafc;">
  <div class="section-inner">
    <div class="section-header">
      <span class="section-label">Levée des Objections</span>
      <h2 class="section-h">Questions <span class="text-accent">Fréquentes</span></h2>
    </div>
    <div class="faq-list">
      <?php $faqs=[
        ["Qu'est-ce qui empêche un employé d'envoyer la photo du QR Code à un collègue ?","Impossible. Double verrou : même avec la photo, notre Algorithm GPS vérifie la position en temps réel. Le téléphone doit être physiquement dans le périmètre du site. De plus, chaque compte est lié à l'empreinte du téléphone (Device Binding). On ne peut pas pointer depuis le téléphone d'un autre."],
        ["Si la connexion internet est instable, comment mes employés valideront leur présence ?","RHXtimes est une PWA avec mode hors-ligne intelligent. L'employé scanne normalement. Le pointage est stocké localement et synchronisé automatiquement dès que la connexion 3G, 4G ou Wi-Fi est rétablie. Aucune donnée perdue."],
        ["Mes employés refusent d'être suivis par GPS. Comment les rassurer ?","RHXtimes n'est pas un outil de surveillance. La géolocalisation n'est activée que pendant la micro-seconde du pointage. Une fois validé, le GPS se coupe immédiatement. Aucune donnée de déplacement n'est collectée."],
        ["Pourquoi payer un abonnement alors qu'un registre papier est gratuit ?","Un registre papier coûte des millions en heures fantômes et retards camouflés. RHXtimes se rentabilise dès le 1er mois : -15 à -20% d'absentéisme + 2 jours admin gagnés chaque mois sur la paie. C'est un investissement, pas une dépense."],
        ["L'application fonctionne-t-elle sur de petits téléphones Android bas de gamme ?","Oui. RHXtimes est ultra-légère, optimisée pour les smartphones d'entrée de gamme. Pas d'installation Play Store. S'ouvre dans le navigateur web. Si le téléphone a une caméra et internet (même 3G), RHXtimes fonctionne."],
        ["Je n'ai pas de carte bancaire internationale. Comment payer ?","RHXtimes est conçu pour l'Afrique. Payez en FCFA via Orange Money, MTN MoMo, ou carte bancaire locale (CinetPay). Aucune banque internationale requise."],
        ["Si j'arrête l'abonnement, est-ce que je perds mon historique de pointage ?","Jamais. Vos données vous appartiennent. Exportez l'intégralité de vos rapports en PDF et Excel à tout moment, même pendant l'essai gratuit."],
        ["Ces rapports numériques sont-ils valables en cas de litige au tribunal ?","Absolument. Contrairement au papier falsifiable, RHXtimes génère un audit inaltérable : horodatage serveur + GPS + ID terminal. Preuve bien plus solide devant l'Inspection du Travail et le Tribunal."],
      ]; ?>
      <?php foreach($faqs as $i=>$f): ?>
      <div class="faq-item" data-aos="fade-up" data-aos-delay="<?= $i*50 ?>">
        <button class="faq-trigger" onclick="toggleFaq(this)">
          <span><?= htmlspecialchars($f[0]) ?></span>
          <div class="faq-icon"><i class="fa fa-plus"></i></div>
        </button>
        <div class="faq-answer"><?= htmlspecialchars($f[1]) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- CTA FINAL -->
<section class="cta-section">
  <div class="cta-inner" data-aos="zoom-in">
    <span class="section-label" style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.3);">Passez à l'action</span>
    <h2 class="section-h" style="color:#fff;margin:16px 0 14px;">Arrêtez de perdre de l'argent sur des heures fantômes</h2>
    <p style="color:rgba(255,255,255,.8);font-size:1rem;margin-bottom:32px;line-height:1.7;">Rejoignez les entreprises africaines qui ont repris le contrôle. Essai gratuit 14 jours, sans carte bancaire.</p>
    <div style="display:flex;flex-wrap:wrap;gap:14px;justify-content:center;">
      <a href="index.php?page=register" class="btn-white"><i class="fa fa-rocket"></i> Essai Gratuit 14 Jours</a>
      <a href="#faq" class="btn-outline-rhx" style="border-color:rgba(255,255,255,.5);color:#fff;"><i class="fa fa-circle-question"></i> Lire la FAQ</a>
    </div>
  </div>
</section>
</div><!-- /.landing-page-wrapper -->
<!-- AOS Animation Library — CDN public fiable (évite les 404 locaux) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>

<!-- BLOC 1 : AOS + FAQ — isolé pour ne pas bloquer le toggle en cas d'erreur AOS -->
<script>
try {
  if (typeof AOS !== 'undefined') {
    AOS.init({once: true, duration: 700, offset: 60});
  }
} catch(e) {}

function toggleFaq(btn) {
  var item = btn.closest('.faq-item');
  var open = item.classList.contains('open');
  document.querySelectorAll('.faq-item.open').forEach(function(i) { i.classList.remove('open'); });
  if (!open) item.classList.add('open');
}
</script>

<!-- BLOC 2 : Toggle Mensuel/Annuel — script autonome, sans dépendance AOS ni jQuery -->
<script>
(function() {
  function initBillingToggle() {
    var billingTgl = document.getElementById('billingTgl');
    var lblM       = document.getElementById('lbl-m');
    var lblA       = document.getElementById('lbl-a');

    if (!billingTgl) return; // Élément absent : ne rien faire

    var formatPrice = function(val) {
      // Formatage entier avec séparateur espace (ex: 12 900)
      return Math.round(val).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '\u00a0');
    };

    var updatePrices = function() {
      var yearly = billingTgl.checked;

      // Mise à jour des labels actifs
      if (lblM) { lblM.classList.toggle('active',  !yearly); }
      if (lblA) { lblA.classList.toggle('active',   yearly); }

      document.querySelectorAll('.plan-card[data-pm]').forEach(function(card) {
        var pm = parseFloat(card.dataset.pm) || 0;
        var pa = parseFloat(card.dataset.pa) || 0;
        if (pm <= 0) return;

        var pv    = card.querySelector('.pv');
        var pp    = card.querySelector('.plan-period-val');
        var sp    = card.querySelector('.saving-pill');
        var ci    = card.querySelector('.cycle-inp');
        var at    = card.querySelector('.annual-total');
        var atVal = card.querySelector('.at-val');

        var showAnnual = yearly && pa > 0;

        // Micro-animation
        card.style.transition = 'opacity 0.15s ease';
        card.style.opacity    = '0.65';

        setTimeout(function() {
          if (pv)    pv.textContent         = showAnnual ? formatPrice(pa)  : formatPrice(pm);
          if (pp)    pp.textContent         = showAnnual ? '/ Mois (facturé annuellement)' : '/ Mois';
          if (sp)    sp.style.display       = showAnnual ? 'block' : 'none';
          if (ci)    ci.value               = showAnnual ? 'annuel' : 'mensuel';
          // Total annuel mis en évidence
          if (at)    at.style.display       = showAnnual ? 'block' : 'none';
          if (atVal) atVal.textContent      = showAnnual ? formatPrice(pa * 12) : '';
          card.style.opacity = '1';
        }, 120);
      });
    };

    // Clics sur les textes "Mensuel" / "Annuel"
    if (lblM) lblM.addEventListener('click', function() { billingTgl.checked = false; updatePrices(); });
    if (lblA) lblA.addEventListener('click', function() { billingTgl.checked = true;  updatePrices(); });

    // Clic sur l'interrupteur physique
    billingTgl.addEventListener('change', updatePrices);

    // Initialisation : forcer Mensuel au chargement pour éviter les artefacts de cache
    billingTgl.checked = false;
    updatePrices();
  }

  // Sécurité double : DOMContentLoaded ou exécution immédiate si DOM déjà prêt
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initBillingToggle);
  } else {
    initBillingToggle();
  }
})();
</script>
