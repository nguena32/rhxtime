<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($fileName) ?></title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background: #fff; margin:0; padding:0; color:#333; }
        .page { page-break-after: always; padding: 40px; box-sizing: border-box; width: 100%; min-height: 100vh; }
        .page-cover { display:flex; flex-direction:column; justify-content:center; align-items:center; text-align:center; }
        h1 { font-size: 32px; color: #1e293b; margin-bottom: 10px; }
        h2 { font-size: 24px; color: #475569; margin-bottom: 30px; }
        .summary-box { border: 2px solid #e2e8f0; border-radius: 12px; padding: 30px; margin-top: 40px; width: 80%; max-width: 600px; text-align: left;}
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 18px; border-bottom: 1px solid #f1f5f9; padding-bottom:10px; }
        .summary-row:last-child { border-bottom: none; font-weight: bold; font-size: 22px; color: #0f172a; margin-top:20px; }
        
        .bulletin { margin-top: 20px; }
        .header-bulletin { border-bottom: 2px solid #cbd5e1; padding-bottom: 20px; margin-bottom: 30px; }
        .table-paie { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table-paie th, .table-paie td { border: 1px solid #cbd5e1; padding: 12px; text-align: left; }
        .table-paie th { background: #f8fafc; font-weight: bold; }
        .text-right { text-align: right !important; }
        .bg-light { background: #f8fafc; }
        
        @media print {
            body { margin: 0; padding: 0; }
            .page { border: none; min-height: auto; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <!-- PAGE DE GARDE -->
    <div class="page page-cover">
        <h1 style="color:#4a6bfd;">LIVRE DE PAIE MENSUEL</h1>
        <h2>Entreprise : <?= htmlspecialchars($entrepriseName) ?></h2>
        <p style="font-size: 18px;">Période : <strong><?= str_pad($month, 2, '0', STR_PAD_LEFT) ?> / <?= $year ?></strong></p>
        
        <div class="summary-box">
            <h3 style="text-align:center; margin-top:0; border-bottom: 2px solid #e2e8f0; padding-bottom:15px;">Récapitulatif Global</h3>
            <div class="summary-row">
                <span>Nombre d'employés actifs :</span>
                <span><?= count($users) ?></span>
            </div>
            <div class="summary-row">
                <span>Total Salaires de Base (Brut) :</span>
                <span><?= number_format($totalBrut, 0, ',', ' ') ?> FCFA</span>
            </div>
            <div class="summary-row" style="color:#ef4444;">
                <span>Total Retenues (Retards) :</span>
                <span>-<?= number_format($totalBrut - $totalNet, 0, ',', ' ') ?> FCFA</span>
            </div>
            <div class="summary-row">
                <span>Total Net à Payer :</span>
                <span style="color:#10b981;"><?= number_format($totalNet, 0, ',', ' ') ?> FCFA</span>
            </div>
        </div>
        
        <button class="no-print" onclick="window.print()" style="margin-top:50px; padding:15px 30px; font-size:18px; background:#4a6bfd; color:white; border:none; border-radius:8px; cursor:pointer;">Lancer l'impression PDF</button>
    </div>

    <!-- BULLETINS INDIVIDUELS -->
    <?php foreach($bulletinsData as $data): $u = $data['user']; ?>
    <div class="page">
        <div class="header-bulletin" style="display:flex; justify-content:space-between;">
            <div>
                <h1 style="margin:0; font-size:24px;">BULLETIN DE PAIE</h1>
                <p style="margin:5px 0; color:#64748b;">Période : <?= str_pad($month, 2, '0', STR_PAD_LEFT) ?>/<?= $year ?></p>
            </div>
            <div style="text-align:right;">
                <h3 style="margin:0; color:#4a6bfd;"><?= htmlspecialchars($entrepriseName) ?></h3>
            </div>
        </div>

        <div style="display:flex; gap:40px; margin-bottom:30px;">
            <div style="flex:1;">
                <h4 style="margin:0 0 10px 0; border-bottom:1px solid #e2e8f0; padding-bottom:5px;">INFORMATIONS EMPLOYÉ</h4>
                <p><strong>Nom :</strong> <?= htmlspecialchars($u['nom'] . ' ' . $u['prenom']) ?></p>
                <p><strong>Fonction :</strong> <?= htmlspecialchars($u['fonction']) ?></p>
                <p><strong>Matricule :</strong> EMP-<?= str_pad($u['id'], 4, '0', STR_PAD_LEFT) ?></p>
                <p><strong>Lieu d'affectation :</strong> <?= htmlspecialchars($u['nom_lieu']) ?></p>
            </div>
        </div>

        <table class="table-paie">
            <thead>
                <tr>
                    <th>Désignation</th>
                    <th class="text-right">Base / Heures</th>
                    <th class="text-right">Taux</th>
                    <th class="text-right">Gains</th>
                    <th class="text-right">Retenues</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Salaire de base</td>
                    <td class="text-right">Mensuel</td>
                    <td class="text-right">-</td>
                    <td class="text-right"><?= number_format($u['salaire_mensuel'], 0, ',', ' ') ?></td>
                    <td class="text-right"></td>
                </tr>
                <tr>
                    <td>Retard constaté</td>
                    <td class="text-right"><?= number_format($data['delay_hours'], 2) ?>h</td>
                    <td class="text-right"><?= number_format($data['taux'], 0, ',', ' ') ?> /h</td>
                    <td class="text-right"></td>
                    <td class="text-right text-danger"><?= number_format($data['retenue'], 0, ',', ' ') ?></td>
                </tr>
                
                <tr class="bg-light" style="font-weight:bold; font-size:18px;">
                    <td colspan="3" class="text-right">NET À PAYER (FCFA)</td>
                    <td colspan="2" class="text-right" style="color:#10b981; font-size:22px;"><?= number_format($data['net'], 0, ',', ' ') ?></td>
                </tr>
            </tbody>
        </table>

        <div style="margin-top:50px; display:flex; justify-content:space-between;">
            <div style="text-align:center; width:200px; padding-top:100px; border-top:1px dashed #cbd5e1;">Signature Employé</div>
            <div style="text-align:center; width:200px; padding-top:100px; border-top:1px dashed #cbd5e1;">Direction des RH</div>
        </div>
    </div>
    <?php endforeach; ?>

    <script>
        // Si l'utilisateur a activé l'impression automatique
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        }
    </script>
</body>
</html>
