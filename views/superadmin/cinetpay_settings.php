<?php
// views/superadmin/cinetpay_settings.php
// Accessible uniquement au Super-Admin
?>

<!-- Messages de succès / erreur -->
<?php if(isset($_GET['success']) && $_GET['success'] === 'cinetpay_saved'): ?>
<div id="alertSuccess" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color:#fff; padding:16px 20px; border-radius:14px; margin-bottom:25px; display:flex; align-items:center; gap:12px; box-shadow: 0 4px 15px rgba(16,185,129,0.3); animation: slideDown 0.4s ease;">
    <i class="fa-solid fa-circle-check" style="font-size:22px;"></i>
    <div>
        <strong>Configuration sauvegardée !</strong>
        <div style="font-size:13px; opacity:0.9; margin-top:2px;">Les paramètres CinetPay ont été mis à jour avec succès.</div>
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
                'cinetpay_save_failed' => 'Impossible de sauvegarder la configuration. Veuillez réessayer.',
                'missing_fields'       => 'Tous les champs sont obligatoires.',
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
    .cp-card {
        background: #fff;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 10px 15px -3px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }
    .cp-card:hover {
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1), 0 20px 40px -10px rgba(0,0,0,0.08);
        transform: translateY(-2px);
    }
    .cp-header {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 50%, #b45309 100%);
        padding: 30px;
        color: #fff;
        position: relative;
        overflow: hidden;
    }
    .cp-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 200px;
        height: 200px;
        background: rgba(255,255,255,0.08);
        border-radius: 50%;
    }
    .cp-header::after {
        content: '';
        position: absolute;
        bottom: -40%;
        left: -10%;
        width: 150px;
        height: 150px;
        background: rgba(255,255,255,0.05);
        border-radius: 50%;
    }
    .cp-header h2 {
        margin: 0 0 8px 0;
        font-size: 22px;
        font-weight: 800;
        position: relative;
        z-index: 1;
    }
    .cp-header p {
        margin: 0;
        font-size: 14px;
        opacity: 0.85;
        position: relative;
        z-index: 1;
    }
    .cp-body {
        padding: 30px;
    }
    .cp-form-group {
        margin-bottom: 24px;
        position: relative;
    }
    .cp-form-group label {
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
    .cp-form-group label i {
        color: #d97706;
        font-size: 14px;
    }
    .cp-form-group input {
        width: 100%;
        padding: 14px 16px;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        font-size: 15px;
        font-family: 'Inter', monospace;
        color: #1e293b;
        background: #f8fafc;
        transition: all 0.3s ease;
        box-sizing: border-box;
        letter-spacing: 0.5px;
    }
    .cp-form-group input:focus {
        outline: none;
        border-color: #d97706;
        background: #fff;
        box-shadow: 0 0 0 4px rgba(217, 119, 6, 0.1);
    }
    .cp-form-group input::placeholder {
        color: #94a3b8;
        font-family: 'Inter', sans-serif;
        letter-spacing: 0;
    }
    .cp-form-group .input-hint {
        font-size: 12px;
        color: #94a3b8;
        margin-top: 6px;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .cp-actions {
        display: flex;
        gap: 12px;
        padding-top: 10px;
        flex-wrap: wrap;
    }
    .cp-actions .btn-save {
        flex: 1;
        min-width: 200px;
        padding: 14px 24px;
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: #fff;
        border: none;
        border-radius: 12px;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        font-family: 'Inter', sans-serif;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .cp-actions .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(217, 119, 6, 0.4);
    }
    .cp-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 16px;
        margin-bottom: 25px;
    }
    .cp-info-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 20px;
        display: flex;
        align-items: flex-start;
        gap: 14px;
        transition: all 0.3s ease;
    }
    .cp-info-item:hover {
        background: #fff;
        border-color: #d97706;
        box-shadow: 0 4px 12px rgba(217, 119, 6, 0.1);
    }
    .cp-info-item .info-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }
    .cp-info-item .info-content h4 {
        margin: 0 0 4px 0;
        font-size: 14px;
        font-weight: 700;
        color: #1e293b;
    }
    .cp-info-item .info-content p {
        margin: 0;
        font-size: 12px;
        color: #64748b;
        line-height: 1.5;
    }
    .cp-security-notice {
        background: linear-gradient(135deg, #eff6ff, #dbeafe);
        border: 1px solid #93c5fd;
        border-radius: 14px;
        padding: 18px 20px;
        margin-top: 24px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }
    .cp-security-notice i {
        color: #2563eb;
        font-size: 20px;
        margin-top: 2px;
    }
    .cp-security-notice .notice-text {
        font-size: 13px;
        color: #1e40af;
        line-height: 1.6;
    }
    .cp-security-notice .notice-text strong {
        color: #1e3a8a;
    }
    .cp-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
    }
    .cp-status-badge.configured {
        background: #dcfce7;
        color: #16a34a;
    }
    .cp-status-badge.not-configured {
        background: #fee2e2;
        color: #dc2626;
    }
    .key-toggle {
        position: absolute;
        right: 14px;
        top: 42px;
        background: none;
        border: none;
        color: #94a3b8;
        cursor: pointer;
        font-size: 16px;
        padding: 6px;
        transition: color 0.2s;
    }
    .key-toggle:hover {
        color: #475569;
    }
    @media (max-width: 768px) {
        .cp-body { padding: 20px; }
        .cp-header { padding: 24px 20px; }
    }
