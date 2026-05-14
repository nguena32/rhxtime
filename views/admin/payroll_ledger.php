<?php
// views/admin/payroll_ledger.php
if(!isset($_SESSION['admin_id'])) { header('Location: index.php?page=login'); exit; }

$months = [
    1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
    5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
    9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
];

// Calcul des totaux globaux
$totalBase = 0;
$totalPrimes = 0;
$totalRetards = 0;
$totalRetenuesMan = 0;
$totalNet = 0;

foreach($report as $r) {
    $totalBase += $r['user']['salaire_mensuel'];
    $totalPrimes += $r['primes'];
    $totalRetards += $r['retenue_retard'];
    $totalRetenuesMan += $r['retenues_manuelles'];
    $totalNet += $r['net_a_payer'];
}
?>

<div class="no-print">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px;">
        <div>
            <h2 style="font-weight: 800; color: var(--color-secondary); margin:0;">Livre de Paie</h2>
            <p style="margin:0; font-size:12px; color:#64748b;">Rapport mensuel consolidé de la masse salariale.</p>
        </div>
        <button onclick="window.print()" class="btn btn-primary" style="border-radius:12px; padding:10px 20px;">
            <i class="fa-solid fa-file-pdf"></i> Exporter en PDF / Imprimer
        </button>
    </div>

    <!-- Filtres -->
    <div class="card" style="border-radius:15px; padding:20px; background:#fff; margin-bottom:30px;">
        <form method="GET" action="index.php" style="display:flex; gap:15px; align-items:flex-end;">
            <input type="hidden" name="page" value="admin_payroll_ledger">
            <div style="flex:1;">
                <label style="font-size:11px; font-weight:800; color:#64748b; text-transform:uppercase; margin-bottom:5px; display:block;">Mois</label>
                <select name="month" class="form-control" style="width:100%; border-radius:10px;">
                    <?php foreach($months as $num => $name): ?>
                        <option value="<?= $num ?>" <?= $month == $num ? 'selected' : '' ?>><?= $name ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="flex:1;">
                <label style="font-size:11px; font-weight:800; color:#64748b; text-transform:uppercase; margin-bottom:5px; display:block;">Année</label>
                <select name="year" class="form-control" style="width:100%; border-radius:10px;">
                    <?php for($y=date('Y'); $y>=2024; $y--): ?>
                        <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-secondary" style="border-radius:10px; padding:10px 25px;">Appliquer</button>
        </form>
    </div>
</div>

