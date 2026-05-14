<!-- views/admin/payroll_detail.php -->
<div class="container">
    <a href="index.php?page=admin_payroll&month=<?= $month ?>&year=<?= $year ?>&lieu_id=<?= $lieu_id ?>" style="text-decoration:none; color:grey; font-weight:600;"><i class="fa-solid fa-arrow-left"></i> Retour au rapport global</a>
    
    <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-top:20px; margin-bottom:20px;">
        <div>
            <h1 class="text-h1"><?= Security::html($user['nom'] . ' ' . $user['prenom']) ?></h1>
            <p style="color:grey; font-weight:500;">Fiche de paie détaillée - <?= date('F Y', mktime(0,0,0,$month,1,$year)) ?></p>
        </div>
        <div style="text-align:right;">
            <div style="font-size:28px; font-weight:800; color:#10b981;"><?= number_format($netToPay, 0, ',', ' ') ?> FCFA</div>
            <div style="font-size:12px; color:grey; font-weight:700; text-transform:uppercase;">Net à Payer Final</div>
        </div>
    </div>

    <!-- AJUSTEMENTS MANUELS -->
    <div class="card" style="margin-bottom:20px; border: 1px dashed var(--color-primary); background: #fff;">
        <h3 style="margin-top:0; font-size:16px; color:var(--color-primary); display:flex; align-items:center; gap:10px;">
            <i class="fa-solid fa-pen-to-square"></i> Ajustements Manuels (Bonus/Gains & Déductions)
        </h3>
        
        <?php if(isset($success_adj)): ?>
            <div style="background:#dcfce7; color:#166534; padding:10px; border-radius:8px; margin-bottom:15px; font-size:13px; font-weight:600;">
                <i class="fa-solid fa-check-circle"></i> <?= $success_adj ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="action" value="save_adjustments">
            <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken() ?>">
            <div style="display:grid; grid-template-columns: 1fr 1fr 2fr; gap:15px;">
                <div>
                    <label style="font-size:11px; font-weight:800; color:#64748b; text-transform:uppercase;">Primes / Indemnités (+)</label>
                    <input type="number" name="primes" class="form-control" value="<?= (float)$adjustment['amount_primes'] ?>" step="0.01" style="width:100%; font-weight:700; color:#10b981;">
                </div>
                <div>
                    <label style="font-size:11px; font-weight:800; color:#64748b; text-transform:uppercase;">Autres Retenues (-)</label>
                    <input type="number" name="retenues_manuelles" class="form-control" value="<?= (float)$adjustment['amount_retenues'] ?>" step="0.01" style="width:100%; font-weight:700; color:#ef4444;">
                </div>
                <div>
                    <label style="font-size:11px; font-weight:800; color:#64748b; text-transform:uppercase;">Notes / Description</label>
                    <div style="display:flex; gap:10px;">
                        <input type="text" name="adjustment_description" class="form-control" placeholder="Ex: Prime de panier, avance sur salaire..." value="<?= Security::html($adjustment['description']) ?>" style="flex:1;">
                        <button type="submit" class="btn btn-primary" style="padding:0 20px;">Enregistrer</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- RÉSUMÉ DU CALCUL -->
    <div class="card" style="border-left: 5px solid var(--color-primary); background: #f8fafc;">
        <h3 style="margin-top:0; font-size:16px; color:var(--color-primary);">Résumé du calcul (Fiche de paie)</h3>
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:20px;">
            <div>
                <small style="color:grey; text-transform:uppercase; font-size:11px; font-weight:700;">Salaire de Base</small>
                <div style="font-size:18px; font-weight:700;"><?= number_format($user['salaire_mensuel'], 0, ',', ' ') ?> FCFA</div>
            </div>
            <div>
                <small style="color:grey; text-transform:uppercase; font-size:11px; font-weight:700;">Heures Théoriques</small>
                <div style="font-size:18px; font-weight:700;"><?= number_format($theoreticalHours, 1) ?>h</div>
            </div>
            <div>
                <small style="color:grey; text-transform:uppercase; font-size:11px; font-weight:700;">Taux Horaire</small>
                <div style="font-size:18px; font-weight:700;"><?= number_format($tauxHoraire, 2) ?> FCFA/h</div>
            </div>
            <div style="border-left: 2px solid #eee; padding-left: 20px;">
                <small style="color:#ef4444; text-transform:uppercase; font-size:11px; font-weight:700;">Total Retenue (Retards)</small>
                <div style="font-size:18px; font-weight:700; color:#ef4444;">-<?= number_format($retenue, 0, ',', ' ') ?> FCFA</div>
                <small style="color:grey; font-size:11px;"><?= number_format($totalDelay / 3600, 2) ?>h de retard cumulé</small>
            </div>
        </div>
    </div>

    <div class="card" style="padding:0; overflow-x:auto;">
        <h3 style="padding:20px; margin:0; border-bottom:1px solid #eee;"><i class="fa-solid fa-list-check"></i> Historique des Présences & Pointages</h3>
        <table style="width:100%; border-collapse:collapse;">
            <thead style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                <tr>
                    <th style="padding:15px; text-align:left;">Date</th>
                    <th style="padding:15px; text-align:center;">Planning (Début)</th>
                    <th style="padding:15px; text-align:left;">Détail Pointages (Journée)</th>
                    <th style="padding:15px; text-align:right;">Retard Matin</th>
                    <th style="padding:15px; text-align:right;">Retenue (Est.)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($details as $d): ?>
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:15px; font-weight:600;"><?= date('d/m/Y', strtotime($d['jour'])) ?></td>
                    <td style="padding:15px; text-align:center; color:grey;"><?= in_array($d['prevu'], ['Repos', 'N/A']) ? $d['prevu'] : substr($d['prevu'], 0, 5) ?></td>
                    <td style="padding:15px;">
                        <div style="display:flex; flex-wrap:wrap; gap:5px;">
                            <?php foreach($d['mvts'] as $mvt): ?>
                                <span class="badge <?= $mvt['type'] == 'ARRIVEE' ? 'badge-success' : 'badge-danger' ?>" style="font-size:10px; padding:3px 8px;">
                                    <?= $mvt['type'] == 'ARRIVEE' ? 'ARR' : 'DEP' ?> <?= substr($mvt['heure'], 0, 5) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </td>
                    <td style="padding:15px; text-align:right; font-weight:700; color:<?= $d['retard_sec'] > 0 ? '#ef4444' : '#10b981' ?>;">
                        <?= $d['retard_sec'] > 0 ? '+' . round($d['retard_sec']/60) . ' min' : ( $d['arrivee'] ? 'À l\'heure' : '-' ) ?>
                    </td>
                    <td style="padding:15px; text-align:right; color:#ef4444; font-weight:600;">
                        <?= $d['retard_sec'] > 0 ? '-' . number_format(($d['retard_sec'] / 3600) * $tauxHoraire, 0, ',', ' ') : '0' ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="background:#f8fafc; font-weight:800;">
                    <td colspan="4" style="padding:20px; text-align:right; font-size:16px;">NET À PAYER FINAL :</td>
                    <td style="padding:20px; text-align:right; color:#10b981; font-size:22px;"><?= number_format($netToPay, 0, ',', ' ') ?> FCFA</td>
                </tr>
            </tfoot>
        </table>
    </div>
    
    <div style="display:flex; gap:10px;">
        <button onclick="window.location.href='index.php?page=admin_payroll_bulletin&user_id=<?= $user['id'] ?>&month=<?= $month ?>&year=<?= $year ?>'" class="btn btn-primary" style="margin-top:20px; flex:1; padding:15px;">
            <i class="fa-solid fa-print"></i> GÉNÉRER LE BULLETIN DE PAIE
        </button>
        <button onclick="window.close()" class="btn" style="margin-top:20px; background:#eee; color:#333; padding:15px;">
            Fermer
        </button>
    </div>
</div>
