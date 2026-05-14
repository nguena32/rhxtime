<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
    <h1 class="text-h2">Gestion du Personnel</h1>
    <button onclick="checkQuotaAndOpen('employés', <?= (int)$currentEmployees ?>, <?= (int)$maxEmployees ?>, () => openModal('add'))" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Ajouter un employé
    </button>
</div>

<form method="GET" action="index.php" style="display:flex; gap:10px; margin-bottom:20px;">
    <input type="hidden" name="page" value="admin_users">
    <input type="text" name="search" value="<?= isset($_GET['search']) ? Security::html($_GET['search']) : '' ?>" placeholder="Rechercher par Nom, Prénom, Téléphone, Email..." class="form-control" style="flex:1; border-radius: 8px; padding: 10px;">
    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-search"></i> Rechercher</button>
</form>

<div class="card" style="overflow-x:auto;">
    <table style="width:100%; border-collapse: collapse; min-width: 800px; text-align: left;">
        <thead>
            <tr style="border-bottom: 2px solid #e2e8f0; color: #64748b; font-size: 13px;">
                <th style="padding: 12px 10px;">Nom et Prénom</th>
                <th style="padding: 12px 10px;">Lieu d'affectation</th>
                <th style="padding: 12px 10px;">Fonction ou Poste</th>
                <th style="padding: 12px 10px;">Email</th>
                <th style="padding: 12px 10px;">Téléphone</th>
                <th style="padding: 12px 10px;">Date Création</th>
                <th style="padding: 12px 10px; text-align: center;">Débloquer</th>
                <th style="padding: 12px 10px; text-align: center;">Modifier</th>
                <th style="padding: 12px 10px; text-align: center;">Suspendre</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($users as $u): ?>
                <tr style="border-bottom: 1px solid #f1f5f9; <?= !$u['is_active'] ? 'background:#f8fafc; color:#94a3b8;' : '' ?>">
                    <td style="padding: 12px 10px; font-weight: 600; <?= !$u['is_active'] ? 'text-decoration:line-through;' : '' ?>">
                        <?= Security::html($u['nom']) ?> <?= Security::html($u['prenom']) ?>
                    </td>
                    <td style="padding: 12px 10px;"><?= Security::html($u['nom_lieu'] ?? 'Aucun lieu') ?></td>
                    <td style="padding: 12px 10px;"><?= Security::html($u['fonction']) ?></td>
                    <td style="padding: 12px 10px;"><?= Security::html($u['email']) ?></td>
                    <td style="padding: 12px 10px;"><?= Security::html($u['telephone']) ?></td>
                    <td style="padding: 12px 10px;">
                        <span style="font-size: 12px; color: #64748b;">
                            <?= !empty($u['created_at']) ? date('d/m/Y', strtotime($u['created_at'])) : '—' ?>
                        </span>
                    </td>
                    
                    <td style="padding: 12px 10px; text-align: center;">
                        <button class="btn" style="background:#fff7ed; color:#c2410c; padding:6px 10px;" onclick="confirmResetDevice(<?= $u['id'] ?>)" title="Débloquer nouveau téléphone">
                            <i class="fa-solid fa-mobile-screen"></i>
                        </button>
                    </td>
                    <td style="padding: 12px 10px; text-align: center;">
                        <button type="button" onclick='openEditModal(<?= json_encode($u) ?>)' class="btn" style="background:#f3f4f6; color:#374151; padding:6px 10px;">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                    </td>
                    <td style="padding: 12px 10px; text-align: center;">
                        <button class="btn" style="background: <?= $u['is_active'] ? '#fee2e2' : '#d1fae5' ?>; color: <?= $u['is_active'] ? '#991b1b' : '#065f46' ?>; padding:6px 10px;" onclick="confirmToggleUser(<?= $u['id'] ?>, <?= $u['is_active'] ? 'true' : 'false' ?>)">
                            <?= $u['is_active'] ? '<i class="fa-solid fa-ban"></i>' : '<i class="fa-solid fa-check"></i>' ?>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if(empty($users)): ?>
                <tr>
                    <td colspan="9" style="text-align: center; padding: 20px; color: var(--color-text-muted);">Aucun employé trouvé.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <!-- Pagination Users -->
    <?php if(isset($totalPages) && $totalPages > 1): ?>
    <div style="display:flex; justify-content:center; gap:8px; margin-top:20px;">
        <?php for($i = 1; $i <= $totalPages; $i++): ?>
            <a href="index.php?page=admin_users&p=<?= $i ?><?= isset($_GET['search']) ? '&search='.urlencode($_GET['search']) : '' ?>" class="btn <?= (isset($page) && $page == $i) ? 'btn-primary' : '' ?>" style="<?= (isset($page) && $page == $i) ? '' : 'background:#f1f5f9; color:#64748b; border:1px solid #e2e8f0;' ?> padding:6px 12px; border-radius:8px; font-weight:600; text-decoration:none; font-size:12px;">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<div id="userModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.6); z-index:1000; align-items:center; justify-content:center; backdrop-filter:blur(8px);">
    <div class="card" style="width:95%; max-width:550px; max-height:90vh; overflow-y:auto; padding:30px; border-radius:24px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
        <button onclick="closeModal()" style="position:absolute; top:20px; right:20px; border:none; background:#f1f5f9; width:35px; height:35px; border-radius:50%; font-size:20px; cursor:pointer; display:flex; align-items:center; justify-content:center;">&times;</button>
        
        <h2 id="modalTitle" class="text-h2" style="margin-bottom:25px; font-size:24px;">Ajouter un employé</h2>
        
        <form id="userForm" method="POST" action="index.php?page=admin_create_user">
            <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken() ?>">
            <input type="hidden" name="id" id="edit_id">
            
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
                <div class="form-group">
                    <label>Nom</label>
                    <input type="text" name="nom" id="edit_nom" class="form-control" required placeholder="Ex: DOE">
                </div>
                <div class="form-group">
                    <label>Prénom</label>
                    <input type="text" name="prenom" id="edit_prenom" class="form-control" required placeholder="Ex: John">
                </div>
            </div>

            <div class="form-group">
                <label>Lieu d'affectation (Obligatoire)</label>
                <select name="lieu_id" id="edit_lieu" class="form-control" required>
                    <option value="">-- Choisir un lieu d'affectation --</option>
                    <?php foreach($lieux as $l): ?>
                        <option value="<?= $l['id'] ?>"><?= Security::html($l['nom_lieu']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
                <div class="form-group">
                    <label>Fonction / Poste</label>
                    <input type="text" name="fonction" id="edit_fonction" class="form-control" required placeholder="Ex: Développeur">
                </div>
                <div class="form-group">
                    <label>Salaire (Base FCFA)</label>
                    <input type="number" name="salaire_mensuel" id="edit_salaire" class="form-control" required placeholder="Ex: 150000">
                </div>
            </div>

            <div class="form-group">
                <label>Téléphone / Identifiant</label>
                <input type="text" name="telephone" id="edit_telephone" class="form-control" required placeholder="Numéro à 9 chiffres : 6XXXXXXXX">
            </div>

            <input type="hidden" name="email" id="edit_email">

            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" name="password" id="edit_password" class="form-control" placeholder="Laisser vide si inchangé">
                <small style="color:var(--color-text-muted); font-size:12px; display:block; margin-top:-10px;" id="passHelp">Requis pour la création.</small>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%; margin-top:15px; padding:15px; font-size:16px;">
                <i class="fa-solid fa-save"></i> Enregistrer les informations
            </button>
        </form>
    </div>
</div>

<script>
function confirmResetDevice(id) {
    Swal.fire({
        title: 'Réinitialiser le téléphone ?',
        text: "L'employé pourra se reconnecter avec un nouvel appareil.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Oui, réinitialiser',
        cancelButtonText: 'Annuler',
        confirmButtonColor: '#F59E0B'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `index.php?page=admin_reset_device&id=${id}&csrf_token=<?= Security::generateCsrfToken() ?>`;
        }
    });
}

