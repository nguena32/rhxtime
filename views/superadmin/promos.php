<div class="header-actions" style="margin-bottom:25px;">
    <h2 style="font-weight:800; color:var(--color-secondary);">Gestion des Codes Promos</h2>
</div>

<!-- Formulaire Création Code Promo -->
<div class="card" style="margin-bottom:20px; border-radius:15px; border-left: 5px solid #10b981;">
    <h3 style="margin-bottom:15px;"><i class="fa-solid fa-ticket"></i> Créer un nouveau code</h3>
    <form method="POST" action="index.php?page=superadmin_promo_create" style="display:flex; gap:15px; flex-wrap:wrap; align-items:flex-end;">
        <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken() ?>">
        
        <div class="form-group" style="flex:1; min-width:150px;">
            <label>Code (Ex: CADEAU30)</label>
            <input type="text" name="code" class="form-control" required placeholder="En majuscules" style="text-transform:uppercase;">
        </div>
        <div class="form-group" style="flex:1; min-width:150px;">
            <label>Jours à ajouter</label>
            <input type="number" name="days_to_add" class="form-control" required min="1" value="30">
        </div>
        <div class="form-group" style="flex:1; min-width:150px;">
            <label>Limite d'utilisation (Max)</label>
            <input type="number" name="usage_limit" class="form-control" required min="1" value="1">
        </div>
        <div class="form-group" style="flex:1; min-width:150px;">
            <label>Date d'expiration (Optionnel)</label>
            <input type="date" name="expires_at" class="form-control">
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-success"><i class="fa-solid fa-plus"></i> Générer</button>
        </div>
    </form>
</div>

<!-- Liste des Codes -->
<div class="card" style="border-radius:15px;">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Code Promo</th>
                    <th>Jours Offerts</th>
                    <th>Utilisations (Actuel / Max)</th>
                    <th>Statut</th>
                    <th>Expiration API</th>
                    <th>Date Création</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($promos as $p): ?>
                <tr>
                    <td>#<?= $p['id'] ?></td>
                    <td><strong style="color:var(--color-primary); background:#f1f5f9; padding:4px 8px; border-radius:6px; letter-spacing:1px;"><?= htmlspecialchars($p['code']) ?></strong></td>
                    <td><span style="color:#10b981; font-weight:bold;">+<?= $p['days_to_add'] ?> Jours</span></td>
                    <td><?= $p['used_count'] ?> / <?= $p['usage_limit'] ?></td>
                    <td>
                        <?php if($p['is_active']): ?>
                            <span class="badge badge-success">Actif</span>
                        <?php else: ?>
                            <span class="badge badge-error" style="background:#fee2e2; color:#ef4444;">Inactif</span>
                        <?php endif; ?>
                    </td>
                    <td><?= $p['expires_at'] ? date('d/m/Y', strtotime($p['expires_at'])) : '---' ?></td>
                    <td><?= date('d/m/Y', strtotime($p['created_at'])) ?></td>
                    <td>
                        <form method="POST" action="index.php?page=superadmin_promo_toggle" style="display:inline;">
                            <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken() ?>">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <button type="submit" class="btn <?= $p['is_active'] ? 'btn-warning' : 'btn-primary' ?>" style="padding:6px 12px; font-size:11px;">
                                <i class="fa-solid <?= $p['is_active'] ? 'fa-ban' : 'fa-check' ?>"></i> 
                                <?= $p['is_active'] ? 'Désactiver' : 'Activer' ?>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($promos)): ?>
                <tr>
                    <td colspan="8" style="text-align:center; padding:20px; color:#94a3b8;">Aucun code promo créé pour le moment.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if(isset($totalPages) && $totalPages > 1): ?>
    <div style="display:flex; justify-content:center; gap:8px; margin-top:20px; padding-bottom:10px;">
        <?php for($i = 1; $i <= $totalPages; $i++): ?>
            <a href="index.php?page=superadmin_promos&p=<?= $i ?>" class="btn <?= (isset($page) && $page == $i) ? 'btn-primary' : '' ?>" style="<?= (isset($page) && $page == $i) ? '' : 'background:#f1f5f9; color:#64748b; border:1px solid #e2e8f0;' ?> padding:6px 12px; border-radius:8px; font-weight:600; text-decoration:none; font-size:12px;">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>
