<?php
// views/superadmin/whatsapp_settings.php
?>

<!-- Messages de succès / erreur -->
<?php if(isset($_GET['success']) && $_GET['success'] === 'updated'): ?>
<div id="alertSuccess" style="background: linear-gradient(135deg, #25d366 0%, #128c7e 100%); color:#fff; padding:16px 20px; border-radius:14px; margin-bottom:25px; display:flex; align-items:center; gap:12px; box-shadow: 0 4px 15px rgba(37,211,102,0.3); animation: slideDown 0.4s ease;">
    <i class="fa-brands fa-whatsapp" style="font-size:22px;"></i>
    <div>
        <strong>Numéro mis à jour !</strong>
        <div style="font-size:13px; opacity:0.9; margin-top:2px;">Le widget WhatsApp sur la landing page utilisera désormais ce numéro.</div>
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
                'invalid_format' => 'Le format du numéro est invalide. Utilisez le format +237XXXXXXXXX.',
                'save_failed'    => 'Impossible de sauvegarder. Erreur technique.',
            ];
            echo $errors[$_GET['error']] ?? 'Une erreur est survenue.';
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
    .wa-card {
        background: #fff;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        max-width: 600px;
    }
    .wa-header {
        background: linear-gradient(135deg, #25d366 0%, #128c7e 100%);
        padding: 30px;
        color: #fff;
    }
    .wa-header h2 { margin: 0; font-size: 22px; font-weight: 800; }
    .wa-header p { margin: 8px 0 0 0; font-size: 14px; opacity: 0.9; }
    
    .wa-body { padding: 30px; }
    
    .wa-form-group { margin-bottom: 24px; }
    .wa-form-group label {
        display: block;
        font-size: 13px;
        font-weight: 700;
        color: #334155;
        text-transform: uppercase;
        margin-bottom: 8px;
    }
    .wa-form-group input {
        width: 100%;
        padding: 14px 16px;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        font-size: 16px;
        background: #f8fafc;
        transition: all 0.3s ease;
        box-sizing: border-box;
    }
    .wa-form-group input:focus {
        outline: none;
        border-color: #25d366;
        background: #fff;
        box-shadow: 0 0 0 4px rgba(37, 211, 102, 0.1);
    }
    
    .btn-save-wa {
        width: 100%;
        padding: 14px;
        background: #25d366;
        color: white;
        border: none;
        border-radius: 12px;
        font-weight: 700;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .btn-save-wa:hover {
        background: #128c7e;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(18, 140, 126, 0.3);
    }
    
    .wa-preview {
        margin-top: 25px;
        padding: 20px;
        background: #f0fdf4;
        border: 1px dashed #25d366;
        border-radius: 14px;
        text-align: center;
    }
</style>

<div class="header-actions" style="margin-bottom: 25px;">
    <h2 style="font-weight: 800; color: var(--color-secondary); margin: 0;">
        <i class="fa-brands fa-whatsapp" style="color: #25d366; margin-right: 8px;"></i>
        Configuration WhatsApp
    </h2>
    <p style="margin: 4px 0 0 0; font-size: 13px; color: #64748b;">Gérez le numéro de contact direct pour vos prospects sur la landing page.</p>
</div>

<div class="wa-card">
    <div class="wa-header">
        <h2>Numéro de Réception</h2>
        <p>Le bouton WhatsApp apparaîtra automatiquement sur la page d'accueil si un numéro est renseigné.</p>
    </div>
    <div class="wa-body">
        <form method="POST" action="index.php?page=superadmin_save_whatsapp">
            <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken() ?>">
            
            <div class="wa-form-group">
                <label>Numéro WhatsApp (Format International)</label>
                <input type="text" name="whatsapp_number" 
                       value="<?= htmlspecialchars($whatsappNumber) ?>" 
                       placeholder="+2376XXXXXXXX" 
                       required>
                <p style="font-size: 12px; color: #64748b; margin-top: 8px;">
                    <i class="fa-solid fa-circle-info"></i> Entrez le numéro avec le code pays, sans espaces (ex: +2376XXXXXXXX).
                </p>
            </div>
            
            <button type="submit" class="btn-save-wa">
                <i class="fa-solid fa-floppy-disk"></i> Enregistrer le numéro
            </button>
        </form>

        <?php if(!empty($whatsappNumber)): ?>
        <div class="wa-preview">
            <p style="margin:0; font-size: 13px; color: #15803d; font-weight: 600;">
                <i class="fa-solid fa-eye"></i> Widget Actif sur la Landing Page
            </p>
            <div style="font-size: 11px; color: #166534; margin-top: 4px;">Cible : <?= htmlspecialchars($whatsappNumber) ?></div>
        </div>
        <?php else: ?>
        <div class="wa-preview" style="background: #fff7ed; border-color: #f97316; color: #9a3412;">
            <p style="margin:0; font-size: 13px; font-weight: 600;">
                <i class="fa-solid fa-eye-slash"></i> Widget Inactif
            </p>
            <div style="font-size: 11px; margin-top: 4px;">Renseignez un numéro pour activer le bouton WhatsApp.</div>
        </div>
        <?php endif; ?>
    </div>
</div>
