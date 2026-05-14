<div class="header-actions" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px;">
    <h2 style="font-weight: 800; color: var(--color-secondary);">Gestion Client</h2>
</div>

<div class="card" style="border-radius:15px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; flex-wrap:wrap; gap:10px;">
        <h3 style="font-weight: 800; color: var(--color-secondary);">Gestion des Entreprises clientes</h3>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Pays</th>
                    <th>Entreprise / Contact</th>
                    <th>Secteur</th>
                    <th>Plan</th>
                    <th>Employés</th>
                    <th>Pass Admin</th>
                    <th>Statut Expiration</th>
                    <th>Confirmation</th>
                    <th>Info</th>
                    <th>Actions</th>
                    <th>Suppr.</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($entreprises as $e): 
                    $expired = ($e['expiration_date'] && strtotime($e['expiration_date']) < time());
                ?>
                <tr>
                    <td>#<?= $e['id'] ?></td>
                    <td>
                        <?php if (!empty($e['pays'])): ?>
                            <span style="font-size:13px; font-weight:600; color:#374151;"><?= htmlspecialchars($e['pays']) ?></span>
                        <?php else: ?>
                            <span style="color:#cbd5e1; font-size:12px;">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="font-weight:700;"><?= htmlspecialchars($e['nom']) ?></div>
                        <div style="font-size:12px; color:#64748b;"><?= htmlspecialchars($e['email']) ?></div>
                    </td>
                    <td>
                        <span style="font-size:12px; background:#f1f5f9; color:#475569; padding:4px 8px; border-radius:6px; white-space:nowrap;">
                            <?= htmlspecialchars($e['secteur'] ?? '—') ?>
                        </span>
                    </td>
                    <td>
                        <div style="display:flex; align-items:center; gap:5px;">
                            <span class="badge <?= ($e['plan_id'] > 1) ? 'badge-primary' : 'badge-warning' ?>" style="font-size:10px;"><?= $e['plan_nom'] ?? 'Starter' ?></span>
                            <button onclick="modalUpdatePlan(<?= $e['id'] ?>, '<?= addslashes($e['nom']) ?>', <?= $e['plan_id'] ?>)" class="btn" style="padding:4px; font-size:10px; background:transparent; color:#64748b;" title="Changer de plan">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                        </div>
                    </td>
                    <td><b style="color:var(--color-primary);"><?= $counts[$e['id']] ?? 0 ?></b> actifs</td>
                    <td>
                        <?php if(isset($adminMapping[$e['id']])): ?>
                            <button onclick="modalResetPass(<?= $adminMapping[$e['id']] ?>, '<?= addslashes($e['nom']) ?>')" class="btn" style="padding:6px 12px; font-size:11px; background:#f8fafc; color:#64748b; border:1px solid #e2e8f0; border-radius:8px;">
                                <i class="fa-solid fa-key"></i> Reset
                            </button>
                        <?php else: ?><small style="color:#cbd5e1;">(Aucun Admin)</small><?php endif; ?>
                    </td>
                    <td>
                        <?php if($expired): ?>
                            <span style="color:#ef4444; font-weight:700; background:#fee2e2; padding:4px 8px; border-radius:6px; font-size:11px;">EXPIRÉ (<?= date('d/m/Y', strtotime($e['expiration_date'])) ?>)</span>
                        <?php else: ?>
                            <span style="color:#10b981; font-weight:700; background:#dcfce7; padding:4px 8px; border-radius:6px; font-size:11px;"><?= $e['expiration_date'] ? 'VALIDE (J-' . ceil((strtotime($e['expiration_date']) - time())/86400) . ')' : 'Indéfini' ?></span>
                        <?php endif; ?>
                        
                        <div style="margin-top:8px;">
                            <button onclick="modalUpdateDate(<?= $e['id'] ?>, '<?= addslashes($e['nom']) ?>', '<?= $e['expiration_date'] ? date('Y-m-d', strtotime($e['expiration_date'])) : '' ?>')" class="btn" style="padding:4px 8px; font-size:10px; background:#f8fafc; color:#64748b; border:1px solid #e2e8f0; border-radius:6px;" title="Modifier l'expiration">
                                <i class="fa-solid fa-calendar-days"></i> Modif.
                            </button>
                        </div>
                    </td>
                    <td>
                        <?php if(!empty($verificationMapping[$e['id']])): ?>
                            <span style="color:#10b981; font-weight:700; background:#dcfce7; padding:4px 8px; border-radius:6px; font-size:11px; white-space:nowrap;">
                                <i class="fa-solid fa-circle-check"></i> Activé
                            </span>
                        <?php else: ?>
                            <span style="color:#f59e0b; font-weight:700; background:#fef3c7; padding:4px 8px; border-radius:6px; font-size:11px; white-space:nowrap;">
                                <i class="fa-solid fa-clock"></i> En attente
                            </span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <button class="btn" style="padding:8px 12px; font-size:11px; background:#4F46E5; color:#fff; border:none; border-radius:8px;" onclick='showCompanyInfo(<?= json_encode($e, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>, "<?= $e['plan_nom'] ?? 'Starter' ?>")'>
                            <i class="fa-solid fa-eye"></i> Voir
                        </button>
                    </td>
                    <td>
                        <button class="btn btn-primary" style="padding:8px 12px; font-size:11px;" onclick="confirmImpersonate(<?= $e['id'] ?>, '<?= addslashes($e['nom']) ?>')">
                            <i class="fa-solid fa-user-secret"></i> Mode Support
                        </button>
                    </td>
                    <td>
                        <button class="btn" style="padding:8px 12px; font-size:11px; background:#ef4444; color:#fff; border:none; border-radius:8px;" onclick="confirmDeleteEnterprise(<?= $e['id'] ?>, '<?= addslashes($e['nom']) ?>')">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($entreprises)): ?>
                <tr>
                    <td colspan="12" style="text-align:center; padding:30px; color:#94a3b8;">Aucune entreprise trouvée.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if(isset($totalPages) && $totalPages > 1): ?>
    <div style="display:flex; justify-content:center; gap:8px; margin-top:20px;">
        <?php for($i = 1; $i <= $totalPages; $i++): ?>
            <a href="index.php?page=superadmin_entreprises&p=<?= $i ?>" class="btn <?= (isset($page) && $page == $i) ? 'btn-primary' : '' ?>" style="<?= (isset($page) && $page == $i) ? '' : 'background:#f1f5f9; color:#64748b; border:1px solid #e2e8f0;' ?> padding:6px 12px; border-radius:8px; font-weight:600; text-decoration:none;">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Modal Réinitialisation Pass -->
