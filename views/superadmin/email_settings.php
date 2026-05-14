<?php
// views/superadmin/email_settings.php
// Accessible uniquement au Super-Admin
?>

<!-- Messages de succès / erreur -->
<?php if(isset($_GET['success']) && $_GET['success'] === 'email_saved'): ?>
<div id="alertSuccess" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color:#fff; padding:16px 20px; border-radius:14px; margin-bottom:25px; display:flex; align-items:center; gap:12px; box-shadow: 0 4px 15px rgba(16,185,129,0.3); animation: slideDown 0.4s ease;">
    <i class="fa-solid fa-circle-check" style="font-size:22px;"></i>
    <div>
        <strong>Configuration sauvegardée !</strong>
        <div style="font-size:13px; opacity:0.9; margin-top:2px;">Les paramètres SMTP ont été mis à jour avec succès.</div>
    </div>
    <button onclick="this.parentElement.remove()" style="margin-left:auto; background:none; border:none; color:#fff; font-size:18px; cursor:pointer; opacity:0.7;">&times;</button>
</div>
<?php endif; ?>

<?php if(isset($_GET['success']) && $_GET['success'] === 'email_test_sent'): ?>
<div id="alertTestSuccess" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color:#fff; padding:16px 20px; border-radius:14px; margin-bottom:25px; display:flex; align-items:center; gap:12px; box-shadow: 0 4px 15px rgba(59,130,246,0.3); animation: slideDown 0.4s ease;">
    <i class="fa-solid fa-paper-plane" style="font-size:22px;"></i>
    <div>
        <strong>Email de test envoyé !</strong>
        <div style="font-size:13px; opacity:0.9; margin-top:2px;">Vérifiez votre boîte de réception pour confirmer la réception.</div>
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
                'email_save_failed' => 'Impossible de sauvegarder la configuration. Veuillez réessayer.',
                'email_test_failed' => 'L\'envoi du mail de test a échoué. Vérifiez vos paramètres SMTP.',
                'missing_fields'    => 'Tous les champs sont obligatoires.',
                'phpmailer_missing' => 'La librairie PHPMailer est absente du serveur. Veuillez l\'installer (dossier vendor) pour activer l\'envoi SMTP.',
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
    @keyframes pulse-glow {
        0%, 100% { box-shadow: 0 0 5px rgba(79, 70, 229, 0.2); }
        50% { box-shadow: 0 0 20px rgba(79, 70, 229, 0.4); }
    }
    .smtp-card {
        background: #fff;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 10px 15px -3px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }
    .smtp-card:hover {
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1), 0 20px 40px -10px rgba(0,0,0,0.08);
        transform: translateY(-2px);
    }
    .smtp-header {
        background: linear-gradient(135deg, var(--color-primary, #4f46e5) 0%, #7c3aed 50%, #a855f7 100%);
        padding: 30px;
        color: #fff;
        position: relative;
        overflow: hidden;
    }
    .smtp-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 200px;
        height: 200px;
        background: rgba(255,255,255,0.08);
        border-radius: 50%;
    }
    .smtp-header::after {
        content: '';
        position: absolute;
        bottom: -40%;
        left: -10%;
        width: 150px;
        height: 150px;
        background: rgba(255,255,255,0.05);
        border-radius: 50%;
    }
    .smtp-header h2 {
        margin: 0 0 8px 0;
        font-size: 22px;
        font-weight: 800;
        position: relative;
        z-index: 1;
    }
    .smtp-header p {
        margin: 0;
        font-size: 14px;
        opacity: 0.85;
        position: relative;
        z-index: 1;
    }
    .smtp-body {
        padding: 30px;
    }
    .smtp-form-group {
        margin-bottom: 24px;
        position: relative;
    }
    .smtp-form-group label {
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
    .smtp-form-group label i {
        color: var(--color-primary, #4f46e5);
        font-size: 14px;
    }
    .smtp-form-group input {
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
    .smtp-form-group input:focus {
        outline: none;
        border-color: var(--color-primary, #4f46e5);
        background: #fff;
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
    }
    .smtp-form-group input::placeholder {
        color: #94a3b8;
    }
    .smtp-form-group .input-hint {
        font-size: 12px;
        color: #94a3b8;
        margin-top: 6px;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .smtp-actions {
        display: flex;
        gap: 12px;
        padding-top: 10px;
        flex-wrap: wrap;
    }
    .smtp-actions .btn-save {
        flex: 2;
        min-width: 180px;
        padding: 14px 24px;
        background: linear-gradient(135deg, var(--color-primary, #4f46e5) 0%, #7c3aed 100%);
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
    .smtp-actions .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(79, 70, 229, 0.4);
    }
    .smtp-actions .btn-test {
        flex: 1;
        min-width: 140px;
        padding: 14px 24px;
        background: #f1f5f9;
        color: #475569;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        font-family: 'Inter', sans-serif;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .smtp-actions .btn-test:hover {
        background: #e2e8f0;
        border-color: #cbd5e1;
        transform: translateY(-2px);
    }
    .smtp-security-notice {
        background: linear-gradient(135deg, #fefce8, #fef9c3);
        border: 1px solid #fde68a;
        border-radius: 14px;
        padding: 18px 20px;
        margin-top: 24px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }
    .smtp-security-notice i {
        color: #d97706;
        font-size: 20px;
        margin-top: 2px;
    }
    .smtp-security-notice .notice-text {
        font-size: 13px;
        color: #92400e;
        line-height: 1.6;
    }
    .smtp-security-notice .notice-text strong {
        color: #78350f;
    }
    .smtp-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 16px;
        margin-bottom: 25px;
    }
    .smtp-info-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 20px;
        display: flex;
        align-items: flex-start;
        gap: 14px;
        transition: all 0.3s ease;
    }
    .smtp-info-item:hover {
        background: #fff;
        border-color: var(--color-primary, #4f46e5);
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.1);
    }
    .smtp-info-item .info-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }
    .smtp-info-item .info-content h4 {
        margin: 0 0 4px 0;
        font-size: 14px;
        font-weight: 700;
        color: #1e293b;
    }
    .smtp-info-item .info-content p {
        margin: 0;
        font-size: 12px;
        color: #64748b;
        line-height: 1.5;
    }
    .password-toggle {
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
    .password-toggle:hover {
        color: #475569;
    }
    .port-options {
        display: flex;
        gap: 8px;
        margin-top: 8px;
    }
    .port-option {
        padding: 6px 14px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        color: #64748b;
        transition: all 0.2s;
        font-family: 'Inter', monospace;
    }
    .port-option:hover {
        border-color: var(--color-primary, #4f46e5);
        color: var(--color-primary, #4f46e5);
        background: #eef2ff;
    }
    @media (max-width: 768px) {
        .smtp-body { padding: 20px; }
        .smtp-header { padding: 24px 20px; }
        .smtp-actions { flex-direction: column; }
        .smtp-actions .btn-save, .smtp-actions .btn-test { min-width: auto; }
    }
</style>

<!-- En-tête de page -->
<div class="header-actions" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px; flex-wrap:wrap; gap:12px;">
    <div>
        <h2 style="font-weight: 800; color: var(--color-secondary); margin: 0;">
            <i class="fa-solid fa-envelope-circle-check" style="color: var(--color-primary); margin-right: 8px;"></i>
            Configuration Email
        </h2>
        <p style="margin: 4px 0 0 0; font-size: 13px; color: #64748b;">Paramètres SMTP pour les alertes et validations de compte</p>
    </div>
    <a href="index.php?page=superadmin_dashboard" class="btn btn-outline" style="font-size:13px; border-radius:10px;">
        <i class="fa-solid fa-arrow-left"></i> Retour Console
    </a>
</div>

<!-- Info Cards -->
<div class="smtp-info-grid">
    <div class="smtp-info-item">
        <div class="info-icon" style="background: #eef2ff; color: var(--color-primary, #4f46e5);">
            <i class="fa-solid fa-bell"></i>
        </div>
        <div class="info-content">
            <h4>Alertes Automatiques</h4>
            <p>Notification automatique des administrateurs lors de détections d'anomalies GPS.</p>
        </div>
    </div>
    <div class="smtp-info-item">
        <div class="info-icon" style="background: #dcfce7; color: #16a34a;">
            <i class="fa-solid fa-user-check"></i>
        </div>
        <div class="info-content">
            <h4>Validation de Compte</h4>
            <p>Envoi de liens de validation pour confirmer la création des nouveaux comptes.</p>
        </div>
    </div>
    <div class="smtp-info-item">
        <div class="info-icon" style="background: #fef3c7; color: #d97706;">
            <i class="fa-solid fa-shield-halved"></i>
        </div>
        <div class="info-content">
            <h4>Connexion Sécurisée</h4>
            <p>SSL/TLS pour garantir la sécurité des communications email.</p>
        </div>
    </div>
</div>

<!-- Formulaire SMTP -->
<div class="smtp-card">
    <div class="smtp-header">
        <h2><i class="fa-solid fa-server" style="margin-right: 10px;"></i> Serveur SMTP</h2>
        <p>Configurez les paramètres de votre serveur de messagerie pour l'envoi des emails transactionnels.</p>
    </div>
    <div class="smtp-body">
        <form method="POST" action="index.php?page=superadmin_save_email_settings" id="smtpForm">
            <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken() ?>">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <!-- SMTP HOST -->
                <div class="smtp-form-group" style="grid-column: 1 / -1;">
                    <label><i class="fa-solid fa-globe"></i> Serveur SMTP (Host)</label>
                    <input type="text" name="smtp_host" id="smtp_host"
                           value="<?= htmlspecialchars($smtpSettings['SMTP_HOST'] ?? '') ?>"
                           placeholder="mail.votre-domaine.com"
                           required>
                    <div class="input-hint">
                        <i class="fa-solid fa-info-circle"></i>
                        Adresse du serveur SMTP de votre hébergeur (ex: mail.example.com, smtp.gmail.com)
                    </div>
                </div>
                
                <!-- SMTP USER -->
                <div class="smtp-form-group">
                    <label><i class="fa-solid fa-at"></i> Utilisateur SMTP</label>
                    <input type="email" name="smtp_user" id="smtp_user"
                           value="<?= htmlspecialchars($smtpSettings['SMTP_USER'] ?? '') ?>"
                           placeholder="contact@votre-domaine.com"
                           required>
                    <div class="input-hint">
                        <i class="fa-solid fa-info-circle"></i>
                        Adresse email d'authentification
                    </div>
                </div>
                
                <!-- SMTP PORT -->
                <div class="smtp-form-group">
                    <label><i class="fa-solid fa-hashtag"></i> Port SMTP</label>
                    <input type="number" name="smtp_port" id="smtp_port"
                           value="<?= htmlspecialchars($smtpSettings['SMTP_PORT'] ?? '465') ?>"
                           placeholder="465"
                           min="1" max="65535"
                           required>
                    <div class="port-options">
                        <button type="button" class="port-option" onclick="document.getElementById('smtp_port').value='465'">465 (SSL)</button>
                        <button type="button" class="port-option" onclick="document.getElementById('smtp_port').value='587'">587 (TLS)</button>
                        <button type="button" class="port-option" onclick="document.getElementById('smtp_port').value='25'">25 (Non sécurisé)</button>
                    </div>
                </div>
                
                <!-- SMTP PASS -->
                <div class="smtp-form-group" style="grid-column: 1 / -1;">
                    <label><i class="fa-solid fa-lock"></i> Mot de passe SMTP</label>
                    <input type="password" name="smtp_pass" id="smtp_pass"
                           value="<?= htmlspecialchars($smtpSettings['SMTP_PASS'] ?? '') ?>"
                           placeholder="Votre mot de passe SMTP"
                           required>
                    <button type="button" class="password-toggle" onclick="togglePassword()">
                        <i class="fa-solid fa-eye" id="passToggleIcon"></i>
                    </button>
                    <div class="input-hint">
                        <i class="fa-solid fa-shield-halved"></i>
                        Stocké de manière chiffrée en base de données
                    </div>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="smtp-actions">
                <button type="submit" class="btn-save">
                    <i class="fa-solid fa-floppy-disk"></i> Enregistrer la configuration
                </button>
                <button type="button" class="btn-test" onclick="testSmtp()">
                    <i class="fa-solid fa-vial"></i> Tester la connexion
                </button>
            </div>
        </form>
        
        <!-- Test SMTP Form (Hidden) -->
        <form method="POST" action="index.php?page=superadmin_test_email" id="testSmtpForm" style="display:none;">
            <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken() ?>">
            <input type="hidden" name="smtp_host" id="test_smtp_host">
            <input type="hidden" name="smtp_user" id="test_smtp_user">
            <input type="hidden" name="smtp_pass" id="test_smtp_pass">
            <input type="hidden" name="smtp_port" id="test_smtp_port">
        </form>
        
        <!-- Notice de sécurité -->
        <div class="smtp-security-notice">
            <i class="fa-solid fa-lock"></i>
            <div class="notice-text">
                <strong>Note de sécurité :</strong> Le mot de passe SMTP est stocké de manière chiffrée (AES-256) dans la base de données. 
                Les communications avec le serveur SMTP utilisent SSL/TLS selon le port configuré. 
                Nous recommandons l'utilisation du <strong>port 465 (SSL)</strong> ou <strong>587 (TLS)</strong> pour une sécurité optimale.
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const input = document.getElementById('smtp_pass');
    const icon = document.getElementById('passToggleIcon');
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

function testSmtp() {
    const host = document.getElementById('smtp_host').value;
    const user = document.getElementById('smtp_user').value;
    const pass = document.getElementById('smtp_pass').value;
    const port = document.getElementById('smtp_port').value;
    
    if (!host || !user || !pass || !port) {
        alert('Veuillez remplir tous les champs SMTP avant de tester.');
        return;
    }
    
    if (!confirm('Un email de test sera envoyé à l\'adresse SMTP configurée (' + user + '). Continuer ?')) return;
    
    document.getElementById('test_smtp_host').value = host;
    document.getElementById('test_smtp_user').value = user;
    document.getElementById('test_smtp_pass').value = pass;
    document.getElementById('test_smtp_port').value = port;
    document.getElementById('testSmtpForm').submit();
}

// Auto-dismiss alerts after 6 seconds
setTimeout(() => {
    const alerts = document.querySelectorAll('#alertSuccess, #alertTestSuccess, #alertError');
    alerts.forEach(el => {
        el.style.transition = 'opacity 0.5s, transform 0.5s';
        el.style.opacity = '0';
        el.style.transform = 'translateY(-15px)';
        setTimeout(() => el.remove(), 500);
    });
}, 6000);
</script>
