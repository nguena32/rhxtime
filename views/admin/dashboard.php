<?php if (isset($_GET['welcome']) && $_GET['welcome'] === '1'): ?>
<div id="welcomeBanner" style="
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
    color: white;
    border-radius: 16px;
    padding: 20px 28px;
    margin-bottom: 28px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    animation: slideDown 0.5s ease;
    box-shadow: 0 8px 25px rgba(79,70,229,0.35);
">
    <style>
        @keyframes slideDown { from { opacity:0; transform:translateY(-15px); } to { opacity:1; transform:translateY(0); } }
    </style>
    <div style="display:flex; align-items:center; gap:16px;">
        <div style="font-size:36px;">🎉</div>
        <div>
            <div style="font-weight:800; font-size:17px; margin-bottom:3px;">Email vérifié — Bienvenue sur RHXtimes !</div>
            <div style="font-size:13px; opacity:0.85;">Votre compte est maintenant actif. Commencez par configurer vos lieux et ajouter vos employés.</div>
        </div>
    </div>
    <button onclick="document.getElementById('welcomeBanner').style.display='none'"
            style="background:rgba(255,255,255,0.2); border:none; color:white; width:32px; height:32px; border-radius:50%; cursor:pointer; font-size:18px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
        ×
    </button>
</div>
<?php endif; ?>

<div class="stats-grid">
    <div class="stat-card">
        <div>
            <div class="stat-value"><?= $nb_users ?? 0 ?></div>
            <div class="stat-label">Total Employés</div>
        </div>
        <div style="font-size:30px; color:var(--color-primary); opacity:0.2;">
            <i class="fa-solid fa-users"></i>
        </div>
    </div>

    <!-- ARRRIVÉES -->
    <div class="stat-card" style="border-left-color: #10b981;">
        <div>
            <div class="stat-value" style="color: #10b981;"><?= $nb_presents ?? 0 ?></div>
            <div class="stat-label">Arrivées validées</div>
        </div>
        <div style="font-size:30px; color:#10b981; opacity:0.2;">
            <i class="fa-solid fa-right-to-bracket"></i>
        </div>
    </div>

    <!-- DÉPARTS -->
    <div class="stat-card" style="border-left-color: #3b82f6;">
        <div>
            <div class="stat-value" style="color: #3b82f6;"><?= $nb_departures ?? 0 ?></div>
            <div class="stat-label">Départs validés</div>
        </div>
        <div style="font-size:30px; color:#3b82f6; opacity:0.2;">
            <i class="fa-solid fa-right-from-bracket"></i>
        </div>
    </div>

    <!-- RETARDS -->
    <div class="stat-card" style="border-left-color: #f59e0b;">
        <div>
            <div class="stat-value" style="color: #f59e0b;"><?= $nb_lates ?? 0 ?></div>
            <div class="stat-label">Employés en Retard</div>
        </div>
        <div style="font-size:30px; color:#f59e0b; opacity:0.2;">
            <i class="fa-solid fa-clock-rotate-left"></i>
        </div>
    </div>

    <!-- ABSENCES -->
    <div class="stat-card" style="border-left-color: #ef4444;">
        <div>
            <div class="stat-value" style="color: #ef4444;"><?= $nb_absents ?? 0 ?></div>
            <div class="stat-label">Employés Absents</div>
        </div>
        <div style="font-size:30px; color:#ef4444; opacity:0.2;">
            <i class="fa-solid fa-user-xmark"></i>
        </div>
    </div>
    
    <div class="stat-card" style="border-left-color: var(--color-accent);">
        <div>
            <div class="stat-value"><?= $nb_lieux ?? 0 ?></div>
            <div class="stat-label">Sites Configurés</div>
        </div>
        <div style="font-size:30px; color:var(--color-accent); opacity:0.2;">
            <i class="fa-solid fa-map-location-dot"></i>
        </div>
    </div>
</div>

