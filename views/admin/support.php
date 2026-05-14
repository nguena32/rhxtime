<?php
// views/admin/support.php
?>
<div class="header-actions" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px;">
    <div>
        <h2 style="font-weight: 800; color: var(--color-secondary); margin: 0;">Support & Aide</h2>
        <p style="margin: 4px 0 0 0; font-size: 13px; color: #64748b;">Communiquez directement avec l'équipe RHXtimes.</p>
    </div>
    <button onclick="document.getElementById('modalCreateTicket').style.display='flex'" class="btn-primary" style="padding:10px 20px; border-radius:10px;">
        <i class="fa-solid fa-plus"></i> Nouveau Ticket
    </button>
</div>

<?php if(isset($_GET['success'])): ?>
    <div style="background:#dcfce7; color:#16a34a; padding:15px; border-radius:10px; margin-bottom:20px; font-size:14px; font-weight:600; border:1px solid #bbf7d0;">
        <i class="fa-solid fa-circle-check"></i> Votre ticket a été créé avec succès. Notre équipe vous répondra dans les plus brefs délais.
    </div>
<?php endif; ?>

<div class="card" style="padding:0; overflow:hidden;">
    <table class="table" style="margin:0;">
        <thead>
            <tr>
                <th>Sujet</th>
                <th>Priorité</th>
                <th>Statut</th>
                <th>Date</th>
                <th style="text-align:right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($tickets)): ?>
                <tr>
                    <td colspan="5" style="text-align:center; padding:40px; color:#94a3b8;">
                        <i class="fa-solid fa-folder-open" style="font-size:40px; display:block; margin-bottom:10px; opacity:0.3;"></i>
                        Vous n'avez pas encore de tickets de support.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach($tickets as $t): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($t['subject']) ?></strong></td>
                        <td>
                            <?php if($t['priority'] === 'high'): ?>
                                <span class="badge badge-danger">Haute</span>
                            <?php elseif($t['priority'] === 'medium'): ?>
                                <span class="badge badge-warning">Moyenne</span>
                            <?php else: ?>
                                <span class="badge badge-info">Basse</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($t['status'] === 'open'): ?>
                                <span style="color:#f59e0b; font-weight:700;"><i class="fa-solid fa-clock"></i> En attente</span>
                            <?php elseif($t['status'] === 'replied'): ?>
                                <span style="color:#10b981; font-weight:700;"><i class="fa-solid fa-reply"></i> Réponse reçue</span>
                            <?php else: ?>
                                <span style="color:#64748b; font-weight:700;"><i class="fa-solid fa-circle-check"></i> Fermé</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('d/m/Y H:i', strtotime($t['created_at'])) ?></td>
                        <td style="text-align:right;">
                            <a href="index.php?page=admin_support_view&id=<?= $t['id'] ?>" class="btn-outline" style="padding:6px 12px; font-size:12px;">Voir la discussion</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal Nouveau Ticket -->
<div id="modalCreateTicket" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.8); z-index:10000; align-items:center; justify-content:center; padding:20px;">
    <div style="background:#fff; width:100%; max-width:600px; border-radius:20px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.5); position:relative; overflow:hidden;">
        <div style="background:var(--color-primary); padding:20px; color:#fff; display:flex; justify-content:space-between; align-items:center;">
            <h3 style="margin:0; font-size:18px;">Nouveau Ticket de Support</h3>
            <button onclick="document.getElementById('modalCreateTicket').style.display='none'" style="background:none; border:none; color:#fff; font-size:24px; cursor:pointer;">&times;</button>
        </div>
        <form action="index.php?page=admin_support_create" method="POST" enctype="multipart/form-data" style="padding:25px;">
            <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken() ?>">
            
            <div style="margin-bottom:20px;">
                <label style="display:block; font-size:13px; font-weight:700; color:#334155; margin-bottom:8px;">Objet de votre demande</label>
                <input type="text" name="subject" required placeholder="Ex: Problème de géolocalisation sur le site Douala" style="width:100%; padding:12px; border:2px solid #e2e8f0; border-radius:10px; font-family:inherit;">
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block; font-size:13px; font-weight:700; color:#334155; margin-bottom:8px;">Priorité</label>
                <select name="priority" style="width:100%; padding:12px; border:2px solid #e2e8f0; border-radius:10px; font-family:inherit;">
                    <option value="low">Basse</option>
                    <option value="medium" selected>Moyenne</option>
                    <option value="high">Haute / Urgente</option>
                </select>
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block; font-size:13px; font-weight:700; color:#334155; margin-bottom:8px;">Message détaillé</label>
                <textarea name="message" required rows="5" placeholder="Décrivez votre problème ici..." style="width:100%; padding:12px; border:2px solid #e2e8f0; border-radius:10px; font-family:inherit; resize:none;"></textarea>
            </div>

            <div style="margin-bottom:25px;">
                <label style="display:block; font-size:13px; font-weight:700; color:#334155; margin-bottom:8px;">Capture d'écran ou Document (optionnel)</label>
                <input type="file" name="attachment" style="font-size:13px;">
                <div style="font-size:11px; color:#64748b; margin-top:5px;">Extensions autorisées : JPG, PNG, PDF. Max 5Mo.</div>
            </div>

            <button type="submit" class="btn-primary" style="width:100%; padding:14px; border-radius:12px; font-weight:700;">Envoyer ma demande</button>
        </form>
    </div>
</div>
