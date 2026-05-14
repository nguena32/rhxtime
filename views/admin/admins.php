<div class="header-actions" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
    <h2 class="text-h2">Gestion des Managers</h2>
    <button class="btn btn-primary" onclick="checkQuotaAndOpen('managers', <?= (int)$currentManagers ?>, <?= (int)$maxManagers ?>, () => document.getElementById('addAdminModal').style.display='flex')">
        <i class="fa-solid fa-plus"></i> Recruter un Manager
    </button>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Périmètre / Site</th>
                    <th>Date d'ajout</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($admins as $a): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($a['email']) ?></strong></td>
                    <td>
                        <span class="badge <?= $a['role'] === 'owner' ? 'badge-primary' : 'badge-warning' ?>" style="background:<?= $a['role'] === 'owner' ? '#e0e7ff' : '#fef3c7' ?>; color:<?= $a['role'] === 'owner' ? '#4338ca' : '#92400e' ?>;">
                            <?= ucfirst($a['role']) ?>
                        </span>
                    </td>
                    <td>
                        <?php if($a['lieu_id']): 
                            $siteName = "Site ID#".$a['lieu_id'];
                            foreach($lieux as $lx) { if($lx['id'] == $a['lieu_id']) { $siteName = $lx['nom_lieu']; break; } }
                            echo '<span style="color:var(--color-primary); font-weight:600;"><i class="fa-solid fa-location-dot"></i> ' . htmlspecialchars($siteName) . '</span>';
                        else: ?>
                            <span style="color:#64748b;">Tous les sites (Global)</span>
                        <?php endif; ?>
                    </td>
                    <td><?= date('d/m/Y', strtotime($a['created_at'])) ?></td>
                    <td>
                        <?php if($a['id'] != $_SESSION['admin_id']): ?>
                            <button class="btn btn-outline" style="color:var(--color-danger); padding:8px 12px; background:#fff1f2; border:none;" 
                                    onclick="confirmDeleteAdmin(<?= $a['id'] ?>)" 
                                    title="Supprimer l'accès">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        <?php else: ?>
                            <small class="badge" style="background:#f1f5f9; color:#64748b; border:none;">Moi (Connecté)</small>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Ajout Admin -->
<div id="addAdminModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.6); z-index:9999; align-items:center; justify-content:center; backdrop-filter: blur(8px);">
    <div class="card" style="width:95%; max-width:450px; padding:35px; border-radius: 24px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);">
        <button onclick="document.getElementById('addAdminModal').style.display='none'" style="position:absolute; top:20px; right:20px; border:none; background:#f1f5f9; width: 35px; height: 35px; border-radius: 50%; cursor:pointer; font-size:18px;">&times;</button>
        <h2 class="text-h2" style="margin-bottom:25px;">Nouvel Administrateur</h2>
        
        <form method="POST" action="index.php?page=admin_create_admin">
            <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken() ?>">
            <div class="form-group">
                <label>Email du collaborateur</label>
                <input type="email" name="email" class="form-control" required placeholder="nom@entreprise.com">
            </div>
            <div class="form-group">
                <label>Mot de passe provisoire</label>
                <input type="password" name="password" class="form-control" required placeholder="••••••••">
            </div>
            <div class="form-group">
                <label>Rôle attribué</label>
                <select name="role" class="form-control">
                    <option value="manager">Manager (Opérationnel)</option>
                    <option value="owner">Co-Propriétaire (Accès total)</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:30px;">
                <label>Périmètre d'action (Site d'affectation)</label>
                <select name="lieu_id" class="form-control">
                    <option value="">Tous les sites (Accès total)</option>
                    <?php foreach($lieux as $l): ?>
                        <option value="<?= $l['id'] ?>"><?= htmlspecialchars($l['nom_lieu']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%; padding:15px; font-size:16px;">
                <i class="fa-solid fa-user-plus"></i> Créer le compte
            </button>
        </form>
    </div>
</div>

<script>
function confirmDeleteAdmin(id) {
    Swal.fire({
        title: 'Supprimer cet accès ?',
        text: "Ce manager ne pourra plus accéder à la plateforme. Cette action est irréversible.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Oui, supprimer',
        cancelButtonText: 'Annuler',
        confirmButtonColor: '#EF4444'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `index.php?page=admin_delete_admin&id=${id}&csrf_token=<?= Security::generateCsrfToken() ?>`;
        }
    });
}
window.onclick = function(event) {
    if (event.target == document.getElementById('addAdminModal')) document.getElementById('addAdminModal').style.display='none';
}
</script>