<div class="card" style="margin-bottom: 30px;">
    <h3 class="text-h2" style="margin-bottom: 15px;">🔍 Recherche & Historique</h3>
    
    <form method="GET" action="index.php" style="display:flex; flex-wrap:wrap; gap:10px; align-items:end;">
        
        <input type="hidden" name="page" value="admin_dashboard">

        <div style="flex:1; min-width:200px;">
            <label style="font-size:12px; font-weight:bold; color:#6b7280;">Employé</label>
            <select name="user_id" class="form-control" style="width:100%;">
                <option value="">-- Tous les employés --</option>
                <?php foreach($all_users as $u): ?>
                    <option value="<?= $u['id'] ?>" <?= (isset($_GET['user_id']) && $_GET['user_id'] == $u['id']) ? 'selected' : '' ?>>
                        <?= Security::html($u['nom'] . ' ' . $u['prenom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="flex:1; min-width:150px;">
            <label style="font-size:12px; font-weight:bold; color:#6b7280;">Du</label>
            <input type="date" name="date_start" class="form-control" value="<?= Security::html($_GET['date_start'] ?? '') ?>">
        </div>

        <div style="flex:1; min-width:150px;">
            <label style="font-size:12px; font-weight:bold; color:#6b7280;">Au</label>
            <input type="date" name="date_end" class="form-control" value="<?= Security::html($_GET['date_end'] ?? '') ?>">
        </div>

        <div style="display:flex; gap:5px; margin-bottom: 16px;">
            <button type="submit" class="btn btn-primary" style="padding:10px 20px;">
                <i class="fa-solid fa-filter"></i> Filtrer
            </button>
            <a href="index.php?page=admin_dashboard" class="btn" style="background:#e5e7eb; color:#374151; padding:10px 15px;" title="Réinitialiser">
                <i class="fa-solid fa-rotate-left"></i>
            </a>
        </div>
    </form>
</div>

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h3 style="font-size:16px; font-weight:700;">Résultats récents</h3>
        <button class="btn btn-primary" style="padding: 8px 12px; font-size:12px; opacity:0.7; cursor:not-allowed;">
            <i class="fa-solid fa-download"></i> <span style="margin-left:5px;">Export</span>
        </button>
    </div>
    
    <div class="table-container" style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:2px solid #f3f4f6; text-align:left; background:#f9fafb;">
                    <th style="padding:12px;">Employé</th>
                    <th style="padding:12px;">Type</th>
                    <th style="padding:12px;">Date & Heure</th>
                    <th style="padding:12px;">Lieu</th>
                    <th style="padding:12px;">Distance</th>
                </tr>
            </thead>
            <tbody id="pointage-feed-body">
                <!-- Chargement dynamique via AJAX -->
            </tbody>
        </table>
        
        <!-- Indicateur de chargement & Bouton Charger Plus -->
        <div id="loading-area" style="text-align:center; padding:30px; display:none;">
            <div class="spinner" style="width:30px; height:30px; border:3px solid #f3f3f3; border-top:3px solid var(--color-primary); border-radius:50%; animation:spin 1s linear infinite; margin:0 auto 10px auto;"></div>
            <p style="font-size:12px; color:#64748b;">Chargement des pointages...</p>
        </div>

        <div id="load-more-btn-container" style="text-align:center; padding:20px; border-top:1px solid #f1f5f9; display:none;">
            <button id="load-more-btn" class="btn" style="background:#f1f5f9; color:var(--color-primary); font-weight:700; border-radius:12px; padding:10px 25px;">
                <i class="fa-solid fa-plus-circle"></i> Afficher plus de résultats
            </button>
        </div>

        <div id="empty-state" style="text-align:center; padding:40px; color:#94a3b8; display:none;">
            <i class="fa-solid fa-file-circle-exclamation" style="font-size:30px; display:block; margin-bottom:10px;"></i>
            Aucun pointage trouvé pour cette période.
        </div>
    </div>
</div>

<style>
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>

<!-- Données pour le JS -->
<script>
window.dashboardFilters = {
    user_id: '<?= $_GET['user_id'] ?? '' ?>',
    date_start: '<?= $_GET['date_start'] ?? '' ?>',
    date_end: '<?= $_GET['date_end'] ?? '' ?>'
};
</script>
<script src="assets/js/dashboard-lazyload.js?v=1.7"></script>