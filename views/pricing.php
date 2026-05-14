<?php
try {
    $stmtPlans = $pdo->query("SELECT * FROM plans ORDER BY montant_mensuel ASC");
    $plans = $stmtPlans->fetchAll();
} catch (Exception $e) { $plans = []; }

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
.pricing-header { text-align: center; padding: 60px 20px; }
.pricing-header h1 { font-size: 42px; font-weight: 800; color: #0f172a; margin-bottom: 20px; }
.pricing-header p { font-size: 18px; color: #64748b; max-width: 600px; margin: auto; }

.cards-container { display: flex; flex-wrap: wrap; justify-content: center; gap: 30px; padding: 0 20px 80px 20px; }
.card-price { background: #fff; border-radius: 20px; padding: 40px; width: 100%; max-width: 350px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); text-align: left; display: flex; flex-direction: column; border: 1px solid #e2e8f0; transition: all 0.3s; }
.card-price.featured { border: 2px solid #0f4c81; box-shadow: 0 20px 40px rgba(15,76,129,0.15); transform: translateY(-10px); }
.featured-badge { background: #0f4c81; color: #fff; font-size: 12px; font-weight: bold; padding: 6px 14px; border-radius: 50px; align-self: flex-start; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 0.5px; }

.plan-name { font-size: 24px; font-weight: 800; color: #0f172a; margin-bottom: 10px; }
.plan-price { font-size: 38px; font-weight: 800; color: #0f172a; margin-bottom: 5px; }
.plan-price small { font-size: 16px; font-weight: 500; color: #64748b; }
.plan-desc { color: #64748b; margin-bottom: 25px; font-size: 14px; line-height: 1.6; }

.features-list { list-style: none; padding: 0; margin: 0 0 30px 0; flex: 1; }
.features-list li { margin-bottom: 12px; font-size: 14.5px; color: #1e293b; display: flex; align-items: flex-start; gap: 10px; }
.features-list li i { margin-top: 4px; font-size: 14px; }
.features-list li.not-included { opacity: 0.5; color: #94a3b8; }

.btn-plan { width: 100%; box-sizing: border-box; text-align: center; padding: 14px; border-radius: 50px; font-weight: 700; text-decoration: none; display: inline-block; transition: all 0.2s; font-size: 15px; }
.btn-outline { border: 2px solid #0f4c81; color: #0f4c81; }
.btn-outline:hover { background: #0f4c81; color: #fff; }
.btn-primary-price { background: #0f4c81; color: #fff; border: 2px solid #0f4c81; box-shadow: 0 4px 15px rgba(15,76,129,0.3); }
.btn-primary-price:hover { background: #1e6fb8; border-color: #1e6fb8; transform: translateY(-2px); }

@media (max-width: 768px) {
    .card-price.featured { transform: none; }
}
</style>

<div class="pricing-header">
    <h1>Des tarifs simples, <span style="color:#0f4c81;">sans surprise.</span></h1>
    <p>Choisissez le forfait qui correspond à la taille de votre entreprise. Commencez avec 14 jours d'essai gratuit.</p>
</div>

<div class="cards-container">
    <?php if(empty($plans)): ?>
        <p style="text-align:center; padding: 40px; color: #64748b;">Aucun forfait configuré pour le moment.</p>
    <?php else: 
        $idx = 0;
        foreach($plans as $p):
            $pm = (float)($p['montant_mensuel'] ?? 0);
            $pa = (float)($p['montant_annuel_mensualise'] ?? 0);
            $isFeatured = ($idx === 1); 
            $idx++;
    ?>
    <div class="card-price <?= $isFeatured ? 'featured' : '' ?>">
        <?php if($isFeatured): ?>
            <div class="featured-badge"><i class="fa fa-star"></i> Le plus populaire</div>
        <?php endif; ?>
        
        <div class="plan-name"><?= htmlspecialchars($p['nom']) ?></div>
        <div class="plan-price">
            <?php if($pm > 0): ?>
                <?= number_format($pm, 0, ',', ' ') ?> <small>FCFA / mois</small>
            <?php else: ?>
                Gratuit
            <?php endif; ?>
        </div>
        
        <div class="plan-desc">
            <?= $pm > 0 ? 'Idéal pour les entreprises structurées en pleine croissance.' : 'Pour découvrir la solution avec vos premiers employés.' ?>
        </div>
        
        <ul class="features-list">
            <li><i class="fa fa-check" style="color:#10b981;"></i> <strong><?= $p['max_employees'] ?></strong> employés max</li>
            <li><i class="fa fa-check" style="color:#10b981;"></i> <strong><?= $p['max_sites'] ?></strong> sites inclus</li>
            <li><i class="fa fa-check" style="color:#10b981;"></i> <strong><?= $p['max_managers'] ?></strong> managers / RH</li>
            <li><i class="fa fa-check" style="color:#10b981;"></i> Support <strong><?= ucfirst($p['support_type'] ?? 'normal') ?></strong></li>
            
            <?php foreach($featureMap as $col => $label): 
                $included = !empty($p[$col]);
            ?>
                <li class="<?= $included ? '' : 'not-included' ?>">
                    <?php if($included): ?>
                        <i class="fa fa-check" style="color:#10b981;"></i>
                    <?php else: ?>
                        <i class="fa fa-xmark" style="color:#ef4444;"></i>
                    <?php endif; ?>
                    <?= $label ?>
                </li>
            <?php endforeach; ?>
        </ul>
        
        <a href="index.php?page=register&plan=<?= $p['id'] ?>" class="btn-plan <?= $isFeatured ? 'btn-primary-price' : 'btn-outline' ?>">
            <?= $pm > 0 ? "Démarrer l'essai" : "S'inscrire gratuitement" ?>
        </a>
    </div>
    <?php endforeach; endif; ?>
</div>


