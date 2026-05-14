<?php
// views/admin/payroll_bulletin.php
if(!isset($_SESSION['admin_id'])) { header('Location: index.php?page=login'); exit; }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bulletin de Paie - <?= Security::html($user['nom'] . ' ' . $user['prenom']) ?></title>
    <style>
        @page { size: A4; margin: 0; }
        body { font-family: 'Inter', Arial, sans-serif; color: #333; margin: 0; padding: 40px; line-height: 1.5; font-size: 13px; background: #fff; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #000; padding-bottom: 20px; margin-bottom: 30px; }
        .company-info h2 { margin: 0; font-size: 22px; color: #000; text-transform: uppercase; }
        .company-info p { margin: 2px 0; color: #666; }
        .bulletin-title { text-align: right; }
        .bulletin-title h1 { margin: 0; font-size: 24px; color: #000; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 30px; }
        .info-box { border: 1px solid #ddd; padding: 15px; border-radius: 4px; }
        .info-box h3 { margin: 0 0 10px 0; font-size: 14px; text-transform: uppercase; border-bottom: 1px solid #eee; padding-bottom: 5px; }
        .info-line { display: flex; justify-content: space-between; margin-bottom: 5px; }
        .info-label { font-weight: 600; color: #555; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th { background: #f4f4f4; text-transform: uppercase; font-size: 11px; padding: 10px; border: 1px solid #ddd; text-align: left; }
        td { padding: 10px; border: 1px solid #ddd; }
        .text-right { text-align: right; }
        .total-row { background: #f9f9f9; font-weight: bold; }
        .net-box { float: right; width: 250px; background: #000; color: #fff; padding: 20px; text-align: center; border-radius: 4px; margin-top: 20px; }
        .net-amount { font-size: 24px; font-weight: 800; display: block; margin-top: 5px; }
        
        .footer { position: fixed; bottom: 40px; width: calc(100% - 80px); font-size: 10px; color: #999; text-align: center; border-top: 1px solid #eee; padding-top: 10px; }
        
        @media print {
            .no-print { display: none; }
            body { padding: 20px; }
            .net-box { border: 1px solid #000; }
        }
    </style>
</head>
<body>



<div class="header">
    <div class="company-info">
        <h2 style="color:var(--color-primary); margin-bottom:10px;"><?= Security::html($entreprise['nom']) ?></h2>
        <p><strong>Entreprise :</strong> <?= Security::html($entreprise['nom']) ?></p>
        <p><strong>Localisation :</strong> <?= Security::html($entreprise['ville'] ?? '') . ' - ' . Security::html($entreprise['localisation'] ?? '') ?></p>
        <p><strong>Email :</strong> <?= Security::html($entreprise['email']) ?></p>
        <p><strong>Contact :</strong> Tél : <?= Security::html($entreprise['telephone'] ?? 'N/A') ?></p>
    </div>
    <div class="bulletin-title">
        <h1>BULLETIN DE PAIE</h1>
        <p>Période : <strong><?= date('M Y', mktime(0,0,0,$month,1,$year)) ?></strong></p>
    </div>
</div>

<div class="info-grid">
    <div class="info-box">
        <h3>Employé</h3>
        <div class="info-line"><span class="info-label">Nom :</span> <span><?= Security::html($user['nom'] . ' ' . $user['prenom']) ?></span></div>
        <div class="info-line"><span class="info-label">Fonction :</span> <span><?= Security::html($user['fonction'] ?? 'Personnel') ?></span></div>
        <div class="info-line"><span class="info-label">Site :</span> <span><?= Security::html($user['nom_lieu'] ?? 'Non affecté') ?></span></div>
        <div class="info-line"><span class="info-label">Matricule :</span> <span>RHX-<?= str_pad($user['id'], 4, '0', STR_PAD_LEFT) ?></span></div>
    </div>
    <div class="info-box">
        <h3>Détails Période</h3>
        <div class="info-line"><span class="info-label">Heures Théoriques :</span> <span><?= number_format($theoreticalHours, 1) ?> h</span></div>
        <div class="info-line"><span class="info-label">Retards Cumulés :</span> <span style="color: red;"><?= number_format($totalDelay / 3600, 2) ?> h</span></div>
        <div class="info-line"><span class="info-label">Taux Horaire :</span> <span><?= number_format($tauxHoraire, 2) ?> FCFA</span></div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>Désignation</th>
            <th class="text-right">Nombre / Base</th>
            <th class="text-right">Taux</th>
            <th class="text-right">Gain (FCFA)</th>
            <th class="text-right">Retenue (FCFA)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Salaire de base mensuel</td>
            <td class="text-right">1.00</td>
            <td class="text-right"><?= number_format($user['salaire_mensuel'], 0, ',', ' ') ?></td>
            <td class="text-right"><?= number_format($user['salaire_mensuel'], 0, ',', ' ') ?></td>
            <td class="text-right"></td>
        </tr>
        <tr>
            <td>Absence / Retards (Déduction)</td>
            <td class="text-right"><?= number_format($totalDelay / 3600, 2) ?> h</td>
            <td class="text-right"><?= number_format($tauxHoraire, 2) ?></td>
            <td class="text-right"></td>
            <td class="text-right"><?= number_format($retenue, 0, ',', ' ') ?></td>
        </tr>
        
        <?php if($adjustment['amount_primes'] > 0): ?>
        <tr>
            <td>Primes et Indemnités diverses</td>
            <td class="text-right">1.00</td>
            <td class="text-right"><?= number_format($adjustment['amount_primes'], 0, ',', ' ') ?></td>
            <td class="text-right"><?= number_format($adjustment['amount_primes'], 0, ',', ' ') ?></td>
            <td class="text-right"></td>
        </tr>
        <?php endif; ?>

        <?php if($adjustment['amount_retenues'] > 0): ?>
        <tr>
            <td>Autres Retenues / Déductions</td>
            <td class="text-right">1.00</td>
            <td class="text-right"><?= number_format($adjustment['amount_retenues'], 0, ',', ' ') ?></td>
            <td class="text-right"></td>
            <td class="text-right"><?= number_format($adjustment['amount_retenues'], 0, ',', ' ') ?></td>
        </tr>
        <?php endif; ?>

        <?php if(!empty($adjustment['description'])): ?>
        <tr style="background:#f9fafb;">
            <td colspan="5" style="font-size:10px; color:#64748b; font-style:italic; padding:5px 15px;">
                Note : <?= Security::html($adjustment['description']) ?>
            </td>
        </tr>
        <?php endif; ?>
    </tbody>
    <tfoot>
        <tr class="total-row">
            <td colspan="3">TOTAUX</td>
            <td class="text-right"><?= number_format($user['salaire_mensuel'] + $adjustment['amount_primes'], 0, ',', ' ') ?></td>
            <td class="text-right"><?= number_format($retenue + $adjustment['amount_retenues'], 0, ',', ' ') ?></td>
        </tr>
    </tfoot>
</table>

<div style="overflow: hidden; margin-top: 40px;">
    <div style="float: left; width: 330px; border: 1px solid #f1f5f9; padding: 15px; border-radius: 8px; background: #f8fafc;">
        <p style="font-weight: 700; color: #64748b; margin-top:0; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Authenticité du document</p>
        <p style="margin: 10px 0 0 0; font-size: 14px; font-weight: 800; color: #1e293b;">
            ID Unique : <span style="font-family: monospace; color: var(--color-primary);">RHX-<?= str_pad($user['id'], 3, '0', STR_PAD_LEFT) ?>-<?= sprintf('%02d%s', $month, substr($year, 2)) ?>-<?= strtoupper(substr(md5($user['id'] . $month . $year . 'RHXS'), 0, 6)) ?></span>
        </p>
        <p style="font-size: 10px; color: #94a3b8; margin: 5px 0 0 0;">Ce bulletin est certifié numériquement par RHXtimes.</p>
    </div>
    
    <div class="net-box">
        <span style="text-transform: uppercase; font-size: 11px; letter-spacing: 1px;">Net à payer</span>
        <span class="net-amount"><?= number_format($netToPay, 0, ',', ' ') ?> FCFA</span>
    </div>
</div>

<div class="footer">
    Bulletin de paie numérique généré par RHXtimes - Logiciel Marvens Group Services - Date d'édition : <?= date('d/m/Y H:i') ?>
</div>

</body>
</html>
