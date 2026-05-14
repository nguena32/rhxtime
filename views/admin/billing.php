<div class="card" style="margin-bottom:20px; border-left: 5px solid #4f46e5;">
    <h2 style="margin-bottom:10px;"><i class="fa-solid fa-gem" style="color:#4f46e5;"></i> Mon Abonnement Actuel</h2>
    <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:20px;">
        <div>
            <p style="margin-bottom:5px;">Entreprise : <strong><?= htmlspecialchars($entreprise['nom']) ?></strong></p>
            <p style="margin-bottom:5px;">Plan Actif : <span class="badge badge-primary" style="font-size:14px;"><?= htmlspecialchars($entreprise['plan_nom'] ?? 'Starter') ?></span></p>
            <p style="margin-bottom:5px;">Statut : 
                <span class="badge" style="background:<?= ($entreprise['statut'] ?? 'Trial') === 'Trial' ? '#fef3c7; color:#92400e;' : '#dcfce7; color:#16a34a;' ?>">
                    <?= ($entreprise['statut'] ?? 'Trial') === 'Trial' ? 'Période d\'essai' : 'Actif' ?>
                </span>
            </p>
            <p>
                Expiration : 
                <?php 
                $daysRemaining = 0;
                if($entreprise['expiration_date']) {
                    $daysRemaining = ceil((strtotime($entreprise['expiration_date']) - time()) / 86400);
                }
                
                if($entreprise['expiration_date'] && strtotime($entreprise['expiration_date']) < time()): ?>
                    <strong style="color:var(--color-danger);">Expiré le <?= date('d/m/Y', strtotime($entreprise['expiration_date'])) ?></strong>
                <?php else: ?>
                    <strong style="color:var(--color-success);"><?= $entreprise['expiration_date'] ? date('d/m/Y', strtotime($entreprise['expiration_date'])) : 'Non défini' ?></strong>
                    <?php if($daysRemaining > 0): ?>
                        <span style="font-size:12px; color:#64748b; margin-left:10px;">(Il reste <?= $daysRemaining ?> jours)</span>
                    <?php endif; ?>
                <?php endif; ?>
            </p>
        </div>
        
        <div style="background:#f8fafc; border-radius:12px; padding:15px; border:1px solid #e2e8f0; min-width:250px;">
            <h4 style="margin:0 0 10px 0; font-size:13px; text-transform:uppercase; color:#64748b;">Quotas du Forfait</h4>
            <div style="display:grid; grid-template-columns: 1fr auto; gap:8px; font-size:14px;">
                <span>Employés max :</span> <strong><?= $entreprise['max_employees'] ?? 3 ?></strong>
                <span>Sites max :</span> <strong><?= $entreprise['max_sites'] ?? 1 ?></strong>
                <span>Managers max :</span> <strong><?= $entreprise['max_managers'] ?? 1 ?></strong>
                <span>Support :</span> <strong style="color:#4f46e5;"><?= ucfirst($entreprise['support_type'] ?? 'Normal') ?></strong>
            </div>
        </div>

        <div style="text-align:right;">
            <a href="index.php?page=landing#pricing-grid" class="btn btn-warning" style="font-size:16px; padding:12px 24px; border-radius:10px; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3); margin-bottom:15px; display:block;">
                <i class="fa-solid fa-arrow-up"></i> Mettre à Niveau
            </a>
            
            <form action="index.php?page=admin_apply_promo_code" method="POST" style="background:#f1f5f9; padding:10px; border-radius:10px; display:flex; gap:10px; border:1px solid #e2e8f0;">
                <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken() ?>">
                <input type="text" name="promo_code" placeholder="Code Promo" required style="border:1px solid #cbd5e1; border-radius:6px; padding:6px 10px; width:120px; text-transform:uppercase; font-size:13px;">
                <button type="submit" class="btn btn-primary" style="padding:6px 12px; font-size:13px;"><i class="fa-solid fa-gift"></i></button>
            </form>
        </div>
    </div>