</style>

<!-- En-tête de page -->
<div class="header-actions" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px; flex-wrap:wrap; gap:12px;">
    <div>
        <h2 style="font-weight: 800; color: var(--color-secondary); margin: 0;">
            <i class="fa-solid fa-credit-card" style="color: #d97706; margin-right: 8px;"></i>
            Configuration CinetPay
        </h2>
        <p style="margin: 4px 0 0 0; font-size: 13px; color: #64748b;">Paramètres de la passerelle de paiement pour les abonnements SaaS</p>
    </div>
    <div style="display:flex; align-items:center; gap:12px;">
        <?php
        $isConfigured = !empty($cinetpaySettings['CINETPAY_API_KEY']) && !empty($cinetpaySettings['CINETPAY_SITE_ID']);
        ?>
        <span class="cp-status-badge <?= $isConfigured ? 'configured' : 'not-configured' ?>">
            <i class="fa-solid <?= $isConfigured ? 'fa-circle-check' : 'fa-circle-xmark' ?>"></i>
            <?= $isConfigured ? 'Configuré' : 'Non configuré' ?>
        </span>
        <a href="index.php?page=superadmin_dashboard" class="btn btn-outline" style="font-size:13px; border-radius:10px;">
            <i class="fa-solid fa-arrow-left"></i> Retour Console
        </a>
    </div>
</div>

<!-- Info Cards -->
<div class="cp-info-grid">
    <div class="cp-info-item">
        <div class="info-icon" style="background: #fef3c7; color: #d97706;">
            <i class="fa-solid fa-money-bill-wave"></i>
        </div>
        <div class="info-content">
            <h4>Paiements Mobile Money</h4>
            <p>Orange Money, MTN MoMo, et autres méthodes de paiement mobile en Afrique Centrale.</p>
        </div>
    </div>
    <div class="cp-info-item">
        <div class="info-icon" style="background: #eef2ff; color: var(--color-primary, #4f46e5);">
            <i class="fa-solid fa-rotate"></i>
        </div>
        <div class="info-content">
            <h4>Renouvellement Automatique</h4>
            <p>Les abonnements Pro et Entreprise sont activés automatiquement après paiement validé.</p>
        </div>
    </div>
    <div class="cp-info-item">
        <div class="info-icon" style="background: #dcfce7; color: #16a34a;">
            <i class="fa-solid fa-shield-halved"></i>
        </div>
        <div class="info-content">
            <h4>Sécurité des Transactions</h4>
            <p>Vérification IPN (Instant Payment Notification) pour chaque transaction CinetPay.</p>
        </div>
    </div>
</div>

