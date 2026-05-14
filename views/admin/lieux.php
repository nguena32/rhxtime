<?php
// views/admin/lieux.php
$stmtPlan = $pdo->prepare("SELECT p.* FROM entreprises e JOIN plans p ON e.plan_id = p.id WHERE e.id = ?");
$stmtPlan->execute([$_SESSION['entreprise_id']]);
$planFeatures = $stmtPlan->fetch();
$hasDirectScan = (bool)($planFeatures['has_direct_scan'] ?? true);
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<div class="container">
    <a href="index.php?page=admin_dashboard" style="text-decoration:none; color:grey;">&larr; Retour Dashboard</a>
    
    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:10px; margin-bottom:20px;">
        <h1 class="text-h1">Gérer les Lieux</h1>
        <?php if ($_SESSION['user_role'] === 'owner') { ?>
            <button type="button" onclick="checkQuotaAndOpen('sites', <?= (int)$currentSites ?>, <?= (int)$maxSites ?>, () => openLieuModal('add'))" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> Ajouter un site
            </button>
        <?php } ?>
    </div>

            <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap:20px;">
        <?php foreach ($lieux as $l) { ?>
            <div class="card" style="display:flex; flex-direction:column; justify-content:space-between; margin-bottom:0;">
                <div>
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:10px;">
                        <div style="font-weight:bold; font-size:18px; color:var(--color-primary);">
                            <?php echo Security::html($l['nom_lieu']); ?>
                        </div>
                        <div style="display:flex; gap:5px; align-items:center;">
                            <?php
                            $methode = $l['methode_pointage'] ?? 'QR_CODE';
                            if ($methode === 'DIRECT'):
                            ?>
                                <span style="font-size:11px; font-weight:700; background:#ede9fe; color:#7c3aed; padding:3px 9px; border-radius:12px;"><i class="fa-solid fa-location-crosshairs" style="margin-right:3px;"></i>Pointage direct (One-Tap)</span>
                            <?php else: ?>
                                <span style="font-size:11px; font-weight:700; background:#e0f2fe; color:#0284c7; padding:3px 9px; border-radius:12px;"><i class="fa-solid fa-qrcode" style="margin-right:3px;"></i>QR Code</span>
                            <?php endif; ?>
                            <?php if ($_SESSION['user_role'] === 'owner') { ?>
                                <button type="button"
                                        class="btn"
                                        style="background:#f3f4f6; color:#374151; padding:5px 8px; font-size:12px;"
                                        title="Modifier"
                                        onclick="handleEdit(this)"
                                        data-lieu='<?php echo Security::html(json_encode($l)); ?>'>
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <a href="javascript:void(0)"
                                   class="btn"
                                   style="background:#fee2e2; color:#991b1b; padding:5px 8px; font-size:12px;"
                                   onclick="confirmDelete('index.php?page=admin_delete_lieu&id=<?php echo $l['id']; ?>&csrf_token=<?php echo Security::generateCsrfToken(); ?>')"
                                   title="Supprimer">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            <?php } ?>
                        </div>
                    </div>
                    <div style="color:grey; font-size:14px; margin-bottom:15px;">
                        <i class="fa-solid fa-location-dot"></i> GPS: <?php echo Security::html($l['gps_lat']); ?>, <?php echo Security::html($l['gps_lng']); ?><br>
                        <i class="fa-solid fa-circle-dot"></i> Rayon: <?php echo Security::html($l['rayon_metre']); ?>m<br>
                        <i class="fa-solid fa-clock-rotate-left"></i> Tolérance retard: <?php echo isset($l['tolerance_retard']) ? Security::html($l['tolerance_retard']) : 0; ?> min
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr <?php echo ($l['methode_pointage'] ?? 'QR_CODE') === 'QR_CODE' ? '1fr' : ''; ?>; gap:10px; margin-top:auto; padding-top:15px; border-top:1px solid #f1f5f9;">
                    <a href="index.php?page=admin_config_lieu&id=<?php echo $l['id']; ?>" class="btn" style="background:#e0e7ff; color:var(--color-primary); font-size:13px; padding:10px;">
                        <i class="fa-solid fa-clock"></i> Horaires de Travail
                    </a>
                    <?php if (($l['methode_pointage'] ?? 'QR_CODE') === 'QR_CODE'): ?>
                    <button type="button"
                            class="btn btn-primary"
                            style="font-size:13px; padding:10px;"
                            onclick="handleQR(this)"
                            data-token="<?php echo Security::html($l['qr_token']); ?>"
                            data-name="<?php echo Security::html($l['nom_lieu']); ?>">
                        <i class="fa-solid fa-qrcode"></i> QR Code
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php } ?>
    </div>
