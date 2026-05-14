<div class="card">
    <h2 class="text-h2" style="margin-bottom: 20px;"><i class="fa-solid fa-bullhorn" style="color:var(--color-primary);"></i> Gestion de l'espace Publicité</h2>
    
    <?php if(isset($_GET['success'])): ?>
        <div style="background:#dcfce7; color:#166534; padding:15px; border-radius:8px; margin-bottom:20px; font-weight: 500;">
            <i class="fa-solid fa-check-circle"></i> Paramètres mis à jour avec succès.
        </div>
    <?php endif; ?>

    <form method="POST" action="index.php?page=admin_save_pub" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken() ?>">
        
        <div class="form-group">
            <label>Image pub (formats acceptés: JPG, PNG, WEBP, GIF)</label>
            <input type="file" name="image" class="form-control" accept="image/*">
            <?php if(!empty($pub['image'])): ?>
                <div style="margin-top:10px;">
                    <img src="<?= htmlspecialchars($pub['image']) ?>" alt="Pub" style="max-height: 150px; border-radius: 8px; border: 1px solid #e2e8f0;">
                </div>
            <?php else: ?>
                <small style="color: gray;">Aucune image de publicité actuelement configurée.</small>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label>Lien de redirection (URL lors du clic)</label>
            <input type="url" name="link" class="form-control" placeholder="https://exemple.com/promo" value="<?= htmlspecialchars($pub['link'] ?? '') ?>">
            <small style="color: var(--color-text-muted);">Mettez un lien complet incluant http:// ou https://</small>
        </div>

        <button type="submit" class="btn btn-primary" style="margin-top: 10px;">
            <i class="fa-solid fa-save"></i> Enregistrer
        </button>
    </form>
</div>
