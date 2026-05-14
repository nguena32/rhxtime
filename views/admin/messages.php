<?php
// views/admin/messages.php
global $pdo;
require_once 'models/Message.php';
require_once 'models/User.php';

$msgModel  = new Message($pdo);
$userModel = new User($pdo);

$active_uid  = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$conversations = $msgModel->getLastMessagesPerUser();
$active_user   = null;
$history       = [];
$lastId        = 0;

if ($active_uid) {
    $msgModel->markAsReadForAdmin($active_uid);
    $active_user = $userModel->findById($active_uid);
    $history     = $msgModel->getConversation($active_uid);
    $lastId      = $msgModel->getLastId($active_uid);
}
$csrf = Security::generateCsrfToken();
?>
<style>
    .msg-layout { display: flex; height: calc(100vh - 120px); border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border: 1px solid #e2e8f0; }

    /* ===== SIDEBAR ===== */
    .msg-sidebar { width: 340px; flex-shrink: 0; background: #fff; border-right: 1px solid #e9edef; display: flex; flex-direction: column; }
    .msg-sidebar-header { background: #128c7e; color: #fff; padding: 16px 20px; font-size: 18px; font-weight: 700; flex-shrink: 0; }
    .conv-list { flex: 1; overflow-y: auto; }
    .conv-item { display: flex; align-items: center; gap: 12px; padding: 13px 16px; cursor: pointer; border-bottom: 1px solid #f0f0f0; transition: background 0.15s; text-decoration: none; color: inherit; }
    .conv-item:hover, .conv-item.active { background: #f0f2f5; }
    .conv-avatar { width: 46px; height: 46px; border-radius: 50%; background: #128c7e; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
    .conv-info { flex: 1; min-width: 0; }
    .conv-name { font-size: 15px; font-weight: 600; color: #111b21; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .conv-preview { font-size: 13px; color: #667781; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 2px; }
    .conv-meta { text-align: right; flex-shrink: 0; }
    .conv-time { font-size: 11px; color: #667781; }
    .conv-badge { background: #25d366; color: #fff; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; margin-top: 4px; margin-left: auto; }

    /* ===== CHAT AREA ===== */
    .msg-chat { flex: 1; display: flex; flex-direction: column; background: #e5ddd5; min-width: 0; }
    .chat-top { background: #128c7e; color: #fff; padding: 12px 20px; display: flex; align-items: center; gap: 14px; flex-shrink: 0; }
    .chat-top .c-avatar { width: 40px; height: 40px; border-radius: 50%; background: #25d366; display: flex; align-items: center; justify-content: center; }
    .chat-top .c-name { font-weight: 700; font-size: 16px; }
    .chat-top .c-status { font-size: 12px; opacity: 0.85; }

    .chat-zone { flex: 1; overflow-y: auto; padding: 14px; display: flex; flex-direction: column; gap: 6px; }

    .bubble-wrap { display: flex; flex-direction: column; max-width: 65%; }
    .bubble-wrap.sent   { align-self: flex-end; align-items: flex-end; }
    .bubble-wrap.recv   { align-self: flex-start; align-items: flex-start; }
    .bubble { padding: 9px 13px 4px; border-radius: 12px; font-size: 14px; line-height: 1.55; box-shadow: 0 1px 2px rgba(0,0,0,0.1); word-wrap: break-word; position: relative; }
    .bubble.sent { background: #dcf8c6; border-bottom-right-radius: 3px; }
    .bubble.recv { background: #fff; border-bottom-left-radius: 3px; }
    .bubble-time { font-size: 10px; color: #667781; margin-top: 3px; display: block; text-align: right; }
    .check { color: #53bdeb; margin-left: 3px; }

    .day-sep { text-align: center; margin: 8px; }
    .day-sep span { background: #e1f2fb; color: #54656f; font-size: 11px; padding: 4px 12px; border-radius: 10px; }

    .chat-input { background: #f0f2f5; padding: 10px 16px; display: flex; align-items: flex-end; gap: 10px; flex-shrink: 0; border-top: 1px solid #e2e8f0; }
    .chat-input textarea { flex: 1; border: none; border-radius: 20px; padding: 10px 16px; font-size: 14px; resize: none; outline: none; max-height: 120px; min-height: 44px; background: #fff; font-family: 'Inter',sans-serif; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
    .send-btn { width: 44px; height: 44px; border-radius: 50%; background: #128c7e; color: #fff; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 16px; flex-shrink: 0; transition: background 0.2s; }
    .send-btn:hover { background: #0e7366; }
    .send-btn:disabled { background: #ccc; }

    .empty-state { flex: 1; display: flex; align-items: center; justify-content: center; flex-direction: column; gap: 12px; color: #8696a0; }
    .empty-state i { font-size: 60px; }

    @media (max-width: 768px) {
        .msg-sidebar { width: 100%; display: <?= $active_uid ? 'none' : 'flex' ?>; }
        .msg-chat { display: <?= $active_uid ? 'flex' : 'none' ?>; }
    }
</style>

<div class="msg-layout">

    <!-- SIDEBAR CONVERSATIONS -->
    <div class="msg-sidebar">
        <div class="msg-sidebar-header"><i class="fa-solid fa-comments" style="margin-right:8px;"></i>Messagerie</div>
        <div class="conv-list" id="convList">
            <?php foreach ($conversations as $c): ?>
            <?php $unread = ($c['direction'] === 'to_admin' && !$c['is_read']); ?>
            <a href="index.php?page=admin_messages&user_id=<?= $c['user_id'] ?>"
               class="conv-item <?= ($active_uid == $c['user_id']) ? 'active' : '' ?>">
                <div class="conv-avatar"><i class="fa-solid fa-user"></i></div>
                <div class="conv-info">
                    <div class="conv-name"><?= Security::html($c['prenom'].' '.$c['nom']) ?></div>
                    <div class="conv-preview <?= $unread ? 'font-weight:700;' : '' ?>">
                        <?= ($c['direction']==='to_employee' ? '↩ ' : '') . Security::html(mb_substr($c['content'],0,40)) ?>
                    </div>
                </div>
                <div class="conv-meta">
                    <div class="conv-time"><?= date('H:i', strtotime($c['created_at'])) ?></div>
                    <?php if ($unread): ?>
                        <div class="conv-badge">!</div>
                    <?php endif; ?>
                </div>
            </a>
            <?php endforeach; ?>
            <?php if (empty($conversations)): ?>
                <p style="text-align:center; color:#94a3b8; padding:30px 20px; font-size:14px;">Aucun message reçu.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- ZONE CHAT -->
    <div class="msg-chat">
        <?php if ($active_user): ?>
        <div class="chat-top">
            <div class="c-avatar"><i class="fa-solid fa-user"></i></div>
            <div>
                <div class="c-name"><?= Security::html($active_user['prenom'].' '.$active_user['nom']) ?></div>
                <div class="c-status"><span style="display:inline-block;width:8px;height:8px;background:#25d366;border-radius:50%;margin-right:4px;"></span>Employé</div>
            </div>
        </div>

        <div class="chat-zone" id="chatZone">
            <?php
            $prevDay = null;
            foreach ($history as $m):
                $day = date('d/m/Y', strtotime($m['created_at']));
                if ($day !== $prevDay):
                    $prevDay = $day;
                    $dayLabel = (date('Y-m-d') === date('Y-m-d', strtotime($m['created_at']))) ? "Aujourd'hui" : $day;
            ?>
                <div class="day-sep"><span><?= $dayLabel ?></span></div>
            <?php endif;
                $isSent = ($m['direction'] === 'to_employee');
                $cls    = $isSent ? 'sent' : 'recv';
            ?>
            <div class="bubble-wrap <?= $cls ?>">
                <div class="bubble <?= $cls ?>">
                    <?= Security::html($m['content']) ?>
                    <span class="bubble-time">
                        <?= date('H:i', strtotime($m['created_at'])) ?>
                        <?php if ($isSent): ?><i class="fa-solid fa-check-double check"></i><?php endif; ?>
                    </span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div id="adminStatusLine" style="font-size:11px; color:#667781; padding:2px 20px; min-height:15px;"></div>
        <div class="chat-input">
            <textarea id="adminMsgInput" placeholder="Message..." rows="1" maxlength="255"
                      onkeydown="handleAdminKey(event)" oninput="autoResize(this)"></textarea>
            <button class="send-btn" id="adminSendBtn" onclick="adminSendMsg()">
                <i class="fa-solid fa-paper-plane"></i>
            </button>
        </div>

        <?php else: ?>
        <div class="empty-state">
            <i class="fa-regular fa-comments"></i>
            <p>Sélectionnez une conversation</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($active_uid): ?>
<script>
const CSRF     = '<?= $csrf ?>';
const ACTIVE_UID = <?= $active_uid ?>;
let   lastId   = <?= $lastId ?>;
 
const zone     = document.getElementById('chatZone');
const input    = document.getElementById('adminMsgInput');
const btn      = document.getElementById('adminSendBtn');
const statusLn = document.getElementById('adminStatusLine');

function scrollBottom() { zone.scrollTop = zone.scrollHeight; }
scrollBottom();

let currentDay = null;
function renderBubble(m) {
    const mDay = new Date(m.created_at).toISOString().slice(0, 10);
    if (mDay !== currentDay) {
        currentDay = mDay;
        const today = new Date().toISOString().slice(0, 10);
        const label = (mDay === today) ? "Aujourd'hui" : new Date(m.created_at).toLocaleDateString('fr-FR');
        zone.insertAdjacentHTML('beforeend', `<div class="day-sep"><span>${label}</span></div>`);
    }
    const isSent = m.direction === 'to_employee';
    const cls    = isSent ? 'sent' : 'recv';
    const time   = new Date(m.created_at).toLocaleTimeString('fr-FR', {hour:'2-digit',minute:'2-digit'});
    const check  = isSent ? '<i class="fa-solid fa-check-double check"></i>' : '';
    const safe   = m.content.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    zone.insertAdjacentHTML('beforeend', `
        <div class="bubble-wrap ${cls}">
            <div class="bubble ${cls}">
                ${safe}<span class="bubble-time">${time} ${check}</span>
            </div>
        </div>`);
}

/* POLLING */
async function poll() {
    try {
        const r = await fetch(`index.php?page=api_msg_poll&user_id=${ACTIVE_UID}&last_id=${lastId}`);
        const data = await r.json();
        if (data.messages?.length > 0) {
            data.messages.forEach(m => { renderBubble(m); lastId = Math.max(lastId, m.id); });
            scrollBottom();
        }
    } catch(e){}
    setTimeout(poll, 2500);
}
poll();

/* ENVOI */
async function adminSendMsg() {
    const text = input.value.trim();
    if (!text || btn.disabled) return;
    
    btn.disabled = true;
    statusLn.textContent = 'Envoi...';
    
    const form = new FormData();
    form.append('csrf_token', CSRF);
    form.append('content',    text);
    form.append('user_id',    ACTIVE_UID);
    form.append('last_id',    lastId);
    
    const oldText = text;
    input.value = '';
    autoResize(input);
    
    try {
        const r = await fetch('index.php?page=api_msg_send', {method:'POST', body:form});
        if (!r.ok) {
            const errData = await r.json().catch(() => ({}));
            statusLn.textContent = errData.message || 'Erreur serveur (' + r.status + ')';
            input.value = oldText;
            btn.disabled = false;
            return;
        }
        const data = await r.json();
        if (data.ok && data.messages) {
            data.messages.forEach(m => { renderBubble(m); lastId = Math.max(lastId, m.id); });
            scrollBottom();
            statusLn.textContent = '';
        } else {
            statusLn.textContent = data.message || 'Erreur lors de l\'envoi';
            input.value = oldText;
        }
    } catch(e) {
        statusLn.textContent = 'Problème de connexion.';
        input.value = oldText;
    }
    btn.disabled = false;
    input.focus();
}

function handleAdminKey(e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); adminSendMsg(); }
}
function autoResize(el) {
    el.style.height = 'auto';
    el.style.height = Math.min(el.scrollHeight, 120) + 'px';
}
</script>
<?php endif; ?>