</div>

<!-- Modal Creation/Edition -->
<div id="lieuModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div class="card" style="width:90%; max-width:500px; padding:20px; position:relative;">
        <button type="button" onclick="closeLieuModal()" style="position:absolute; top:15px; right:15px; border:none; background:none; font-size:20px; cursor:pointer;">&times;</button>
        <h2 id="modalTitle" class="text-h2" style="margin-bottom:20px;">Nouveau Site</h2>
        
        <form id="lieuForm" action="index.php?page=admin_save_lieu" method="POST" onsubmit="return validateLieuForm(event)">
            <input type="hidden" name="csrf_token" value="<?php echo Security::generateCsrfToken(); ?>">
            <input type="hidden" name="id" id="lieu_id">
            
            <div class="form-group">
                <label>Nom du site</label>
                <input type="text" name="nom_lieu" id="edit_nom_lieu" class="form-control" placeholder="Ex: Siège Bastos" required>
            </div>
            
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                <div class="form-group">
                    <label>Latitude</label>
                    <input type="text" name="gps_lat" id="edit_lat" class="form-control" placeholder="0.000000">
                </div>
                <div class="form-group">
                    <label>Longitude</label>
                    <input type="text" name="gps_lng" id="edit_lng" class="form-control" placeholder="0.000000">
                </div>
            </div>

            <button type="button" onclick="getMyPosition()" id="btnGetPos" class="btn" style="background:#eee; color:#333; margin-bottom:20px; width:100%; border: 2px dashed #4F46E5; color: #4F46E5; font-weight: bold;">
                📍 Utiliser ma position actuelle
            </button>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                <div class="form-group">
                    <label>Rayon (mètres)</label>
                    <input type="number" name="rayon_metre" id="edit_rayon" class="form-control" value="50">
                </div>
                <div class="form-group">
                    <label>Tolérance (min)</label>
                    <input type="number" name="tolerance_retard" id="edit_tolerance" class="form-control" value="0">
                </div>
            </div>

            <div class="form-group">
                <label><i class="fa-solid fa-mobile-screen" style="color:var(--color-primary); margin-right:5px;"></i>Méthode de pointage</label>
                <select name="methode_pointage" id="edit_methode" class="form-control" required onchange="toggleQRInfo(this.value)" style="width:100%; padding:12px; border-radius:8px; border:2px solid #e2e8f0; font-size:15px; cursor:pointer;">
                    <option value="DIRECT" <?= !$hasDirectScan ? 'disabled' : '' ?>>
                        📍 Avec One-Tap (automatique) <?= !$hasDirectScan ? '— 🚩 Option indisponible avec votre forfait' : '' ?>
                    </option>
                    <option value="QR_CODE">📷 Avec QR Code (scan physique)</option>
                </select>
                <p id="methodeHint" style="font-size:12px; color:#64748b; margin-top:6px; padding:8px 12px; background:#f8fafc; border-radius:8px; border-left:3px solid var(--color-primary);"></p>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;">Enregistrer le Lieu</button>
        </form>
    </div>
</div>

<!-- Modal QR Code -->
<div id="qrModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); align-items:center; justify-content:center; z-index:1500;">
    <div class="card" style="width:350px; text-align:center;">
        <h2 id="qrTitle" class="text-h2"></h2>
        <div id="qrCanvas" style="margin:20px auto; display:flex; justify-content:center;"></div>
        <p style="font-size:12px; color:grey;">Scannez ce code pour pointer</p>
        <hr style="margin:20px 0; border:0; border-top:1px solid #eee;">
        <button type="button" onclick="printQR()" class="btn btn-primary" style="margin-bottom:10px; width:100%;">Imprimer (PDF)</button>
        <button type="button" onclick="closeQR()" class="btn" style="background:#eee; color:#333; width:100%;">Fermer</button>
    </div>
</div>

<script>
function validateLieuForm(e) {
    const lat = document.getElementById('edit_lat').value.trim();
    const lng = document.getElementById('edit_lng').value.trim();
    
    if (!lat || !lng || lat === "0" || lng === "0") {
        e.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'Localisation manquante',
            text: 'Veuillez cliquer sur le bouton "Utiliser ma position actuelle" pour définir les coordonnées GPS du site avant d\'enregistrer.',
            confirmButtonColor: '#4F46E5',
            confirmButtonText: 'Compris !'
        }).then(() => {
            const btn = document.getElementById('btnGetPos');
            btn.style.animation = "pulse-blue 1s infinite";
            setTimeout(() => { btn.style.animation = ""; }, 3000);
        });
        return false;
    }
    return true;
}