<div id="resetPassModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.6); z-index:9999; align-items:center; justify-content:center; backdrop-filter: blur(8px);">
    <div class="card" style="width:95%; max-width:400px; padding:35px; border-radius: 24px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);">
        <button onclick="document.getElementById('resetPassModal').style.display='none'" style="position:absolute; top:20px; right:20px; border:none; background:#f1f5f9; width: 35px; height: 35px; border-radius: 50%; cursor:pointer; font-size:18px;">&times;</button>
        <h3 class="text-h2" style="margin-bottom:10px;">Réinit. Mot de Passe</h3>
        <p style="font-size:13px; color:#64748b; margin-bottom:25px;">Support pour : <strong id="resetTargetName" style="color:var(--color-primary);"></strong></p>
        
        <form method="POST" action="index.php?page=superadmin_reset_password">
            <input type="hidden" name="admin_id" id="reset_admin_id">
            <div class="form-group" style="margin-bottom:25px;">
                <label>Nouveau mot de passe</label>
                <input type="password" name="new_password" class="form-control" required style="width:100%;" placeholder="Minimum 6 caractères">
            </div>
            <div style="display:flex; gap:12px;">
                <button type="button" onclick="document.getElementById('resetPassModal').style.display='none'" class="btn" style="flex:1; background:#f1f5f9; color:#64748b; border:none;">Annuler</button>
                <button type="submit" class="btn btn-primary" style="flex:2;">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Modification Expiration -->
<div id="updateDateModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.6); z-index:9999; align-items:center; justify-content:center; backdrop-filter: blur(8px);">
    <div class="card" style="width:95%; max-width:400px; padding:35px; border-radius: 24px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);">
        <button onclick="document.getElementById('updateDateModal').style.display='none'" style="position:absolute; top:20px; right:20px; border:none; background:#f1f5f9; width: 35px; height: 35px; border-radius: 50%; cursor:pointer; font-size:18px;">&times;</button>
        <h3 class="text-h2" style="margin-bottom:10px;">Modifier Expiration</h3>
        <p style="font-size:13px; color:#64748b; margin-bottom:25px;">Entreprise : <strong id="updateDateTargetName" style="color:var(--color-primary);"></strong></p>
        
        <form method="POST" action="index.php?page=superadmin_update_expiration">
            <input type="hidden" name="entreprise_id" id="update_date_entreprise_id">
            <div class="form-group" style="margin-bottom:25px;">
                <label>Nouvelle date d'expiration</label>
                <input type="date" name="new_expiration_date" id="update_new_date" class="form-control" style="width:100%;">
                <small style="color:#94a3b8; display:block; margin-top:5px;">Laissez vide pour une validité illimitée.</small>
            </div>
            <div style="display:flex; gap:12px;">
                <button type="button" onclick="document.getElementById('updateDateModal').style.display='none'" class="btn" style="flex:1; background:#f1f5f9; color:#64748b; border:none;">Annuler</button>
                <button type="submit" class="btn btn-primary" style="flex:2;">Confirmer</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Modification Plan -->
