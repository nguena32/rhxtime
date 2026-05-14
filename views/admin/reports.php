<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 class="text-h2"><i class="fa-solid fa-file-contract" style="color:var(--color-primary); margin-right:10px;"></i> Rapport de pointages</h2>
        <div class="badge badge-success">Export PDF (Bientôt)</div>
    </div>

    <!-- FILTRES -->
    <form method="GET" action="index.php" class="filter-card" style="background:#f1f5f9; padding:20px; border-radius:var(--radius-sm); margin-bottom:30px;">
        <input type="hidden" name="page" value="admin_reports">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: end;">
            <div class="form-group" style="margin-bottom:0;">
                <label>Employé</label>
                <select name="user_id" class="form-control" style="margin-bottom:0;">
                    <option value="">Tous les employés</option>
                    <?php foreach($all_users as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= (isset($_GET['user_id']) && $_GET['user_id'] == $u['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($u['nom'] . ' ' . $u['prenom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label>Date Début</label>
                <input type="date" name="date_start" class="form-control" value="<?= htmlspecialchars($date_start) ?>" style="margin-bottom:0;">
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label>Date Fin</label>
                <input type="date" name="date_end" class="form-control" value="<?= htmlspecialchars($date_end) ?>" style="margin-bottom:0;">
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label>Statut</label>
                <select name="status_filter" class="form-control" style="margin-bottom:0;">
                    <option value="">Tous les statuts</option>
                    <option value="En retard" <?= (isset($_GET['status_filter']) && $_GET['status_filter'] == 'En retard') ? 'selected' : '' ?>>En retard uniquement</option>
                    <option value="Absent" <?= (isset($_GET['status_filter']) && $_GET['status_filter'] == 'Absent') ? 'selected' : '' ?>>Absents uniquement</option>
                    <option value="A l'heure" <?= (isset($_GET['status_filter']) && $_GET['status_filter'] == "A l'heure") ? 'selected' : '' ?>>À l'heure uniquement</option>
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-primary" style="width:100%;">
                    <i class="fa-solid fa-filter"></i> Filtrer
                </button>
            </div>
        </div>
    </form>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Employé</th>
                    <th>Lieu</th>
                    <th>Entrée</th>
                    <th>Départ</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($reportData)): ?>
                    <tr>
                        <td colspan="6" style="text-align:center; padding:40px; color:var(--color-text-muted);">
                            <i class="fa-solid fa-folder-open" style="font-size:40px; display:block; margin-bottom:10px; opacity:0.3;"></i>
                            Aucune donnée trouvée pour cette période.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach($reportData as $row): ?>
                        <tr>
                            <td style="font-weight:600;"><?= date('d/m/Y', strtotime($row['date'])) ?></td>
                            <td><?= htmlspecialchars($row['nom']) ?></td>
                            <td><span style="font-size:12px; color:var(--color-text-muted);"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($row['nom_lieu']) ?></span></td>
                            <td style="font-family:monospace; font-weight:700; color:var(--color-primary);"><?= $row['heure_entree'] ?></td>
                            <td style="font-family:monospace; font-weight:700; color:var(--color-text-muted);"><?= $row['heure_depart'] ?></td>
                            <td>
                                <span class="badge" style="background-color: <?= $row['statut_color'] ?>22; color: <?= $row['statut_color'] ?>; border: 1px solid <?= $row['statut_color'] ?>;">
                                    <?= $row['statut'] ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.filter-card {
    border: 1px solid var(--color-border);
    transition: all 0.3s;
}
.filter-card:hover {
    box-shadow: var(--shadow-sm);
}
</style>
