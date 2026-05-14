<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
    <h1 class="text-h2">Pointage Xtimes (Quotidien)</h1>
    <form method="GET" action="index.php" style="display:flex; gap:10px;">
        <input type="hidden" name="page" value="admin_xtimes">
        <input type="date" name="date" class="form-control" value="<?= Security::html($dateFilter) ?>" onchange="this.form.submit()">
    </form>
</div>

<div class="card" style="overflow-x:auto;">
    <table style="width:100%; border-collapse: collapse; min-width: 800px; text-align: left;">
        <thead>
            <tr style="border-bottom: 2px solid #e2e8f0; color: #64748b; font-size: 13px;">
                <th style="padding: 12px 10px;">Nom et Prénom</th>
                <th style="padding: 12px 10px;">Lieu d'affectation</th>
                <th style="padding: 12px 10px;">Heure d'entrée</th>
                <th style="padding: 12px 10px;">Heure de départ</th>
                <th style="padding: 12px 10px; text-align:center;">Statut</th>
                <th style="padding: 12px 10px; text-align: center;">Justifier</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($lignes as $l): ?>
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 12px 10px; font-weight: 600;">
                        <?= Security::html($l['nom']) ?>
                    </td>
                    <td style="padding: 12px 10px;"><?= Security::html($l['nom_lieu'] ?? 'Aucun') ?></td>
                    <td style="padding: 12px 10px; font-weight: bold;"><?= $l['heure_entree'] ?></td>
                    <td style="padding: 12px 10px; font-weight: bold;"><?= $l['heure_depart'] ?></td>
                    <td style="padding: 12px 10px; text-align:center;">
                        <span style="background-color: <?= $l['statut_color'] ?>20; color: <?= $l['statut_color'] ?>; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: bold;">
                            <?= $l['statut'] ?>
                        </span>
                    </td>
                    
                    <td style="padding: 12px 10px; text-align: center;">
                        <?php if($l['besoin_justif']): ?>
                            <form method="POST" action="index.php?page=admin_justify_absence" style="display:inline-block;" onsubmit="return confirm('Êtes-vous sûr de vouloir justifier cela ? Cette action enlèvera les pénalités sur le salaire.');">
                                <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken() ?>">
                                <input type="hidden" name="user_id" value="<?= $l['user_id'] ?>">
                                <input type="hidden" name="date" value="<?= Security::html($dateFilter) ?>">
                                <input type="hidden" name="type" value="<?= $l['besoin_justif'] ?>">
                                <button type="submit" class="btn" style="background:#f3f4f6; color:#374151; padding:6px 12px; font-size:12px; border-radius:8px;">
                                    <i class="fa-solid fa-file-signature"></i> Justifier
                                </button>
                            </form>
                        <?php else: ?>
                            <span style="color:#cbd5e1;">-</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if(empty($lignes)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px; color: var(--color-text-muted);">Aucune donnée pour cette date.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <?php if(isset($totalPages) && $totalPages > 1): ?>
    <div style="display:flex; justify-content:center; gap:8px; margin-top:20px; padding-bottom: 20px;">
        <?php for($i = 1; $i <= $totalPages; $i++): ?>
            <a href="index.php?page=admin_xtimes&date=<?= urlencode($dateFilter) ?>&p=<?= $i ?>" class="btn <?= (isset($page) && $page == $i) ? 'btn-primary' : '' ?>" style="<?= (isset($page) && $page == $i) ? '' : 'background:#f1f5f9; color:#64748b; border:1px solid #e2e8f0;' ?> padding:6px 12px; border-radius:8px; font-weight:600; text-decoration:none; font-size:12px;">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<?php if(isset($_GET['success']) && $_GET['success'] == 'justified'): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        Toast.fire({ icon: 'success', title: 'Justification enregistrée avec succès' });
    });
</script>
<?php endif; ?>

<script>
    // Système d'actualisation en temps réel (invisible pour l'utilisateur)
    setInterval(async function() {
        // Optionnel : Désactiver l'actualisation si une boîte de dialogue SWEETALERT ou window.confirm() est ouverte
        // mais window.confirm() bloque déjà l'exécution du JS sur le thread principal.
        
        try {
            const currentUrl = window.location.href;
            const response = await fetch(currentUrl);
            const html = await response.text();
            
            // On parse le HTML reçu pour récupérer uniquement le contenu de <tbody>
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, "text/html");
            const newTbody = doc.querySelector("tbody");
            
            if (newTbody) {
                document.querySelector("tbody").innerHTML = newTbody.innerHTML;
            }
        } catch(e) {
            console.error("Erreur de synchronisation temps réel Xtimes", e);
        }
    }, 15000); // 15 secondes d'intervalle
</script>