</div>

<div class="card" style="margin-bottom:20px; border-left: 5px solid var(--color-primary);">
    <h3 style="margin-bottom:20px;"><i class="fa-solid fa-building-user"></i> Informations de l'Entreprise</h3>
    <form action="index.php?page=admin_update_enterprise" method="POST">
        <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken() ?>">
        
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:20px;">
            <div class="form-group">
                <label style="font-weight:600; color:var(--slate); margin-bottom:5px; display:block;">Nom de l'entreprise</label>
                <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($entreprise['nom'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label style="font-weight:600; color:var(--slate); margin-bottom:5px; display:block;">Nom du responsable</label>
                <input type="text" name="nom_responsable" class="form-control" value="<?= htmlspecialchars($entreprise['nom_responsable'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label style="font-weight:600; color:var(--slate); margin-bottom:5px; display:block;">Numéro de Tel</label>
                <input type="text" name="telephone" class="form-control" value="<?= htmlspecialchars($entreprise['telephone'] ?? '') ?>" required>
            </div>
        </div>
        
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:20px; margin-top:15px;">
            <div class="form-group">
                <label style="font-weight:600; color:var(--slate); margin-bottom:5px; display:block;">Ville</label>
                <input type="text" name="ville" class="form-control" value="<?= htmlspecialchars($entreprise['ville'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label style="font-weight:600; color:var(--slate); margin-bottom:5px; display:block;">Localisation (Quartier/Rue)</label>
                <input type="text" name="localisation" class="form-control" value="<?= htmlspecialchars($entreprise['localisation'] ?? '') ?>" required>
            </div>
        </div>

        <div style="margin-top:20px; display:flex; justify-content: flex-end;">
            <button type="submit" class="btn btn-primary" style="padding:10px 25px; border-radius:10px; font-weight:700;">
                <i class="fa-solid fa-save"></i> Enregistrer les modifications
            </button>
        </div>
    </form>
</div>

<div class="card">
    <h3>Historique des Transactions</h3>
    <div class="table-responsive" style="margin-top:15px;">
        <table class="table">
            <thead>
                <tr>
                    <th>Numéro Transaction</th>
                    <th>Date</th>
                    <th>Montant</th>
                    <th>Plan</th>
                    <th>Statut</th>
                    <th>Facture</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($transactions as $t): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($t['transaction_id']) ?></strong></td>
                    <td><?= date('d/m/Y H:i', strtotime($t['date_paiement'])) ?></td>
                    <td><?= number_format($t['montant'], 0, ',', ' ') ?> FCFA</td>
                    <td><?= htmlspecialchars($t['plan_choisi'] ?? 'N/A') ?></td>
                    <td>
                        <?php if($t['statut'] === 'ACCEPTED'): ?>
                            <span class="badge badge-success">Accepté</span>
                        <?php elseif($t['statut'] === 'PENDING'): ?>
                            <span class="badge badge-warning">En attente</span>
                        <?php else: ?>
                            <span class="badge badge-danger"><?= htmlspecialchars($t['statut']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($t['statut'] === 'ACCEPTED'): ?>
                            <a href="index.php?page=download_invoice&transaction_id=<?= urlencode($t['transaction_id']) ?>" target="_blank" class="btn btn-sm btn-outline"><i class="fa-solid fa-file-pdf"></i> Reçu PDF</a>
                        <?php else: ?>
                            <span style="color:#999;font-size:12px;">N/A</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($transactions)): ?>
                <tr>
                    <td colspan="6" style="text-align:center;">Aucune transaction enregistrée.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination Transactions -->
    <?php if(isset($totalPages) && $totalPages > 1): ?>
    <div style="display:flex; justify-content:center; gap:8px; margin-top:20px;">
        <?php for($i = 1; $i <= $totalPages; $i++): ?>
            <a href="index.php?page=admin_billing&p=<?= $i ?>" class="btn <?= (isset($page) && $page == $i) ? 'btn-primary' : '' ?>" style="<?= (isset($page) && $page == $i) ? '' : 'background:#f1f5f9; color:#64748b; border:1px solid #e2e8f0;' ?> padding:6px 12px; border-radius:8px; font-weight:600; text-decoration:none; font-size:12px;">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<!-- SECTION UPGRADE HARMONISÉE -->
<style>
/* Styles de la grille tarifaire extraits de landing.php */
.pricing-section-admin {
  background: linear-gradient(135deg, #0f172a 0%, #0f4c81 100%);
  padding: 40px 20px;
  border-radius: 20px;
  margin-top: 30px;
  color: #fff;
}
.pricing-grid-admin {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 24px;
  margin-top: 30px;
}
.plan-card-admin {
  background: rgba(255,255,255,.08);
  border: 1px solid rgba(255,255,255,.15);
  border-radius: 20px;
  padding: 32px;
  position: relative;
  transition: all .3s;
  color: #fff;
  display: flex;
  flex-direction: column;
}
.plan-card-admin:hover { background: rgba(255,255,255,.12); transform: translateY(-4px); }
.plan-card-admin.featured {
  background: #fff;
  border-color: #f97316;
  color: #0f172a;
  box-shadow: 0 20px 50px rgba(249,115,22,.3);
}
.featured-tag-admin {
  position: absolute;
  top: -14px;
  left: 50%;
  transform: translateX(-50%);
  background: linear-gradient(90deg, #f97316, #ea580c);
  color: #fff;
  padding: 5px 18px;
  border-radius: 50px;
  font-size: .78rem;
  font-weight: 800;
  white-space: nowrap;
  z-index: 10;
}
.plan-name-admin { font-size: 1.2rem; font-weight: 800; margin-bottom: 4px; }
.plan-card-admin.featured .plan-name-admin { color: #0f172a; }
.plan-sub-admin { font-size: .82rem; color: rgba(255,255,255,.55); margin-bottom: 20px; }
.plan-card-admin.featured .plan-sub-admin { color: #64748b; }
.plan-price-admin {
  font-size: 2rem;
  font-weight: 900;
  margin-bottom: 4px;
  line-height: 1;
}
.plan-card-admin.featured .plan-price-admin { color: #0f172a; }
.plan-period-admin { font-size: .85rem; color: rgba(255,255,255,.5); margin-bottom: 24px; }
.plan-card-admin.featured .plan-period-admin { color: #64748b; }
.plan-feats-admin { list-style: none; margin-bottom: 28px; display: flex; flex-direction: column; gap: 10px; padding: 0; flex-grow: 1; }
.plan-feat-item-admin { font-size: .88rem; color: rgba(255,255,255,.75); display: flex; align-items: center; gap: 10px; }
.plan-card-admin.featured .plan-feat-item-admin { color: #475569; }
.plan-feat-item-admin .fa-check { color: #10b981; flex-shrink: 0; }
.plan-feat-item-admin .fa-xmark { color: #ef4444; flex-shrink: 0; width: 14px; text-align: center; }

.billing-toggle-admin {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 14px;
  margin-top: 10px;
}
.billing-label-admin {
  color: rgba(255,255,255,.5);
  font-weight: 600;
  font-size: .95rem;
  cursor: pointer;
  transition: color .2s;
}
.billing-label-admin.active { color: #fff; }
.saving-tag-admin {
  background: rgba(249,115,22,.2);
  color: #fdba74;
  padding: 2px 8px;
  border-radius: 50px;
  font-size: .75rem;
  font-weight: 700;
  border: 1px solid rgba(249,115,22,.3);
}

/* Toggle Switch Styling */
.toggle-sw-admin { position: relative; display: inline-block; width: 48px; height: 24px; cursor: pointer; }
.toggle-sw-admin input { opacity: 0; width: 0; height: 0; }
.toggle-track-admin {
  position: absolute; inset: 0;
  background: rgba(255,255,255,.2);
  border-radius: 50px;
  transition: .3s;
}
.toggle-track-admin::before {
  content: '';
  position: absolute;
  width: 18px; height: 18px;
  background: #fff; border-radius: 50%;
  left: 3px; top: 3px;
  transition: .3s;
}
.toggle-sw-admin input:checked + .toggle-track-admin { background: #f97316; }
.toggle-sw-admin input:checked + .toggle-track-admin::before { transform: translateX(24px); }

/* Boutons */
.btn-upgrade-rhx {
  background: linear-gradient(135deg, #f97316, #ea580c);
  color: #fff !important;
  border: none;
  padding: 14px 24px;
  border-radius: 12px;
  font-weight: 800;
  font-size: .95rem;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  transition: all .3s;
  text-decoration: none;
  box-shadow: 0 10px 20px rgba(249,115,22,.2);
}
.btn-upgrade-rhx:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(249,115,22,.3); }
.btn-upgrade-outline {
  background: rgba(255,255,255,0.1);
  color: #fff !important;
  border: 1px solid rgba(255,255,255,0.3);
  padding: 14px 24px;
  border-radius: 12px;
  font-weight: 700;
  font-size: .95rem;
  text-align: center;
  text-decoration: none;
  transition: all 0.3s;
}
.btn-upgrade-outline:hover { background: rgba(255,255,255,0.2); border-color: #fff; }
</style>

<div class="pricing-section-admin">
    <div style="text-align:center; margin-bottom:30px;">
        <h2 style="color:#fff; margin-bottom:10px;">Boostez votre productivité</h2>
        <p style="color:rgba(255,255,255,.7); max-width:600px; margin:0 auto;">
            Choisissez le plan qui correspond le mieux à l'évolution de votre entreprise. 
            Mise à jour instantanée après paiement.
        </p>
        
        <div class="billing-toggle-admin">
            <span class="billing-label-admin active" id="lbl-m-admin">Mensuel</span>
            <label class="toggle-sw-admin">
                <input type="checkbox" id="billingTglAdmin">
                <div class="toggle-track-admin"></div>
            </label>
            <span class="billing-label-admin" id="lbl-a-admin">Annuel <span class="saving-tag-admin">-20%</span></span>
        </div>
    </div>

    <div class="pricing-grid-admin">
        <?php 
        $idx = 0; 
        foreach($allPlans as $p): 
            if($p['id'] == $entreprise['plan_id']) continue; // Ne pas afficher le plan actuel dans les options d'upgrade
            
            $pm = (float)($p['montant_mensuel'] ?? 0);
            $pa = (float)($p['montant_annuel_mensualise'] ?? 0);
            $disc = ($pm > 0 && $pa > 0) ? round((1 - $pa / $pm) * 100) : 0;
            $isFeatured = ($idx === 1); 
            $idx++;
        ?>
        <div class="plan-card-admin <?= $isFeatured ? 'featured' : '' ?>" data-pm="<?= $pm ?>" data-pa="<?= $pa ?>">
            <?php if($isFeatured): ?><div class="featured-tag-admin">⭐ RECOMMANDÉ</div><?php endif; ?>
            
            <div class="plan-name-admin"><?= htmlspecialchars($p['nom']) ?></div>
            <div class="plan-sub-admin"><?= $pm > 0 ? 'Pour les équipes en croissance' : 'Essai gratuit' ?></div>
            
            <div class="plan-price-admin plan-price-val-admin">
                <?php if($pm > 0): ?>
                    <span class="pv-admin"><?= number_format($pm, 0, ',', ' ') ?></span><span style="font-size:1.2rem; font-weight:500;"> FCFA</span>
                <?php else: ?>
                    Gratuit
                <?php endif; ?>
            </div>
            <div class="plan-period-admin plan-period-val-admin"><?= $pm > 0 ? '/ Mois' : 'À vie' ?></div>
            
            <?php if($disc > 0): ?>
                <div class="saving-pill-admin" style="display:none; margin-bottom:15px;">
                    <span class="saving-tag-admin" style="background:#10b981; color:#fff; border:none;">
                        Économisez <?= number_format(($pm - $pa) * 12, 0, ',', ' ') ?> FCFA/an
                    </span>
                </div>
            <?php endif; ?>

            <ul class="plan-feats-admin">
                <li class="plan-feat-item-admin"><i class="fa-solid fa-check"></i> <?= $p['max_employees'] ?> employés max</li>
                <li class="plan-feat-item-admin"><i class="fa-solid fa-check"></i> <?= $p['max_sites'] ?> sites inclus</li>
                <li class="plan-feat-item-admin"><i class="fa-solid fa-check"></i> <?= $p['max_managers'] ?> managers inclus</li>
                
                <?php foreach($featureMap as $col => $label): 
                    $included = !empty($p[$col]);
                ?>
                    <li class="plan-feat-item-admin" style="<?= $included ? '' : 'opacity: 0.35;' ?>">
                        <?php if($included): ?>
                            <i class="fa-solid fa-check"></i>
                        <?php else: ?>
                            <i class="fa-solid fa-xmark"></i>
                        <?php endif; ?>
                        <?= $label ?>
                    </li>
                <?php endforeach; ?>
                
                <li class="plan-feat-item-admin"><i class="fa-solid fa-check"></i> Support <?= ($p['support_type'] ?? '') === 'prioritaire' ? 'Prioritaire' : 'Standard' ?></li>
            </ul>

            <form action="index.php?page=subscription_init" method="POST" style="margin-top:auto;">
                <input type="hidden" name="plan_id" value="<?= $p['id'] ?>">
                <input type="hidden" name="billing_cycle" class="cycle-inp-admin" value="mensuel">
                <button type="submit" class="<?= $isFeatured ? 'btn-upgrade-rhx' : 'btn-upgrade-outline' ?>" style="width:100%;">
                    <i class="fa-solid fa-rocket"></i> Passer à ce forfait
                </button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
    
    <p style="text-align:center; color:rgba(255,255,255,.55); margin-top:30px; font-size:.9rem;">
        <i class="fa-solid fa-shield-halved" style="color:#fdba74; margin-right:8px;"></i>
        Paiement sécurisé via <strong>Orange Money</strong>, <strong>MTN MoMo</strong> ou Carte Bancaire.
    </p>
</div>

<script>
// Logic de toggle identique à landing.php
var billingTglAdmin = document.getElementById('billingTglAdmin');
if(billingTglAdmin){
    billingTglAdmin.addEventListener('change', function(){
        var yearly = this.checked;
        document.getElementById('lbl-m-admin').classList.toggle('active', !yearly);
        document.getElementById('lbl-a-admin').classList.toggle('active', yearly);
        
        document.querySelectorAll('.plan-card-admin[data-pm]').forEach(function(c){
            var pm = parseFloat(c.dataset.pm) || 0;
            var pa = parseFloat(c.dataset.pa) || 0;
            if(pm <= 0) return;
            
            var pv = c.querySelector('.pv-admin');
            var pp = c.querySelector('.plan-period-val-admin');
            var sp = c.querySelector('.saving-pill-admin');
            var ci = c.querySelector('.cycle-inp-admin');
            
            if(pv) pv.textContent = (yearly && pa > 0) ? Math.round(pa).toLocaleString('fr-FR') : Math.round(pm).toLocaleString('fr-FR');
            if(pp) pp.textContent = (yearly && pa > 0) ? '/ Mois (facturé annuellement)' : '/ Mois';
            if(sp) sp.style.display = (yearly && pa > 0) ? 'block' : 'none';
            if(ci) ci.value = (yearly && pa > 0) ? 'annuel' : 'mensuel';
        });
    });
}
</script>