<!-- LE LIVRE DE PAIE (VERSION IMPRIMABLE) -->
<div class="printable-ledger-container" style="background:#fff; border-radius:12px;">
    
    <!-- En-tête du document (visible à l'impression) -->
    <div class="print-only-header" style="display:none; margin-bottom:30px; border-bottom:2px solid #000; padding-bottom:15px;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start;">
            <div>
                <h1 style="margin:0; font-size:24px; text-transform:uppercase;"><?= Security::html($entreprise['nom']) ?></h1>
                <p style="margin:5px 0; font-size:12px;"><?= Security::html($entreprise['ville']) ?> - <?= Security::html($entreprise['localisation']) ?></p>
                <p style="margin:0; font-size:12px;">Tél: <?= Security::html($entreprise['telephone']) ?></p>
            </div>
            <div style="text-align:right;">
                <h2 style="margin:0; font-size:20px; color:#333;">LIVRE DE PAIE</h2>
                <p style="margin:5px 0; font-weight:700;">Période : <?= $months[$month] ?> <?= $year ?></p>
                <p style="margin:0; font-size:10px; color:#666;">Date d'édition : <?= date('d/m/Y H:i') ?></p>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="ledger-table" style="width:100%; border-collapse:collapse; font-size:12px;">
            <thead>
                <tr style="background:#f8fafc; border-bottom:2px solid #e2e8f0;">
                    <th style="padding:12px; text-align:left;">NOM & PRÉNOMS</th>
                    <th style="padding:12px; text-align:right;">SALAIRE BASE</th>
                    <th style="padding:12px; text-align:right;">PRIMES/INDEMN.</th>
                    <th style="padding:12px; text-align:right;">RETARDS (-)</th>
                    <th style="padding:12px; text-align:right;">AUTRES RET. (-)</th>
                    <th style="padding:12px; text-align:right; font-weight:800; background:#f1f5f9;">NET À PAYER</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($report as $r): ?>
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:12px; font-weight:600; color:var(--color-secondary);">
                        <?= Security::html($r['user']['nom'] . ' ' . $r['user']['prenom']) ?>
                    </td>
                    <td style="padding:12px; text-align:right;"><?= number_format($r['user']['salaire_mensuel'], 0, ',', ' ') ?></td>
                    <td style="padding:12px; text-align:right; color:#10b981;"><?= $r['primes'] > 0 ? '+'.number_format($r['primes'], 0, ',', ' ') : '-' ?></td>
                    <td style="padding:12px; text-align:right; color:#ef4444;"><?= $r['retenue_retard'] > 0 ? '-'.number_format($r['retenue_retard'], 0, ',', ' ') : '-' ?></td>
                    <td style="padding:12px; text-align:right; color:#ef4444;"><?= $r['retenues_manuelles'] > 0 ? '-'.number_format($r['retenues_manuelles'], 0, ',', ' ') : '-' ?></td>
                    <td style="padding:12px; text-align:right; font-weight:800; color:<?= $r['net_a_payer'] > 0 ? 'var(--color-primary)' : '#000' ?>; background:#f8fafc;">
                        <?= number_format($r['net_a_payer'], 0, ',', ' ') ?> FCFA
                    </td>
                </tr>
                <?php endforeach; ?>

                <?php if(empty($report)): ?>
                <tr>
                    <td colspan="6" style="padding:50px; text-align:center; color:#94a3b8;">Aucune donnée disponible pour cette période.</td>
                </tr>
                <?php endif; ?>
            </tbody>
            
            <!-- TOTAUX DE L'ENTREPRISE -->
            <tfoot>
                <tr style="background:#1e293b; color:#fff; font-weight:700;">
                    <td style="padding:15px; text-transform:uppercase; letter-spacing:1px;">TOTAL ENTREPRISE</td>
                    <td style="padding:15px; text-align:right;"><?= number_format($totalBase, 0, ',', ' ') ?></td>
                    <td style="padding:15px; text-align:right; color:#4ade80;">+<?= number_format($totalPrimes, 0, ',', ' ') ?></td>
                    <td style="padding:15px; text-align:right; color:#f87171;">-<?= number_format($totalRetards, 0, ',', ' ') ?></td>
                    <td style="padding:15px; text-align:right; color:#f87171;">-<?= number_format($totalRetenuesMan, 0, ',', ' ') ?></td>
                    <td style="padding:15px; text-align:right; font-size:16px; background:var(--color-primary); color:#fff;">
                        <?= number_format($totalNet, 0, ',', ' ') ?> FCFA
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Pied de page certification (Print only) -->
    <div class="print-only-footer" style="display:none; margin-top:50px;">
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:50px;">
            <div style="border:1px solid #ddd; padding:20px; border-radius:8px; height:150px;">
                <p style="margin:0; font-size:10px; font-weight:800; text-transform:uppercase; color:#666;">Visa de la Direction</p>
            </div>
            <div style="border:1px solid #ddd; padding:20px; border-radius:8px; height:150px;">
                <p style="margin:0; font-size:10px; font-weight:800; text-transform:uppercase; color:#666;">Visa de l'Expert Comptable / RH</p>
            </div>
        </div>
        <p style="text-align:center; margin-top:30px; font-size:10px; color:#999;">Généré numériquement par RHXtimes - Logiciel certifié Marvens Group Services</p>
    </div>
</div>

<style>
@media print {
    body * { visibility: hidden; }
    .printable-ledger-container, .printable-ledger-container * { visibility: visible; }
    .printable-ledger-container { 
        position: absolute; 
        left: 0; 
        top: 0; 
        width: 100%; 
        padding: 0 !important;
        margin: 0 !important;
    }
    .no-print { display: none !important; }
    .print-only-header, .print-only-footer { display: block !important; }
    .ledger-table th { background: #eee !important; color: #000 !important; -webkit-print-color-adjust: exact; }
    .ledger-table tfoot tr { background: #000 !important; color: #fff !important; -webkit-print-color-adjust: exact; }
    .ledger-table td[style*="background:#f8fafc"] { background: #f8fafc !important; -webkit-print-color-adjust: exact; }
    @page { size: A4 landscape; margin: 1cm; }
}
</style>