<div id="updatePlanModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.6); z-index:9999; align-items:center; justify-content:center; backdrop-filter: blur(8px);">
    <div class="card" style="width:95%; max-width:400px; padding:35px; border-radius: 24px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);">
        <button onclick="document.getElementById('updatePlanModal').style.display='none'" style="position:absolute; top:20px; right:20px; border:none; background:#f1f5f9; width: 35px; height: 35px; border-radius: 50%; cursor:pointer; font-size:18px;">&times;</button>
        <h3 class="text-h2" style="margin-bottom:10px;">Changer de Plan</h3>
        <p style="font-size:13px; color:#64748b; margin-bottom:25px;">Entreprise : <strong id="updatePlanTargetName" style="color:var(--color-primary);"></strong></p>
        
        <form method="POST" action="index.php?page=superadmin_update_plan">
            <input type="hidden" name="entreprise_id" id="update_plan_entreprise_id">
            <div class="form-group" style="margin-bottom:15px;">
                <label>Choisir le nouveau plan</label>
                <select name="plan_id" id="update_new_plan" class="form-control" style="width:100%;" required>
                    <?php foreach($allPlans as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:25px;">
                <label>Nouvelle expiration (Optionnel)</label>
                <input type="date" name="new_expiration_date" class="form-control" style="width:100%;">
                <small style="color:#94a3b8; display:block; margin-top:5px;">Laissez vide pour conserver la date actuelle.</small>
            </div>
            <div style="display:flex; gap:12px;">
                <button type="button" onclick="document.getElementById('updatePlanModal').style.display='none'" class="btn" style="flex:1; background:#f1f5f9; color:#64748b; border:none;">Annuler</button>
                <button type="submit" class="btn btn-primary" style="flex:2;">Appliquer</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Info Entreprise -->
<div id="infoModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.6); z-index:9999; align-items:center; justify-content:center; backdrop-filter: blur(8px);">
    <div class="card" style="width:95%; max-width:600px; padding:0; border-radius: 24px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); overflow:hidden; border:none;">
        <div style="background: linear-gradient(135deg, #4F46E5 0%, #3730A3 100%); padding: 30px; color: white; position: relative;">
            <button onclick="document.getElementById('infoModal').style.display='none'" style="position:absolute; top:20px; right:20px; border:none; background:rgba(255,255,255,0.2); color:white; width: 35px; height: 35px; border-radius: 50%; cursor:pointer; font-size:18px; display:flex; align-items:center; justify-content:center;">&times;</button>
            <h3 style="margin:0; font-size: 24px; font-weight: 800; color:#fff;" id="info_nom_entreprise">Nom Entreprise</h3>
            <p style="margin: 5px 0 0; opacity: 0.8; font-size: 14px; color:#fff;" id="info_secteur">Secteur d'activité</p>
        </div>
        
        <div style="padding: 30px; max-height: 70vh; overflow-y: auto; background:#fff;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px;">
                <div>
                    <label style="display:block; font-size:11px; text-transform:uppercase; color:#94a3b8; font-weight:700; margin-bottom:5px;">Responsable</label>
                    <div style="font-weight:600; color:#1e293b;" id="info_responsable">...</div>
                </div>
                <div>
                    <label style="display:block; font-size:11px; text-transform:uppercase; color:#94a3b8; font-weight:700; margin-bottom:5px;">Email de contact</label>
                    <div style="font-weight:600; color:#1e293b;" id="info_email">...</div>
                </div>
                <div>
                    <label style="display:block; font-size:11px; text-transform:uppercase; color:#94a3b8; font-weight:700; margin-bottom:5px;">Téléphone</label>
                    <div style="font-weight:600; color:#1e293b;" id="info_tel">...</div>
                </div>
                <div>
                    <label style="display:block; font-size:11px; text-transform:uppercase; color:#94a3b8; font-weight:700; margin-bottom:5px;">Localisation</label>
                    <div style="font-weight:600; color:#1e293b;" id="info_lieu">...</div>
                </div>
            </div>

            <div style="margin-top: 25px; padding-top: 20px; border-top: 1px solid #f1f5f9;">
                <label style="display:block; font-size:11px; text-transform:uppercase; color:#94a3b8; font-weight:700; margin-bottom:5px;">Adresse exacte</label>
                <div style="font-weight:600; color:#1e293b;" id="info_adresse">...</div>
            </div>

            <div style="margin-top: 25px; display: grid; grid-template-columns: 1fr 1fr; gap: 25px; padding: 20px; background: #f8fafc; border-radius: 16px;">
                <div>
                    <label style="display:block; font-size:11px; text-transform:uppercase; color:#94a3b8; font-weight:700; margin-bottom:5px;">Créé le</label>
                    <div style="font-weight:700; color:#475569;" id="info_creation">...</div>
                </div>
                <div>
                    <label style="display:block; font-size:11px; text-transform:uppercase; color:#94a3b8; font-weight:700; margin-bottom:5px;">Expiration</label>
                    <div style="font-weight:700; color:#475569;" id="info_expiration">...</div>
                </div>
            </div>
        </div>
        
        <div style="padding: 20px 30px; background: #f8fafc; text-align: right; border-top: 1px solid #f1f5f9;">
            <button onclick="document.getElementById('infoModal').style.display='none'" class="btn" style="background:#fff; color:#64748b; border:1px solid #e2e8f0; padding: 10px 25px; font-weight: 700; border-radius:12px;">Fermer</button>
        </div>
    </div>
</div>

<script>
function modalResetPass(id, name) {
    document.getElementById('reset_admin_id').value = id;
    document.getElementById('resetTargetName').innerText = name;
    document.getElementById('resetPassModal').style.display = 'flex';
}

function modalUpdateDate(id, name, currentDate) {
    document.getElementById('update_date_entreprise_id').value = id;
    document.getElementById('updateDateTargetName').innerText = name;
    document.getElementById('update_new_date').value = currentDate;
    document.getElementById('updateDateModal').style.display = 'flex';
}

function modalUpdatePlan(id, name, currentPlanId) {
    document.getElementById('update_plan_entreprise_id').value = id;
    document.getElementById('updatePlanTargetName').innerText = name;
    document.getElementById('update_new_plan').value = currentPlanId;
    document.getElementById('updatePlanModal').style.display = 'flex';
}

function confirmImpersonate(id, name) {
    Swal.fire({
        title: 'Entrer en Mode Support ?',
        html: `Vous allez basculer sur l'interface de l'entreprise <b>${name}</b>.<br><br><small style="color:#64748b;">Une notification de sécurité sera enregistrée.</small>`,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Oui, basculer',
        cancelButtonText: 'Annuler',
        confirmButtonColor: '#4F46E5',
        customClass: {
            popup: 'swal2-modern-popup'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `index.php?page=superadmin_impersonate&id=${id}`;
        }
    });
}

function confirmDeleteEnterprise(id, name) {
    Swal.fire({
        title: 'Supprimer définitivement ?',
        html: `<div style="text-align:center;">
                <p>Vous êtes sur le point de supprimer <b>${name}</b>.</p>
                <div style="background:#fff1f1; border:1px solid #fecaca; padding:15px; border-radius:12px; color:#991b1b; font-size:13px; margin-top:15px;">
                    <i class="fa-solid fa-triangle-exclamation"></i> <b>Attention :</b> Cette action supprimera TOUT (Comptes, Employés, Pointages, Transactions, Sites, Tickets). <br><b>C'est irréversible.</b>
                </div>
               </div>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Oui, TOUT supprimer',
        cancelButtonText: 'Annuler',
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        customClass: {
            popup: 'swal2-modern-popup'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `index.php?page=superadmin_delete_enterprise&id=${id}`;
        }
    });
}

function showCompanyInfo(data, planNom) {
    document.getElementById('info_nom_entreprise').innerText = data.nom;
    document.getElementById('info_secteur').innerText = (data.secteur || 'Non défini') + ' • ' + planNom;
    document.getElementById('info_responsable').innerText = data.nom_responsable || '—';
    document.getElementById('info_email').innerText = data.email || '—';
    document.getElementById('info_tel').innerText = data.telephone || '—';
    document.getElementById('info_lieu').innerText = (data.pays || '—') + ', ' + (data.ville || '—');
    document.getElementById('info_adresse').innerText = data.localisation || 'Non renseignée';
    
    // Dates formatées
    const dateC = data.Created_at ? new Date(data.Created_at).toLocaleDateString('fr-FR', {day:'2-digit', month:'long', year:'numeric'}) : '—';
    const dateE = data.expiration_date ? new Date(data.expiration_date).toLocaleDateString('fr-FR', {day:'2-digit', month:'long', year:'numeric'}) : 'Illimitée';
    
    document.getElementById('info_creation').innerText = dateC;
    document.getElementById('info_expiration').innerText = dateE;
    
    document.getElementById('infoModal').style.display = 'flex';
}

window.onclick = function(event) {
    if (event.target == document.getElementById('resetPassModal')) document.getElementById('resetPassModal').style.display='none';
    if (event.target == document.getElementById('updateDateModal')) document.getElementById('updateDateModal').style.display='none';
    if (event.target == document.getElementById('updatePlanModal')) document.getElementById('updatePlanModal').style.display='none';
    if (event.target == document.getElementById('infoModal')) document.getElementById('infoModal').style.display='none';
}
</script>
