<?php
// views/superadmin/moneyfusion_settings.php
// Accessible uniquement au Super-Admin
?>

<!-- Messages de succès / erreur -->
<?php if(isset($_GET['success']) && $_GET['success'] === 'moneyfusion_saved'): ?>
<div id="alertSuccess" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color:#fff; padding:16px 20px; border-radius:14px; margin-bottom:25px; display:flex; align-items:center; gap:12px; box-shadow: 0 4px 15px rgba(16,185,129,0.3); animation: slideDown 0.4s ease;">
    <i class="fa-solid fa-circle-check" style="font-size:22px;"></i>
    <div>
        <strong>Configuration sauvegardée !</strong>
        <div style="font-size:13px; opacity:0.9; margin-top:2px;">Les paramètres Money Fusion ont été mis à jour avec succès.</div>
    </div>
    <button onclick="this.parentElement.remove()" style="margin-left:auto; background:none; border:none; color:#fff; font-size:18px; cursor:pointer; opacity:0.7;">&times;</button>
</div>
<?php endif; ?>

<?php if(isset($_GET['error'])): ?>
<div id="alertError" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color:#fff; padding:16px 20px; border-radius:14px; margin-bottom:25px; display:flex; align-items:center; gap:12px; box-shadow: 0 4px 15px rgba(239,68,68,0.3); animation: slideDown 0.4s ease;">
    <i class="fa-solid fa-triangle-exclamation" style="font-size:22px;"></i>
    <div>
        <strong>Erreur</strong>
        <div style="font-size:13px; opacity:0.9; margin-top:2px;">
            <?php
            $errors = [
                'moneyfusion_save_failed' => 'Impossible de sauvegarder la configuration. Veuillez réessayer.',
                'missing_fields'          => 'L\'URL de l\'API est obligatoire.',
            ];
            echo $errors[$_GET['error']] ?? 'Une erreur inconnue est survenue.';
            ?>
        </div>
    </div>
    <button onclick="this.parentElement.remove()" style="margin-left:auto; background:none; border:none; color:#fff; font-size:18px; cursor:pointer; opacity:0.7;">&times;</button>
</div>
<?php endif; ?>

