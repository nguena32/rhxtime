<div class="header-actions" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px;">
    <h2 style="font-weight: 800; color: var(--color-secondary);">Audit Logs</h2>
</div>

<div class="card" style="border-radius:15px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
        <h3 style="font-weight: 800; color: var(--color-secondary);">Journal des modifications système</h3>
    </div>
    
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Utilisateur (Admin)</th>
                    <th>Entreprise</th>
                    <th>Action</th>
                    <th>Ancienne Valeur</th>
                    <th>Nouvelle Valeur</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($logs as $log): ?>
                <tr>
                    <td style="white-space:nowrap; font-size:12px; color:#64748b;">
                        <?= date('d/m/Y H:i', strtotime($log['created_at'])) ?>
                    </td>
                    <td>
                        <div style="font-weight:600; font-size:13px;"><?= htmlspecialchars($log['admin_email'] ?? 'Système') ?></div>
                    </td>
                    <td>
                        <span style="font-size:12px; color:#475569;"><?= htmlspecialchars($log['entreprise_nom'] ?? 'Plateforme') ?></span>
                    </td>
                    <td>
                        <?php 
                            $badgeClass = 'badge-primary';
                            if(strpos($log['action'], 'DELETE') !== false) $badgeClass = 'badge-danger';
                            if(strpos($log['action'], 'CREATE') !== false) $badgeClass = 'badge-success';
                            if(strpos($log['action'], 'MODIF') !== false) $badgeClass = 'badge-warning';
                        ?>
                        <span class="badge <?= $badgeClass ?>" style="font-size:10px; text-transform:uppercase;">
                            <?= htmlspecialchars($log['action']) ?>
                        </span>
                    </td>
                    <td style="max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; font-size:12px; color:#94a3b8;">
                        <?= htmlspecialchars($log['old_value'] ?? '—') ?>
                    </td>
                    <td style="max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; font-size:12px; color:var(--color-primary); font-weight:600;">
                        <?= htmlspecialchars($log['new_value'] ?? '—') ?>
                    </td>
                    <td>
                        <code style="font-size:11px; background:#f1f5f9; padding:2px 5px; border-radius:4px;"><?= htmlspecialchars($log['ip_address']) ?></code>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($logs)): ?>
                <tr>
                    <td colspan="7" style="text-align:center; padding:30px; color:#94a3b8;">Aucun log d'audit trouvé.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if(isset($totalPages) && $totalPages > 1): ?>
    <div style="display:flex; justify-content:center; gap:8px; margin-top:20px;">
        <?php for($i = 1; $i <= $totalPages; $i++): ?>
            <a href="index.php?page=superadmin_audit_logs&p=<?= $i ?>" class="btn <?= (isset($page) && $page == $i) ? 'btn-primary' : '' ?>" style="<?= (isset($page) && $page == $i) ? '' : 'background:#f1f5f9; color:#64748b; border:1px solid #e2e8f0;' ?> padding:6px 12px; border-radius:8px; font-weight:600; text-decoration:none;">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>
