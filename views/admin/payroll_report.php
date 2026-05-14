<!-- views/admin/payroll_report.php -->
<div class="container">
    <?php if(isset($_GET['success'])): ?>
        <div style="padding:10px; background:#dcfce7; color:#166534; border-radius:8px; margin-bottom:15px;">
            Opération réussie !
        </div>
    <?php endif; ?>
    <?php if(isset($_GET['error'])): ?>
        <div style="padding:10px; background:#fee2e2; color:#991b1b; border-radius:8px; margin-bottom:15px;">
            Erreur : <?= Security::html($_GET['error']) ?>
        </div>
    <?php endif; ?>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h1 class="text-h1">Gestion de la Paie</h1>
        <form method="GET" action="index.php" style="display:flex; gap:10px; flex-wrap: wrap;">
            <input type="hidden" name="page" value="admin_payroll">
            
            <select name="lieu_id" class="form-control" style="width:180px;">
                <option value="">Tous les Lieux</option>
                <?php foreach($lieux as $l): ?>
                    <option value="<?= $l['id'] ?>" <?= $l['id'] == $lieu_id ? 'selected' : '' ?>><?= Security::html($l['nom_lieu']) ?></option>
                <?php endforeach; ?>
            </select>

            <select name="month" class="form-control" style="width:140px;">
                <?php 
                $months = [
                    1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril', 5 => 'Mai', 6 => 'Juin',
                    7 => 'Juillet', 8 => 'Août', 9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
                ];
                foreach($months as $num => $name): ?>
                    <option value="<?= $num ?>" <?= $num == $month ? 'selected' : '' ?>><?= $name ?></option>
                <?php endforeach; ?>
            </select>
            <select name="year" class="form-control" style="width:100px;">
                <?php for($y=2025; $y<=2027; $y++): ?>
                    <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
            <button type="submit" class="btn btn-primary">Filtrer</button>
        </form>
    </div>

    <div class="card" style="padding:0; overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                <tr>
                    <th style="padding:15px; text-align:left;">Employé</th>
                    <th style="padding:15px; text-align:left;">Lieu</th>
                    <th style="padding:15px; text-align:right;">Salaire Base</th>
                    <th style="padding:15px; text-align:right;">Retards (h)</th>
                    <th style="padding:15px; text-align:right;">Retenue</th>
                    <th style="padding:15px; text-align:right;">Net à Payer</th>
                    <th style="padding:15px; text-align:center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($report as $r): ?>
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:15px; font-weight:600;"><?= Security::html($r['user']['nom'] . ' ' . $r['user']['prenom']) ?></td>
                    <td style="padding:15px; color:#64748b; font-size:14px;"><?= Security::html($r['user']['nom_lieu']) ?></td>
                    <td style="padding:15px; text-align:right; font-weight:600;"><?= number_format($r['user']['salaire_mensuel'] ?? 0, 0, ',', ' ') ?></td>
                    <td style="padding:15px; text-align:right; color:#ef4444; font-weight:700;">-<?= number_format($r['total_delay_hours'] ?? 0, 2) ?>h</td>
                    <td style="padding:15px; text-align:right; color:#ef4444;">-<?= number_format($r['retenue'] ?? 0, 0, ',', ' ') ?></td>
                    <td style="padding:15px; text-align:right; font-weight:800; color:#10b981;"><?= number_format($r['net_a_payer'] ?? 0, 0, ',', ' ') ?> FCFA</td>
                    <td style="padding:15px; text-align:center;">
                        <a href="index.php?page=admin_payroll_detail&user_id=<?= $r['user']['id'] ?>&month=<?= $month ?>&year=<?= $year ?>&lieu_id=<?= $lieu_id ?>" class="btn btn-primary" style="padding: 6px 12px; font-size: 13px;" target="_blank">
                            <i class="fa-solid fa-file-lines"></i> Détails
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
