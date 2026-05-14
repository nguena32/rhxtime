<?php
// views/superadmin/support_view.php
?>
<div class="header-actions" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px;">
    <div>
        <a href="index.php?page=superadmin_support" style="text-decoration:none; color:var(--color-primary); font-size:13px; font-weight:600;"><i class="fa-solid fa-arrow-left"></i> Retour aux tickets</a>
        <h2 style="font-weight: 800; color: var(--color-secondary); margin: 5px 0 0 0;">Ticket #<?= $ticket['id'] ?> : <?= htmlspecialchars($ticket['subject']) ?></h2>
        <div style="font-size:13px; color:#64748b; margin-top:5px;">Client : <?= htmlspecialchars($ticket['entreprise_nom']) ?></div>
    </div>
    <div style="display:flex; gap:10px;">
        <?php if($ticket['status'] !== 'closed'): ?>
            <a href="index.php?page=superadmin_support_close&id=<?= $ticket['id'] ?>" class="btn-outline" style="border-color:#ef4444; color:#ef4444; padding:8px 15px; font-size:13px; border-radius:10px;" onclick="return confirm('Clôturer ce ticket ?')">Clôturer le ticket</a>
        <?php endif; ?>
    </div>
</div>

<div style="display:flex; flex-direction:column; gap:20px; max-width:1000px; margin:0 auto; padding-bottom:100px;">
    <?php foreach($messages as $msg): ?>
        <?php $isMe = ($msg['sender_role'] === 'superadmin'); ?>
        <div style="display:flex; justify-content: <?= $isMe ? 'flex-end' : 'flex-start' ?>;">
            <div style="max-width:80%; min-width:350px; background: <?= $isMe ? '#334155' : '#fff' ?>; color: <?= $isMe ? '#fff' : '#1e293b' ?>; padding:20px; border-radius:20px; <?= $isMe ? 'border-bottom-right-radius:4px;' : 'border-bottom-left-radius:4px;' ?> box-shadow:0 10px 15px -3px rgba(0,0,0,0.1); border: <?= $isMe ? 'none' : '2px solid #e2e8f0' ?>;">
                <div style="display:flex; justify-content:space-between; margin-bottom:10px; font-size:12px; opacity:0.8;">
                    <strong><?= $isMe ? 'Support (Moi)' : 'Client : ' . htmlspecialchars($ticket['entreprise_nom']) ?></strong>
                    <span><?= date('d/m/Y H:i', strtotime($msg['created_at'])) ?></span>
                </div>
                <div style="font-size:15px; line-height:1.6; white-space: pre-wrap;"><?= htmlspecialchars($msg['message']) ?></div>
                
                <?php if(!empty($msg['attachment_path'])): ?>
                    <div style="margin-top:15px; padding:10px; background:rgba(255,255,255,0.1); border-radius:8px; border:1px solid rgba(0,0,0,0.1);">
                        <a href="<?= $msg['attachment_path'] ?>" target="_blank" style="color: <?= $isMe ? '#fff' : 'var(--color-primary)' ?>; text-decoration:none; font-size:13px; font-weight:600; display:flex; align-items:center; gap:10px;">
                            <i class="fa-solid fa-paperclip"></i> Voir la pièce jointe
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if($ticket['status'] !== 'closed'): ?>
        <div class="card" style="margin-top:20px; padding:25px; border-radius:20px; border-color:#334155; border-width:2px; background:#fff;">
            <form action="index.php?page=superadmin_support_reply" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken() ?>">
                <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:15px;">
                    <i class="fa-solid fa-reply-all" style="color:#334155;"></i>
                    <label style="font-size:14px; font-weight:700; color:#334155;">Répondre au client :</label>
                </div>

                <textarea name="message" required rows="5" style="width:100%; border:1px solid #e2e8f0; border-radius:12px; padding:15px; font-family:inherit; margin-bottom:15px; resize:none; background:#f8fafc;" placeholder="Saisissez votre réponse ici..."></textarea>
                
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <input type="file" name="attachment" style="font-size:12px;">
                    </div>
                    <button type="submit" class="btn-primary" style="background:#334155; border-color:#334155; padding:10px 40px; border-radius:12px; font-weight:700; color:#fff;">Envoyer la réponse</button>
                </div>
            </form>
        </div>
    <?php else: ?>
        <div style="text-align:center; padding:30px; background:#f1f5f9; border-radius:20px; color:#475569; font-weight:600; border:1px dashed #cbd5e1;">
            <i class="fa-solid fa-lock"></i> Ce ticket est désormais clos.
        </div>
    <?php endif; ?>
</div>
