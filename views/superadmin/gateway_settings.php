<?php
// views/superadmin/gateway_settings.php
?>

<!-- Messages de succès / erreur -->
<?php if(isset($_GET['success']) && $_GET['success'] === 'gateway_saved'): ?>
<div id="alertSuccess" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color:#fff; padding:16px 20px; border-radius:14px; margin-bottom:25px; display:flex; align-items:center; gap:12px;">
    <i class="fa-solid fa-circle-check" style="font-size:22px;"></i>
    <div>
        <strong>Passerelle active mise à jour !</strong>
        <div style="font-size:13px; opacity:0.9;">La plateforme utilisera désormais cette passerelle pour tous les nouveaux abonnements.</div>
    </div>
</div>
<?php endif; ?>

<style>
    .gw-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 25px;
        margin-top: 20px;
    }
    .gw-card {
        background: #fff;
        border-radius: 20px;
        border: 2px solid #e2e8f0;
        padding: 30px;
        text-align: center;
        transition: all 0.3s ease;
        position: relative;
        cursor: pointer;
    }
    .gw-card.active {
        border-color: #4f46e5;
        background: #f5f3ff;
        box-shadow: 0 10px 25px rgba(79, 70, 229, 0.1);
    }
    .gw-card.active::before {
        content: '\f058';
        font-family: "Font Awesome 6 Free";
        font-weight: 900;
        position: absolute;
        top: 15px;
        right: 15px;
        color: #4f46e5;
        font-size: 24px;
    }
    .gw-icon {
        width: 70px;
        height: 70px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 30px;
        margin: 0 auto 20px auto;
    }
    .gw-card h3 {
        margin: 0 0 10px 0;
        font-weight: 800;
        color: #1e293b;
    }
    .gw-card p {
        font-size: 14px;
        color: #64748b;
        line-height: 1.5;
        margin-bottom: 25px;
    }
    .btn-activate {
        padding: 10px 20px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 14px;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
    }
    .active .btn-activate {
        background: #4f46e5;
        color: #fff;
    }
    .not-active .btn-activate {
        background: #f1f5f9;
        color: #64748b;
    }
    .gw-card:hover:not(.active) {
        border-color: #cbd5e1;
        transform: translateY(-5px);
    }
</style>

<div class="header-actions" style="margin-bottom:25px;">
    <h2 style="font-weight: 800; color: var(--color-secondary); margin: 0;">
        <i class="fa-solid fa-toggle-on" style="color: #4f46e5; margin-right: 8px;"></i>
        Choix de la Passerelle Active
    </h2>
    <p style="margin: 4px 0 0 0; font-size: 13px; color: #64748b;">Définissez quel service sera proposé aux clients lors du paiement de leur abonnement.</p>
</div>

<form action="index.php?page=superadmin_save_gateway_settings" method="POST" id="gatewayForm">
    <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken() ?>">
    <input type="hidden" name="active_gateway" id="active_gateway_input" value="<?= htmlspecialchars($activeGateway) ?>">

    <div class="gw-container">
        <!-- CinetPay Card -->
        <div class="gw-card <?= $activeGateway === 'cinetpay' ? 'active' : 'not-active' ?>" onclick="selectGateway('cinetpay')">
            <div class="gw-icon" style="background:#fff7ed; color:#f59e0b;">
                <i class="fa-solid fa-credit-card"></i>
            </div>
            <h3>CinetPay</h3>
            <p>Spécialisé en Afrique de l'Ouest et Centrale. Supporte Orange, MTN, MoMo et Cartes Bancaires.</p>
            <div class="btn-activate">
                <?= $activeGateway === 'cinetpay' ? 'Passerelle Actuelle' : 'Choisir CinetPay' ?>
            </div>
        </div>

        <!-- Money Fusion Card -->
        <div class="gw-card <?= $activeGateway === 'moneyfusion' ? 'active' : 'not-active' ?>" onclick="selectGateway('moneyfusion')">
            <div class="gw-icon" style="background:#ecfeff; color:#06b6d4;">
                <i class="fa-solid fa-bolt"></i>
            </div>
            <h3>Money Fusion</h3>
            <p>Passerelle Fusion Pay rapide via lien API. Simple et efficace pour les paiements locaux.</p>
            <div class="btn-activate">
                <?= $activeGateway === 'moneyfusion' ? 'Passerelle Actuelle' : 'Choisir Money Fusion' ?>
            </div>
        </div>
    </div>
</form>

<script>
function selectGateway(gateway) {
    document.getElementById('active_gateway_input').value = gateway;
    document.getElementById('gatewayForm').submit();
}
</script>