<!-- Formulaire CinetPay -->
<div class="cp-card">
    <div class="cp-header">
        <h2><i class="fa-solid fa-wallet" style="margin-right: 10px;"></i> Passerelle CinetPay v2</h2>
        <p>Configurez vos identifiants API pour recevoir les paiements d'abonnement via CinetPay.</p>
    </div>
    <div class="cp-body">
        <form method="POST" action="index.php?page=superadmin_save_cinetpay_settings" id="cinetpayForm">
            <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken() ?>">
            
            <!-- CINETPAY API KEY -->
            <div class="cp-form-group">
                <label><i class="fa-solid fa-key"></i> Clé API (API Key)</label>
                <input type="password" name="cinetpay_api_key" id="cinetpay_api_key"
                       value="<?= htmlspecialchars($cinetpaySettings['CINETPAY_API_KEY'] ?? '') ?>"
                       placeholder="Votre clé API CinetPay"
                       required>
                <button type="button" class="key-toggle" onclick="toggleField('cinetpay_api_key', 'apiKeyIcon')">
                    <i class="fa-solid fa-eye" id="apiKeyIcon"></i>
                </button>
                <div class="input-hint">
                    <i class="fa-solid fa-info-circle"></i>
                    Disponible dans votre tableau de bord CinetPay → Paramètres → Intégration API
                </div>
            </div>
            
            <!-- CINETPAY SITE ID -->
            <div class="cp-form-group">
                <label><i class="fa-solid fa-hashtag"></i> Identifiant du Site (Site ID)</label>
                <input type="password" name="cinetpay_site_id" id="cinetpay_site_id"
                       value="<?= htmlspecialchars($cinetpaySettings['CINETPAY_SITE_ID'] ?? '') ?>"
                       placeholder="Votre Site ID CinetPay"
                       required>
                <button type="button" class="key-toggle" onclick="toggleField('cinetpay_site_id', 'siteIdIcon')">
                    <i class="fa-solid fa-eye" id="siteIdIcon"></i>
                </button>
                <div class="input-hint">
                    <i class="fa-solid fa-info-circle"></i>
                    Identifiant unique de votre site sur CinetPay (numérique)
                </div>
            </div>
            
            <!-- Actions -->
            <div class="cp-actions">
                <button type="submit" class="btn-save">
                    <i class="fa-solid fa-floppy-disk"></i> Enregistrer la configuration
                </button>
            </div>
        </form>
        
        <!-- Notice informative -->
        <div class="cp-security-notice">
            <i class="fa-solid fa-info-circle"></i>
            <div class="notice-text">
                <strong>Information :</strong> Les clés API sont stockées de manière chiffrée (AES-256) dans la base de données. 
                Pour obtenir vos identifiants, connectez-vous sur <a href="https://app.cinetpay.com" target="_blank" style="color: #2563eb; font-weight: 600;">app.cinetpay.com</a>, 
                puis accédez à <strong>Intégration</strong> → <strong>Paramètres API</strong>.
                Les montants des plans sont définis en <strong>FCFA</strong> (Franc CFA).
            </div>
        </div>

        <!-- Récapitulatif des plans -->
        <div style="margin-top: 24px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 20px;">
            <h4 style="margin: 0 0 16px 0; font-size: 14px; font-weight: 700; color: #334155; text-transform: uppercase; letter-spacing: 0.5px;">
                <i class="fa-solid fa-tags" style="color: #d97706; margin-right: 6px;"></i> Plans d'abonnement actifs
            </h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px;">
                <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; text-align: center;">
                    <div style="font-size: 12px; color: #94a3b8; text-transform: uppercase; font-weight: 600; margin-bottom: 6px;">Starter</div>
                    <div style="font-size: 24px; font-weight: 800; color: #64748b;">Gratuit</div>
                    <div style="font-size: 11px; color: #94a3b8; margin-top: 4px;">Essai 14 jours</div>
                </div>
                <div style="background: #fff; border: 2px solid #f59e0b; border-radius: 10px; padding: 16px; text-align: center; position: relative;">
                    <div style="position: absolute; top: -8px; right: 12px; background: #f59e0b; color: #fff; font-size: 9px; font-weight: 700; padding: 2px 8px; border-radius: 10px;">POPULAIRE</div>
                    <div style="font-size: 12px; color: #d97706; text-transform: uppercase; font-weight: 600; margin-bottom: 6px;">Pro</div>
                    <div style="font-size: 24px; font-weight: 800; color: #1e293b;">15 000 <small style="font-size:12px; font-weight:400;">FCFA</small></div>
                    <div style="font-size: 11px; color: #94a3b8; margin-top: 4px;">/ mois</div>
                </div>
                <div style="background: linear-gradient(135deg, #1e293b, #334155); border: none; border-radius: 10px; padding: 16px; text-align: center;">
                    <div style="font-size: 12px; color: #94a3b8; text-transform: uppercase; font-weight: 600; margin-bottom: 6px;">Entreprise</div>
                    <div style="font-size: 24px; font-weight: 800; color: #fff;">50 000 <small style="font-size:12px; font-weight:400;">FCFA</small></div>
                    <div style="font-size: 11px; color: #94a3b8; margin-top: 4px;">/ mois</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleField(fieldId, iconId) {
    const input = document.getElementById(fieldId);
    const icon = document.getElementById(iconId);
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Auto-dismiss alerts after 6 seconds
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
