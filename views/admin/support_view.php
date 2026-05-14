<?php
// views/admin/support_view.php
?>
<div class="header-actions" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px;">
    <div>
        <a href="index.php?page=admin_support" style="text-decoration:none; color:var(--color-primary); font-size:13px; font-weight:600;"><i class="fa-solid fa-arrow-left"></i> Retour à mes tickets</a>
        <h2 style="font-weight: 800; color: var(--color-secondary); margin: 5px 0 0 0;">Ticket #<?= $ticket['id'] ?> : <?= htmlspecialchars($ticket['subject']) ?></h2>
    </div>
    <div>
        <?php if($ticket['status'] === 'open'): ?>
            <span class="badge badge-warning">En attente de réponse</span>
        <?php elseif($ticket['status'] === 'replied'): ?>
            <span class="badge badge-success">Soutien a répondu</span>
        <?php else: ?>
            <span class="badge badge-secondary">Ticket clôturé</span>
        <?php endif; ?>
    </div>
</div>

<div style="display:flex; flex-direction:column; gap:20px; max-width:900px; margin:0 auto; padding-bottom:100px;">
    <?php foreach($messages as $msg): ?>
        <?php $isMe = ($msg['sender_role'] === 'admin'); ?>
        <div style="display:flex; justify-content: <?= $isMe ? 'flex-end' : 'flex-start' ?>;">
            <div style="max-width:80%; min-width:300px; background: <?= $isMe ? 'var(--color-primary)' : '#fff' ?>; color: <?= $isMe ? '#fff' : '#1e293b' ?>; padding:20px; border-radius:20px; <?= $isMe ? 'border-bottom-right-radius:4px;' : 'border-bottom-left-radius:4px;' ?> box-shadow:0 10px 15px -3px rgba(0,0,0,0.1); border: <?= $isMe ? 'none' : '1px solid #e2e8f0' ?>;">
                <div style="display:flex; justify-content:space-between; margin-bottom:10px; font-size:12px; opacity:0.8;">
                    <strong><?= $isMe ? 'Moi' : 'Support RHXtimes' ?></strong>
                    <span><?= date('d/m/Y H:i', strtotime($msg['created_at'])) ?></span>
                </div>
                <div style="font-size:15px; line-height:1.6; white-space: pre-wrap;"><?= htmlspecialchars($msg['message']) ?></div>
                
                <?php if(!empty($msg['attachment_path'])): ?>
                    <div style="margin-top:15px; padding:10px; background:rgba(0,0,0,0.05); border-radius:8px;">
                        <a href="<?= $msg['attachment_path'] ?>" target="_blank" style="color: <?= $isMe ? '#fff' : 'var(--color-primary)' ?>; text-decoration:none; font-size:13px; font-weight:600; display:flex; align-items:center; gap:10px;">
                            <i class="fa-solid fa-paperclip"></i> Voir la pièce jointe
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if($ticket['status'] !== 'closed'): ?>
        <div class="card" style="margin-top:20px; padding:25px; border-radius:20px; border-color:var(--color-primary); border-width:2px;">
            <form action="index.php?page=admin_support_reply" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken() ?>">
                <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                
                <label style="display:block; font-size:14px; font-weight:700; color:#334155; margin-bottom:12px;">Votre réponse :</label>
                <textarea name="message" required rows="4" style="width:100%; border:1px solid #e2e8f0; border-radius:12px; padding:15px; font-family:inherit; margin-bottom:15px; resize:none;" placeholder="Écrivez votre message ici..."></textarea>
                
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <label for="attachment" style="cursor:pointer; color:#64748b; font-size:13px;"><i class="fa-solid fa-paperclip"></i> Ajouter un fichier</label>
                        <input type="file" id="attachment" name="attachment" style="display:none;" onchange="document.getElementById('fileName').innerText = this.files[0].name">
                        <span id="fileName" style="font-size:11px; color:var(--color-primary);"></span>
                    </div>
                    <button type="submit" class="btn-primary" style="padding:10px 30px; border-radius:12px; font-weight:700;">Envoyer</button>
                </div>
            </form>
        </div>
    <?php else: ?>
        <div style="text-align:center; padding:20px; background:#f8fafc; border-radius:12px; color:#64748b; font-size:14px; border:1px dashed #cbd5e1;">
            La conversation est terminée. Ce ticket est clôturé.
        </div>
    <?php endif; ?>
</div>
