<div class="header-actions" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px;">
    <h2 style="font-weight: 800; color: var(--color-secondary);">Console</h2>
</div>

<?php if (isset($signatureIssues) && $signatureIssues > 0): ?>
<div class="card" style="margin-bottom:25px; border-left: 5px solid #f59e0b; background: #fffbeb; padding:20px; display:flex; align-items:center; justify-content:space-between; gap:20px; border-radius:15px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
    <div style="display:flex; align-items:center; gap:15px;">
        <div style="background:#f59e0b; color:#fff; width:35px; height:35px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:18px;">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <div>
            <h4 style="margin:0; color:#92400e; font-weight:800;">Alerte de Désynchronisation (Multi-Machine)</h4>
            <p style="margin:0; font-size:13px; color:#b45309;"><strong><?= $signatureIssues ?> licences</strong> ne correspondent pas au SEL de cet ordinateur. Les clients concernés verront une page 403.</p>
        </div>
    </div>
    <a href="index.php?page=superadmin_repair_signatures" class="btn" style="background:#f59e0b; color:#fff; border-radius:10px; font-weight:700; white-space:nowrap;" onclick="return confirm('Voulez-vous synchroniser toutes les licences avec le SEL de cet ordinateur ?');">
        <i class="fa-solid fa-sync"></i> Synchroniser maintenant
    </a>
</div>
<?php endif; ?>

<!-- GRID DES KPIs CRITIQUES -->
<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap:20px; margin-bottom: 30px;">
    
    <!-- 1. MRR -->
    <div class="card" style="padding:20px; background:linear-gradient(135deg, var(--color-primary) 0%, #4338ca 100%); color:#fff; border-radius:15px; border:none; box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.4);">
        <div style="display:flex; justify-content:space-between; align-items:start;">
            <h3 style="margin:0; font-size:14px; text-transform:uppercase; opacity:0.8;">MRR Global</h3>
            <i class="fa-solid fa-money-bill-trend-up" style="font-size:20px; opacity:0.5;"></i>
        </div>
        <h1 style="margin:15px 0; font-size:32px; font-weight:800;"><?= number_format($mrr, 0, ',', ' ') ?> <small style="font-size:14px;">FCFA</small></h1>
        <p style="margin:0; font-size:12px; opacity:0.7;">Chiffre d'Affaire Récurrent Mensuel</p>
    </div>

    <!-- 9. Entreprises Actives -->
    <div class="card" style="padding:20px; border-radius:15px; border:1px solid #e2e8f0;">
        <div style="display:flex; justify-content:space-between; align-items:start;">
            <h3 style="margin:0; font-size:14px; text-transform:uppercase; color:#64748b;">Entreprises Actives</h3>
            <i class="fa-solid fa-building-circle-check" style="font-size:20px; color:var(--color-primary);"></i>
        </div>
        <h1 style="margin:15px 0; font-size:32px; font-weight:800; color:var(--color-secondary);"><?= $nbActives ?> <small style="font-size:14px; font-weight:400; color:#64748b;">/ <?= count($entreprises) ?></small></h1>
        <p style="margin:0; font-size:12px; color:#94a3b8;">Abonnements non-expirés</p>
    </div>

    <!-- 10. Visiteurs Uniques -->
    <div class="card" style="padding:20px; border-radius:15px; border:1px solid #e2e8f0; background: #fdfcf9;">
        <div style="display:flex; justify-content:space-between; align-items:start;">
            <h3 style="margin:0; font-size:14px; text-transform:uppercase; color:#64748b;">Audience (Unique IP)</h3>
            <i class="fa-solid fa-eye" style="font-size:20px; color:#f59e0b;"></i>
        </div>
        <h1 style="margin:15px 0; font-size:32px; font-weight:800; color:var(--color-secondary);"><?= $nbTotalVisitors ?? 0 ?></h1>
        <p style="margin:0; font-size:12px; color:#94a3b8;">Visiteurs uniques (30 derniers jours)</p>
    </div>

    <!-- 4 & 5. DAU / MAU -->
    <div class="card" style="padding:20px; border-radius:15px; border:1px solid #e2e8f0;">
        <div style="display:flex; justify-content:space-between; align-items:start;">
            <h3 style="margin:0; font-size:14px; text-transform:uppercase; color:#64748b;">Engagement (DAU/MAU)</h3>
            <i class="fa-solid fa-users-viewfinder" style="font-size:20px; color:#10b981;"></i>
        </div>
        <div style="display:flex; gap:20px; align-items:baseline;">
            <h1 style="margin:15px 0 5px 0; font-size:32px; font-weight:800; color:var(--color-secondary);"><?= $dau ?></h1>
            <span style="color:#64748b; font-size:14px;">DAU Today</span>
        </div>
        <p style="margin:0; font-size:12px; color:#94a3b8;">MAU mensuel : <strong><?= $mau ?></strong> employés</p>
    </div>

    <!-- 8. Anomalies GPS -->
    <div class="card" style="padding:20px; border-radius:15px; border:1px solid #fee2e2;">
        <div style="display:flex; justify-content:space-between; align-items:start;">
            <h3 style="margin:0; font-size:14px; text-transform:uppercase; color:#991b1b;">Tentatives de Fraude</h3>
            <i class="fa-solid fa-shield-halved" style="font-size:20px; color:#ef4444;"></i>
        </div>
        <h1 style="margin:15px 0; font-size:32px; font-weight:800; color:#ef4444;"><?= $anomalies ?></h1>
        <p style="margin:0; font-size:12px; color:#991b1b; font-weight:600;">Anomalies GPS détectées ce mois</p>
    </div>

