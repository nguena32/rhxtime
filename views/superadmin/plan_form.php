<?php
// views/superadmin/plan_form.php
$isEdit = isset($plan['id']);
?>

<div class="header-actions" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px; flex-wrap:wrap; gap:12px;">
    <div>
        <h2 style="font-weight: 800; color: var(--color-secondary); margin: 0;">
            <i class="fa-solid fa-layer-group" style="color: #4f46e5; margin-right: 8px;"></i>
            <?= $isEdit ? 'Modifier le Forfait' : 'Créer un Forfait' ?>
        </h2>
        <p style="margin: 4px 0 0 0; font-size: 13px; color: #64748b;">Définissez les caractéristiques et les limites du plan.</p>
    </div>
    <a href="index.php?page=superadmin_plans" class="btn btn-outline" style="font-size:13px; border-radius:10px;">
        <i class="fa-solid fa-arrow-left"></i> Retour à la liste
    </a>
</div>

<div class="card" style="border-radius:15px; max-width:800px; margin:0 auto;">
    <form method="POST" action="index.php?page=superadmin_plan_save">
        <input type="hidden" name="id" value="<?= $plan['id'] ?? '' ?>">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
            <div class="form-group" style="grid-column: span 2;">
                <label>Nom du Forfait</label>
                <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($plan['nom'] ?? '') ?>" placeholder="ex: Pro, Entreprise, Starter..." required>
            </div>

            <div class="form-group">
                <label>Montant Mensuel (FCFA)</label>
                <input type="number" name="montant_mensuel" class="form-control" value="<?= $plan['montant_mensuel'] ?? 0 ?>" required>
            </div>

            <div class="form-group">
                <label>Montant Mensuel - Payé Annuellement (FCFA)</label>
                <input type="number" name="montant_annuel_mensualise" class="form-control" value="<?= $plan['montant_annuel_mensualise'] ?? 0 ?>" required>
                <small style="color:#64748b; font-size:11px;">Ex: 1 900 FCFA/mois au lieu de 3 500 FCFA.</small>
            </div>

            <div class="form-group">
                <label>Max Employés</label>
                <input type="number" name="max_employees" class="form-control" value="<?= $plan['max_employees'] ?? 10 ?>" required>
            </div>

            <div class="form-group">
                <label>Max Sites</label>
                <input type="number" name="max_sites" class="form-control" value="<?= $plan['max_sites'] ?? 1 ?>" required>
            </div>

            <div class="form-group">
                <label>Max Managers / RH</label>
                <input type="number" name="max_managers" class="form-control" value="<?= $plan['max_managers'] ?? 1 ?>" required>
            </div>

            <div class="form-group">
                <label>Jours d'Essai Gratuit</label>
                <input type="number" name="trial_days" class="form-control" value="<?= $plan['trial_days'] ?? 14 ?>" required>
            </div>

            <div class="form-group">
                <label>Type de Support</label>
                <select name="support_type" class="form-control">
                    <option value="normal" <?= ($plan['support_type'] ?? '') === 'normal' ? 'selected' : '' ?>>Normal</option>
                    <option value="prioritaire" <?= ($plan['support_type'] ?? '') === 'prioritaire' ? 'selected' : '' ?>>Prioritaire</option>
                </select>
            </div>

            <div style="grid-column: span 2; margin-top:20px; padding:20px; border:1px solid #e2e8f0; border-radius:12px; background:#f8fafc;">
                <h3 style="margin:0 0 15px 0; font-size:16px; color:var(--color-secondary);"><i class="fa-solid fa-list-check"></i> Fonctionnalités à la carte</h3>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; font-size:14px;">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                        <input type="checkbox" name="has_qr_code" <?= ($plan['has_qr_code'] ?? 1) ? 'checked' : '' ?> style="width:18px; height:18px;">
                        Pointage QR Code
                    </label>
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                        <input type="checkbox" name="has_direct_scan" <?= ($plan['has_direct_scan'] ?? 1) ? 'checked' : '' ?> style="width:18px; height:18px;">
                        Pointage direct (One-Tap)
                    </label>
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                        <input type="checkbox" name="has_gps_precision" <?= ($plan['has_gps_precision'] ?? 1) ? 'checked' : '' ?> style="width:18px; height:18px;">
                        Vérification GPS Haute Précision
                    </label>
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                        <input type="checkbox" name="has_advanced_dashboard" <?= ($plan['has_advanced_dashboard'] ?? 0) ? 'checked' : '' ?> style="width:18px; height:18px;">
                        Tableau de bord avancé
                    </label>
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                        <input type="checkbox" name="has_payroll_mgmt" <?= ($plan['has_payroll_mgmt'] ?? 0) ? 'checked' : '' ?> style="width:18px; height:18px;">
                        Gestion de la paie
                    </label>
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                        <input type="checkbox" name="has_auto_payroll" <?= ($plan['has_auto_payroll'] ?? 0) ? 'checked' : '' ?> style="width:18px; height:18px;">
                        Calcul de paie automatique
                    </label>
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                        <input type="checkbox" name="has_pdf_export" <?= ($plan['has_pdf_export'] ?? 0) ? 'checked' : '' ?> style="width:18px; height:18px;">
                        Export PDF bulletin de paie
                    </label>
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                        <input type="checkbox" name="has_unlimited_history" <?= ($plan['has_unlimited_history'] ?? 0) ? 'checked' : '' ?> style="width:18px; height:18px;">
                        Historique de présence illimité
                    </label>
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                        <input type="checkbox" name="has_web_mobile" <?= ($plan['has_web_mobile'] ?? 1) ? 'checked' : '' ?> style="width:18px; height:18px;">
                        Accès Web & Mobile
                    </label>
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                        <input type="checkbox" name="has_email_alerts" <?= ($plan['has_email_alerts'] ?? 0) ? 'checked' : '' ?> style="width:18px; height:18px;">
                        Alertes e-mail
                    </label>
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                        <input type="checkbox" name="has_messaging" <?= ($plan['has_messaging'] ?? 1) ? 'checked' : '' ?> style="width:18px; height:18px;">
                        Messagerie interne
                    </label>
                </div>
            </div>
        </div>

        <div style="margin-top:30px; display:flex; gap:10px;">
            <button type="submit" class="btn btn-primary" style="flex:1; padding:12px;">
                <i class="fa-solid fa-save"></i> <?= $isEdit ? 'Mettre à jour' : 'Enregistrer le forfait' ?>
            </button>
            <a href="index.php?page=superadmin_plans" class="btn btn-outline" style="flex:1; padding:12px; text-align:center;">Annuler</a>
        </div>
    </form>
</div>
