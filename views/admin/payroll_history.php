<?php
// views/admin/payroll_history.php
if(!isset($_SESSION['admin_id'])) { header('Location: index.php?page=login'); exit; }
?>
<div class="header-actions" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px;">
    <div>
        <h2 style="font-weight: 800; color: var(--color-secondary); margin:0;">Bulletins de paie générés</h2>
        <p style="margin:0; font-size:12px; color:#64748b;">Historique et traçabilité des rémunérations.</p>
    </div>
</div>

<div class="card" style="border-radius:20px; padding:20px; background:#fff; margin-bottom:30px;">
    <form method="GET" action="index.php" style="display:flex; gap:10px; margin-bottom:20px;">
        <input type="hidden" name="page" value="admin_payroll_history">
        <div style="flex:1; position:relative;">
            <i class="fa-solid fa-magnifying-glass" style="position:absolute; left:15px; top:50%; transform:translateY(-50%); color:#94a3b8;"></i>
            <input type="text" name="search" value="<?= Security::html($search) ?>" placeholder="Rechercher par nom d'employé..." class="form-control" style="padding-left:45px; width:100%; border-radius:12px;">
        </div>
        <button type="submit" class="btn btn-primary" style="border-radius:12px; padding:0 25px;">Filtrer</button>
        <?php if($search): ?>
            <a href="index.php?page=admin_payroll_history" class="btn btn-outline" style="border-radius:12px;"><i class="fa-solid fa-xmark"></i></a>
        <?php endif; ?>
    </form>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Employé</th>
                    <th>Période</th>
                    <th>ID Unique Certification</th>
                    <th>Net à payer</th>
                    <th>Date de génération</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($bulletins)): ?>
                    <tr>
                        <td colspan="6" class="text-center" style="padding:40px; color:#94a3b8;">
                            <i class="fa-solid fa-file-circle-exclamation" style="font-size:30px; display:block; margin-bottom:10px;"></i>
                            Aucun bulletin généré trouvé.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach($bulletins as $b): ?>
                        <tr>
                            <td style="font-weight:700; color:var(--color-secondary);">
                                <?= Security::html($b['nom'] . ' ' . $b['prenom']) ?>
                            </td>
                            <td>
                                <span class="badge" style="background:#f1f5f9; color:#475569; padding:5px 10px; border-radius:6px; font-weight:600;">
                                    <?= date('M Y', mktime(0,0,0,$b['month'],1,$b['year'])) ?>
                                </span>
                            </td>
                            <td>
                                <code style="background:#f8fafc; padding:3px 8px; border-radius:4px; color:var(--color-primary); font-size:11px;"><?= $b['unique_id'] ?></code>
                            </td>
                            <td style="font-weight:700; color:#10b981;">
                                <?= number_format($b['net_to_pay'], 0, ',', ' ') ?> FCFA
                            </td>
                            <td style="font-size:12px; color:#64748b;">
                                <?= date('d/m/Y H:i', strtotime($b['generated_at'])) ?>
                            </td>
                            <td class="text-right">
                                <a href="index.php?page=admin_payroll_bulletin&user_id=<?= $b['user_id'] ?>&month=<?= $b['month'] ?>&year=<?= $b['year'] ?>" target="_blank" class="btn btn-outline" title="Afficher/Imprimer">
                                    <i class="fa-solid fa-print"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
