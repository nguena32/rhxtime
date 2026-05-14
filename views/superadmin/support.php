<?php
// views/superadmin/support.php
?>
<div class="header-actions" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px;">
    <div>
        <h2 style="font-weight: 800; color: var(--color-secondary); margin: 0;">Support Desk (Tickets)</h2>
        <p style="margin: 4px 0 0 0; font-size: 13px; color: #64748b;">Gérez les demandes d'assistance de vos clients.</p>
    </div>
</div>

<div class="card" style="padding:0; overflow:hidden;">
    <div style="background:#f8fafc; padding:15px; border-bottom:1px solid #e2e8f0; display:flex; gap:10px;">
        <button class="badge" style="background:var(--color-primary); color:white; border:none; cursor:pointer; padding:5px 12px; font-size:12px;">Tous les tickets</button>
        <button class="badge" style="background:#fff; color:#64748b; border:1px solid #e2e8f0; cursor:pointer; padding:5px 12px; font-size:12px;">En attente (Open)</button>
    </div>
    <table class="table" style="margin:0;">
        <thead>
            <tr>
                <th>ID</th>
                <th>Client / Entreprise</th>
                <th>Sujet</th>
                <th>Priorité</th>
                <th>Statut</th>
                <th>Dernier Message</th>
                <th style="text-align:right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($tickets)): ?>
                <tr>
                    <td colspan="7" style="text-align:center; padding:40px; color:#94a3b8;">Aucun ticket à traiter.</td>
                </tr>
            <?php else: ?>
                <?php foreach($tickets as $t): ?>
                    <tr style="<?= $t['status'] == 'open' ? 'background:#f0fdf4;' : '' ?>">
                        <td>#<?= $t['id'] ?></td>
                        <td>
                            <div style="font-weight:700;"><?= htmlspecialchars($t['entreprise_nom']) ?></div>
                            <div style="font-size:11px; color:#64748b;">(Admin ID: <?= $t['admin_id'] ?>)</div>
                        </td>
                        <td><strong><?= htmlspecialchars($t['subject']) ?></strong></td>
                        <td>
                            <?php if($t['priority'] === 'high'): ?>
                                <span class="badge badge-danger">High</span>
                            <?php elseif($t['priority'] === 'medium'): ?>
                                <span class="badge badge-warning">Medium</span>
                            <?php else: ?>
                                <span class="badge badge-info">Low</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($t['status'] === 'open'): ?>
                                <span class="badge badge-warning" style="background:#f59e0b; animation: pulse 2s infinite;">Attente SuperAdmin</span>
                            <?php elseif($t['status'] === 'replied'): ?>
                                <span class="badge badge-success">Répondu</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Clôturé</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('d/m/Y H:i', strtotime($t['updated_at'])) ?></td>
                        <td style="text-align:right;">
                            <a href="index.php?page=superadmin_support_view&id=<?= $t['id'] ?>" class="btn-primary" style="padding:6px 14px; font-size:12px; text-decoration:none; border-radius:8px;">Répondre</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.6; }
    100% { opacity: 1; }
}
</style>