function confirmToggleUser(id, isActive) {
    const title = isActive ? 'Suspendre cet employé ?' : 'Réactiver cet employé ?';
    const text = isActive ? "Il ne pourra plus pointer jusqu'à sa réactivation." : "Il pourra à nouveau pointer sur ses sites.";
    const btnText = isActive ? 'Oui, suspendre' : 'Oui, réactiver';
    const btnColor = isActive ? '#EF4444' : '#10B981';

    Swal.fire({
        title: title,
        text: text,
        icon: isActive ? 'warning' : 'info',
        showCancelButton: true,
        confirmButtonText: btnText,
        cancelButtonText: 'Annuler',
        confirmButtonColor: btnColor
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `index.php?page=admin_toggle_user&id=${id}&csrf_token=<?= Security::generateCsrfToken() ?>`;
        }
    });
}

function openModal(mode) {
    const modal = document.getElementById('userModal');
    const form = document.getElementById('userForm');
    const title = document.getElementById('modalTitle');
    
    form.reset();
    document.getElementById('edit_id').value = '';
    modal.style.display = 'flex';

    if(mode === 'add') {
        title.innerText = "Ajouter un employé";
        form.action = "index.php?page=admin_create_user";
        document.getElementById('edit_password').required = true;
        document.getElementById('passHelp').innerText = "Veuillez créer un mot de passe sécurisé.";
    }
}

function openEditModal(user) {
    const modal = document.getElementById('userModal');
    const form = document.getElementById('userForm');
    const title = document.getElementById('modalTitle');

    modal.style.display = 'flex';
    title.innerText = "Modifier l'employé";
    form.action = "index.php?page=admin_update_user";

    document.getElementById('edit_id').value = user.id;
    document.getElementById('edit_nom').value = user.nom;
    document.getElementById('edit_prenom').value = user.prenom;
    document.getElementById('edit_fonction').value = user.fonction;
    document.getElementById('edit_telephone').value = user.telephone;
    document.getElementById('edit_email').value = user.email || '';
    document.getElementById('edit_salaire').value = user.salaire_mensuel;
    document.getElementById('edit_lieu').value = user.lieu_id;

    document.getElementById('edit_password').required = false;
    document.getElementById('passHelp').innerText = "Laissez vide pour conserver le mot de passe actuel.";
}

function closeModal() {
    document.getElementById('userModal').style.display = 'none';
}

window.onclick = function(event) {
    if (event.target == document.getElementById('userModal')) closeModal();
}
</script>