function handleEdit(btn) {
    const lieu = JSON.parse(btn.dataset.lieu);
    openLieuModal('edit', lieu);
}

function handleQR(btn) {
    showQR(btn.dataset.token, btn.dataset.name);
}

function openLieuModal(mode, lieu = null) {
    const modal = document.getElementById('lieuModal');
    const form  = document.getElementById('lieuForm');
    const title = document.getElementById('modalTitle');

    form.reset();
    document.getElementById('lieu_id').value = '';
    document.getElementById('btnGetPos').style.animation = "";

    if (mode === 'edit' && lieu) {
        title.innerText = "Modifier le site";
        form.action = "index.php?page=admin_update_lieu";
        document.getElementById('lieu_id').value        = lieu.id;
        document.getElementById('edit_nom_lieu').value  = lieu.nom_lieu;
        document.getElementById('edit_lat').value       = lieu.gps_lat;
        document.getElementById('edit_lng').value       = lieu.gps_lng;
        document.getElementById('edit_rayon').value     = lieu.rayon_metre;
        document.getElementById('edit_tolerance').value = (lieu.tolerance_retard !== undefined && lieu.tolerance_retard !== null) ? lieu.tolerance_retard : 0;
        const methode = lieu.methode_pointage || 'QR_CODE';
        document.getElementById('edit_methode').value   = methode;
        toggleQRInfo(methode);
    } else {
        title.innerText = "Nouveau Site";
        form.action = "index.php?page=admin_save_lieu";
        const defaultMethode = '<?= $hasDirectScan ? "DIRECT" : "QR_CODE" ?>';
        document.getElementById('edit_methode').value = defaultMethode;
        toggleQRInfo(defaultMethode);
    }

    modal.style.display = 'flex';
}

function closeLieuModal() {
    document.getElementById('lieuModal').style.display = 'none';
}

function confirmDelete(url) {
    Swal.fire({
        title: 'Êtes-vous sûr ?',
        text: "Cette action supprimera également tous les horaires liés à ce site.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#64748B',
        confirmButtonText: 'Oui, supprimer',
        cancelButtonText: 'Annuler',
        customClass: {
            popup: 'swal2-modern-popup'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
}

function getMyPosition() {
    if (navigator.geolocation) {
        Swal.fire({
            title: 'Localisation en cours...',
            text: 'Veuillez patienter pendant que nous récupérons votre position.',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });
        navigator.geolocation.getCurrentPosition(function(position) {
            Swal.close();
            document.getElementById('edit_lat').value = position.coords.latitude;
            document.getElementById('edit_lng').value = position.coords.longitude;
            document.getElementById('btnGetPos').style.animation = "";
            Toast.fire({ icon: 'success', title: 'Position récupérée !' });
        }, function(error) {
            Swal.fire({
                icon: 'error',
                title: 'Erreur GPS',
                text: 'Activez la localisation sur votre appareil et réessayez.',
                confirmButtonColor: '#4F46E5'
            });
        });
    } else {
        Swal.fire({ icon: 'error', title: 'Non supporté', text: 'GPS non supporté par ce navigateur.' });
    }
}

function showQR(token, name) {
    document.getElementById('qrModal').style.display = 'flex';
    document.getElementById('qrTitle').innerText = name;
    document.getElementById('qrCanvas').innerHTML = ""; 
    new QRCode(document.getElementById("qrCanvas"), {
        text: token,
        width: 200,
        height: 200
    });
}

function closeQR() {
    document.getElementById('qrModal').style.display = 'none';
}

function printQR() {
    window.print();
}

function toggleQRInfo(val) {
    const hint = document.getElementById('methodeHint');
    if (val === 'DIRECT') {
        hint.innerHTML = '<i class="fa-solid fa-location-crosshairs"></i> <strong>Avec One-Tap :</strong> L\'employé pointe via One-Tap uniquement — pas besoin d\'afficher un QR Code sur le site.';
        hint.style.borderLeftColor = '#7c3aed';
    } else {
        hint.innerHTML = '<i class="fa-solid fa-qrcode"></i> <strong>QR Code :</strong> Un QR Code physique sera généré et doit être affiché sur le site de travail.';
        hint.style.borderLeftColor = '#0284c7';
    }
}

window.onclick = function(event) {
    if (event.target == document.getElementById('lieuModal')) closeLieuModal();
    if (event.target == document.getElementById('qrModal')) closeQR();
}
</script>

<style>
@keyframes pulse-blue {
    0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(79, 70, 229, 0.7); }
    70% { transform: scale(1.02); box-shadow: 0 0 0 10px rgba(79, 70, 229, 0); }
    100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(79, 70, 229, 0); }
}
</style>