<style>
    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-15px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .mf-card {
        background: #fff;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 10px 15px -3px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }
    .mf-card:hover {
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1), 0 20px 40px -10px rgba(0,0,0,0.08);
        transform: translateY(-2px);
    }
    .mf-header {
        background: linear-gradient(135deg, #06b6d4 0%, #3b82f6 100%);
        padding: 30px;
        color: #fff;
        position: relative;
        overflow: hidden;
    }
    .mf-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 200px;
        height: 200px;
        background: rgba(255,255,255,0.08);
        border-radius: 50%;
    }
    .mf-header h2 {
        margin: 0 0 8px 0;
        font-size: 22px;
        font-weight: 800;
        position: relative;
        z-index: 1;
    }
    .mf-header p {
        margin: 0;
        font-size: 14px;
        opacity: 0.9;
        position: relative;
        z-index: 1;
    }
    .mf-body {
        padding: 30px;
    }
    .mf-form-group {
        margin-bottom: 24px;
        position: relative;
    }
    .mf-form-group label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        font-weight: 700;
        color: #334155;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }
    .mf-form-group label i {
        color: #0891b2;
        font-size: 14px;
    }
    .mf-form-group input {
        width: 100%;
        padding: 14px 16px;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        font-size: 15px;
        font-family: 'Inter', sans-serif;
        color: #1e293b;
        background: #f8fafc;
        transition: all 0.3s ease;
        box-sizing: border-box;
    }
    .mf-form-group input:focus {
        outline: none;
        border-color: #06b6d4;
        background: #fff;
        box-shadow: 0 0 0 4px rgba(6, 182, 212, 0.1);
    }
    .mf-actions {
        display: flex;
        gap: 12px;
        padding-top: 10px;
    }
    .mf-actions .btn-save {
        flex: 1;
        padding: 14px 24px;
        background: linear-gradient(135deg, #06b6d4 0%, #3b82f6 100%);
        color: #fff;
        border: none;
        border-radius: 12px;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .mf-actions .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
    }
    .mf-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 16px;
        margin-bottom: 25px;
    }
    .mf-info-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 20px;
        display: flex;
        align-items: flex-start;
        gap: 14px;
        transition: all 0.3s ease;
    }
    .mf-info-item:hover {
        background: #fff;
        border-color: #06b6d4;
        box-shadow: 0 4px 12px rgba(6, 182, 212, 0.1);
    }
    .mf-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
    }
    .mf-status-badge.configured { background: #dcfce7; color: #16a34a; }
    .mf-status-badge.not-configured { background: #fee2e2; color: #dc2626; }
</style>

<!-- En-tête de page -->
<div class="header-actions" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px; flex-wrap:wrap; gap:12px;">
    <div>
        <h2 style="font-weight: 800; color: var(--color-secondary); margin: 0;">
            <i class="fa-solid fa-file-invoice-dollar" style="color: #06b6d4; margin-right: 8px;"></i>
            Configuration Money Fusion
        </h2>
        <p style="margin: 4px 0 0 0; font-size: 13px; color: #64748b;">Passerelle de paiement Fusion Pay pour les abonnements</p>
    </div>
    <div style="display:flex; align-items:center; gap:12px;">
        <?php $isConfigured = !empty($moneyfusionUrl); ?>
        <span class="mf-status-badge <?= $isConfigured ? 'configured' : 'not-configured' ?>">
            <i class="fa-solid <?= $isConfigured ? 'fa-circle-check' : 'fa-circle-xmark' ?>"></i>
            <?= $isConfigured ? 'Configuré' : 'Non configuré' ?>
        </span>
        <a href="index.php?page=superadmin_dashboard" class="btn btn-outline" style="font-size:13px; border-radius:10px;">
            <i class="fa-solid fa-arrow-left"></i> Retour Console
        </a>
    </div>
</div>

<!-- Info Cards -->
<div class="mf-info-grid">
    <div class="mf-info-item">
        <div class="info-icon" style="background: #ecfeff; color: #0891b2; width:44px; height:44px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:18px;">
            <i class="fa-solid fa-bolt"></i>
        </div>
        <div class="info-content">
            <h4>Paiements Rapides</h4>
            <p>Intégration simplifiée via lien API pour une expérience utilisateur fluide.</p>
        </div>
    </div>
    <div class="mf-info-item">
        <div class="info-icon" style="background: #eff6ff; color: #2563eb; width:44px; height:44px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:18px;">
            <i class="fa-solid fa-globe"></i>
        </div>
        <div class="info-content">
            <h4>Multi-devises</h4>
            <p>Supporte plusieurs méthodes de paiement selon votre configuration application.</p>
        </div>
    </div>
</div>

<!-- Formulaire Money Fusion -->
<div class="mf-card">
    <div class="mf-header">
        <h2><i class="fa-solid fa-link" style="margin-right: 10px;"></i> Lien API Fusion Pay</h2>
        <p>Entrez l'URL de l'API générée dans votre tableau de bord Money Fusion.</p>
    </div>
    <div class="mf-body">
        <form method="POST" action="index.php?page=superadmin_save_moneyfusion_settings">
            <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken() ?>">
            
            <div class="mf-form-group">
                <label><i class="fa-solid fa-link"></i> URL de l'API (API URL)</label>
                <input type="url" name="moneyfusion_api_url" 
                       value="<?= htmlspecialchars($moneyfusionUrl ?? '') ?>"
                       placeholder="https://moneyfusion.net/api/v1/..."
                       required>
                <div style="font-size: 12px; color: #94a3b8; margin-top: 8px;">
                    <i class="fa-solid fa-info-circle"></i>
                    Obtenez ce lien dans <strong>API de paiement</strong> → <strong>Créer une application</strong> sur votre compte Money Fusion.
                </div>
            </div>
            
            <div class="mf-actions">
                <button type="submit" class="btn-save">
                    <i class="fa-solid fa-floppy-disk"></i> Enregistrer la configuration
                </button>
            </div>
        </form>
        
        <div style="background: linear-gradient(135deg, #f0fdfa, #ccfbf1); border: 1px solid #5eead4; border-radius: 14px; padding: 18px 20px; margin-top: 24px; display: flex; gap: 12px;">
            <i class="fa-solid fa-shield-halved" style="color: #0d9488; font-size: 20px;"></i>
            <div style="font-size: 13px; color: #134e4a; line-height: 1.6;">
                <strong>Sécurité IP :</strong> Assurez-vous d'avoir déclaré l'adresse IP de ce serveur dans votre application Money Fusion pour autoriser les requêtes.
            </div>
        </div>
    </div>
</div>
<?php
// Auto-dismiss alerts
?>
<script>
setTimeout(() => {
    const alerts = document.querySelectorAll('#alertSuccess, #alertError');
    alerts.forEach(el => {
        el.style.transition = 'opacity 0.5s, transform 0.5s';
        el.style.opacity = '0';
        el.style.transform = 'translateY(-15px)';
        setTimeout(() => el.remove(), 500);
    });
}, 6000);
</script>
