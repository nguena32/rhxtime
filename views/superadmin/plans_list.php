<?php
// views/superadmin/plans_list.php
?>

<div class="header-actions" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px; flex-wrap:wrap; gap:12px;">
    <div>
        <h2 class="text-h2" style="margin: 0;">
            <i class="fa-solid fa-layer-group" style="color: #4f46e5; margin-right: 8px;"></i>
            Configuration des Forfaits
        </h2>
        <p style="margin: 4px 0 0 0; font-size: 13px; color: #64748b;">Gérez les offres commerciales, quotas et périodes d'essai du SaaS.</p>
    </div>
    <a href="index.php?page=superadmin_plan_form" class="btn btn-primary" style="border-radius:10px; padding:12px 24px;">
        <i class="fa-solid fa-plus"></i> Créer une offre
    </a>
</div>

<div class="card" style="border-radius:15px; padding:0; overflow:hidden;">
    <div class="table-responsive">
        <table class="table" style="margin:0;">
            <thead style="background:#f8fafc;">
                <tr>
                    <th style="padding:15px 20px;">Forfait</th>
                    <th>Prix (Mensuel/Annuel)</th>
                    <th>Quotas (Emp/Sites/Mgr)</th>
                    <th>Essai</th>
                    <th>Support</th>
                    <th style="text-align:right; padding-right:20px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($plans as $p): ?>
                <tr>
                    <td style="padding:15px 20px;">
                        <div style="font-weight:700; color:#1e293b;"><?= htmlspecialchars($p['nom']) ?></div>
                        <div style="font-size:11px; color:#94a3b8;">ID: #<?= $p['id'] ?></div>
                    </td>
                    <td>
                        <div style="font-weight:600; font-size:14px;"><?= number_format($p['montant_mensuel'], 0, ',', ' ') ?> <small>FCFA</small></div>
                        <div style="font-size:11px; color:#94a3b8;">Annuel: <?= number_format($p['montant_annuel_mensualise'] ?? 0, 0, ',', ' ') ?> FCFA</div>
                    </td>
                    <td>
                        <div style="display:flex; gap:5px; flex-wrap:wrap;">
                            <span class="badge" style="background:#eef2ff; color:#4f46e5; border:none;"><?= $p['max_employees'] ?> Agents</span>
                            <span class="badge" style="background:#f0fdf4; color:#16a34a; border:none;"><?= $p['max_sites'] ?> Sites</span>
                            <span class="badge" style="background:#fff7ed; color:#c2410c; border:none;"><?= $p['max_managers'] ?> Mgr.</span>
                        </div>
                    </td>
                    <td>
                        <div style="font-weight:600; color:#64748b; font-size:13px;"><?= $p['trial_days'] ?> jours</div>
                    </td>
                    <td>
                        <span class="badge" style="background:<?= $p['support_type'] === 'prioritaire' ? '#fef3c7; color:#92400e;' : '#f1f5f9; color:#475569;' ?>; border:none;">
                            <?= ucfirst($p['support_type']) ?>
                        </span>
                        <div style="margin-top:5px; font-size:10px; display:flex; gap:3px; flex-wrap:wrap;">
                            <?php if($p['has_direct_scan'] ?? 0): ?><span title="Pointage Direct" style="background:#e0f2fe; color:#0369a1; padding:2px 4px; border-radius:4px;">📍 GPS</span><?php endif; ?>
                            <?php if($p['has_messaging'] ?? 0): ?><span title="Messagerie" style="background:#f3e8ff; color:#7e22ce; padding:2px 4px; border-radius:4px;">💬 MSG</span><?php endif; ?>
                        </div>
                    </td>
                    <td style="text-align:right; padding-right:20px;">
                        <div style="display:flex; justify-content:flex-end; gap:8px;">
                            <a href="index.php?page=superadmin_plan_form&id=<?= $p['id'] ?>" class="btn" style="padding:8px 12px; background:#f1f5f9; color:#475569; border:none;" title="Modifier">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            <button class="btn" style="padding:8px 12px; background:#fff1f2; color:#e11d48; border:none;" title="Supprimer" onclick="confirmDeletePlan(<?= $p['id'] ?>)">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function confirmDeletePlan(id) {
    Swal.fire({
        title: 'Supprimer ce forfait ?',
        text: "Cette action est irréversible. Les entreprises utilisant ce plan ne pourront plus le renouveler.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Oui, supprimer le plan',
        cancelButtonText: 'Annuler',
        confirmButtonColor: '#e11d48'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `index.php?page=superadmin_plan_delete&id=${id}&csrf_token=<?= Security::generateCsrfToken() ?>`;
        }
    });
}
</script>