</div>

<!-- GRID DES TAUX (CONVERSION / CHURN / ACTIVATION) -->
<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap:20px; margin-bottom: 30px;">
    
    <!-- 1. Taux de Conversion -->
    <div class="card" style="padding:15px; border-radius:12px; background:#f8fafc; border-left: 4px solid #f59e0b;">
        <div style="font-size:12px; color:#64748b; margin-bottom:5px;">TAUX DE CONVERSION</div>
        <div style="font-size:24px; font-weight:800; color:var(--color-secondary);"><?= number_format($conversionRate, 1) ?>%</div>
        <div style="font-size:11px; color:#94a3b8; margin-top:5px;">Visiteurs → Essais gratuits</div>
    </div>

    <!-- 2. Trial-to-Paid -->
    <div class="card" style="padding:15px; border-radius:12px; background:#f8fafc; border-left: 4px solid #10b981;">
        <div style="font-size:12px; color:#64748b; margin-bottom:5px;">TRIAL-TO-PAID</div>
        <div style="font-size:24px; font-weight:800; color:var(--color-secondary);"><?= number_format($trialToPaid, 1) ?>%</div>
        <div style="font-size:11px; color:#94a3b8; margin-top:5px;">Conversion en abonnements payants</div>
    </div>

    <!-- 6. Taux d'Activation -->
    <div class="card" style="padding:15px; border-radius:12px; background:#f8fafc; border-left: 4px solid var(--color-primary);">
        <div style="font-size:12px; color:#64748b; margin-bottom:5px;">TAUX D'ACTIVATION (24H)</div>
        <div style="font-size:24px; font-weight:800; color:var(--color-secondary);"><?= number_format($activationRate, 1) ?>%</div>
        <div style="font-size:11px; color:#94a3b8; margin-top:5px;">Config : 1 Lieu + 1 Employé</div>
    </div>

    <!-- 3. Churn Rate -->
    <div class="card" style="padding:15px; border-radius:12px; background:#f8fafc; border-left: 4px solid #ef4444;">
        <div style="font-size:12px; color:#64748b; margin-bottom:5px;">TAUX DE CHURN</div>
        <div style="font-size:24px; font-weight:800; color:var(--color-secondary);"><?= number_format($churnRate, 1) ?>%</div>
        <div style="font-size:11px; color:#94a3b8; margin-top:5px;">Désabonnements (Forfaits expirés)</div>
    </div>

</div>

<div class="card" style="border-radius:15px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; flex-wrap:wrap; gap:10px;">
        <h3 style="font-weight: 800; color: var(--color-secondary);">Gestion des Entreprises clientes</h3>
        <div style="display:flex; gap:8px; flex-wrap:wrap;">
            <a href="index.php?page=superadmin_cinetpay_settings" class="btn btn-outline" style="font-size:12px; border-color: #d97706; color: #d97706;"><i class="fa-solid fa-credit-card"></i> CinetPay</a>
            <a href="index.php?page=superadmin_email_settings" class="btn btn-outline" style="font-size:12px; border-color: #7c3aed; color: #7c3aed;"><i class="fa-solid fa-envelope-circle-check"></i> Config. Email</a>
            <a href="index.php?page=admin_pub" class="btn btn-outline"><i class="fa-solid fa-bullhorn"></i> Gestion Pub Global</a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Entreprise / Contact</th>
                    <th>Plan</th>
                    <th>Employés</th>
                    <th>Pass Admin</th>
                    <th>Statut Expiration</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($entreprises as $e): 
                    $expired = ($e['expiration_date'] && strtotime($e['expiration_date']) < time());
                ?>
                <tr>
                    <td>#<?= $e['id'] ?></td>
                    <td>
                        <div style="font-weight:700;"><?= htmlspecialchars($e['nom']) ?></div>
                        <div style="font-size:12px; color:#64748b;"><?= htmlspecialchars($e['email']) ?></div>
                    </td>
                    <td><span class="badge <?= ($e['plan_id'] > 1) ? 'badge-primary' : 'badge-warning' ?>" style="font-size:10px;"><?= $e['plan_nom'] ?? 'Starter' ?></span></td>
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
                    </td>
                    <td>
                        <button class="btn btn-primary" style="padding:8px 12px; font-size:11px;" onclick="confirmImpersonate(<?= $e['id'] ?>, '<?= addslashes($e['nom']) ?>')">
                            <i class="fa-solid fa-user-secret"></i> Mode Support
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($entreprises)): ?>
                <tr>
                    <td colspan="7" style="text-align:center; padding:30px; color:#94a3b8;">Aucune entreprise trouvée.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
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

<script>
function modalResetPass(id, name) {
    document.getElementById('reset_admin_id').value = id;
    document.getElementById('resetTargetName').innerText = name;
    document.getElementById('resetPassModal').style.display = 'flex';
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

window.onclick = function(event) {
    if (event.target == document.getElementById('resetPassModal')) document.getElementById('resetPassModal').style.display='none';
}
</script>

