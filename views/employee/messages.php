<?php
global $pdo;
require_once 'models/Message.php';
$msgModel = new Message($pdo);
$user_id  = $_SESSION['user_id'];
$msgModel->markAsReadForEmployee($user_id);
$history  = $msgModel->getConversation($user_id);
$lastId   = $msgModel->getLastId($user_id);
$csrf     = Security::generateCsrfToken();
$nom      = htmlspecialchars($_SESSION['user_nom'] ?? 'Employé');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<title>Messages | RHXtimes</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root{--rhx-primary:#0f4c81;--rhx-accent:#f97316;--rhx-dark:#0f172a;--rhx-success:#10b981;--rhx-muted:#64748b;}
*{box-sizing:border-box;margin:0;padding:0;-webkit-tap-highlight-color:transparent;}
body{font-family:'Inter',sans-serif;background:#0f172a;display:flex;flex-direction:column;height:100dvh;overflow:hidden;color:#1e293b;}

/* HEADER */
.chat-header{background:linear-gradient(135deg,var(--rhx-primary) 0%,#1e6fb8 100%);padding:12px 16px;display:flex;align-items:center;gap:14px;flex-shrink:0;box-shadow:0 4px 20px rgba(0,0,0,.3);safe-area-inset-top:env(safe-area-inset-top);}
.back-btn{width:44px;height:44px;border-radius:50%;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;color:#fff;font-size:18px;text-decoration:none;flex-shrink:0;transition:background .2s;}
.back-btn:hover{background:rgba(255,255,255,.25);}
.header-avatar{width:44px;height:44px;border-radius:50%;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:#fff;flex-shrink:0;border:2px solid rgba(255,255,255,.3);}
.header-info h3{font-size:.95rem;font-weight:700;color:#fff;}
.header-info span{font-size:.75rem;color:rgba(255,255,255,.75);display:flex;align-items:center;gap:5px;}
.online-dot{width:7px;height:7px;background:#10b981;border-radius:50%;display:inline-block;animation:pulse-g 2s infinite;}
@keyframes pulse-g{0%,100%{box-shadow:0 0 0 0 rgba(16,185,129,.5);}70%{box-shadow:0 0 0 5px rgba(16,185,129,0);}}

/* MESSAGES ZONE */
.messages-zone{flex:1;overflow-y:auto;padding:16px 12px 8px;display:flex;flex-direction:column;gap:8px;background:#f0f4f8;}
.messages-zone::-webkit-scrollbar{width:3px;}
.messages-zone::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:10px;}

/* BUBBLES */
.bubble-wrap{display:flex;flex-direction:column;max-width:80%;}
.bubble-wrap.sent{align-self:flex-end;align-items:flex-end;}
.bubble-wrap.recv{align-self:flex-start;align-items:flex-start;}
.bubble{padding:10px 14px 6px;border-radius:16px;font-size:.9rem;line-height:1.5;word-wrap:break-word;box-shadow:0 1px 4px rgba(0,0,0,.1);position:relative;}
.bubble.sent{background:linear-gradient(135deg,#0f4c81,#1e6fb8);color:#fff;border-bottom-right-radius:4px;}
.bubble.recv{background:#fff;color:#1e293b;border-bottom-left-radius:4px;border:1px solid #e2e8f0;}
.bubble-time{font-size:.7rem;margin-top:4px;display:block;color:rgba(255,255,255,.65);}
.bubble.recv .bubble-time{color:#94a3b8;}
.check{margin-left:3px;font-size:.65rem;}

/* DAY SEPARATOR */
.day-sep{text-align:center;margin:10px 0;}
.day-sep span{background:#e1eaf5;color:#64748b;font-size:.72rem;font-weight:700;padding:4px 14px;border-radius:50px;}

/* INPUT BAR */
.input-bar{padding:10px 12px calc(10px + env(safe-area-inset-bottom));background:#fff;display:flex;align-items:flex-end;gap:10px;flex-shrink:0;border-top:1px solid #e2e8f0;box-shadow:0 -4px 20px rgba(0,0,0,.06);}
.input-bar textarea{flex:1;border:2px solid #e2e8f0;border-radius:20px;padding:11px 16px;font-family:'Inter',sans-serif;font-size:.9rem;resize:none;outline:none;max-height:120px;min-height:44px;line-height:1.4;background:#f8fafc;transition:border-color .2s;}
.input-bar textarea:focus{border-color:var(--rhx-primary);background:#fff;}
.send-btn{width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,var(--rhx-primary),#1e6fb8);color:#fff;border:none;display:flex;align-items:center;justify-content:center;cursor:pointer;flex-shrink:0;transition:all .2s;font-size:1rem;box-shadow:0 4px 12px rgba(15,76,129,.35);}
.send-btn:hover{transform:scale(1.05);}
.send-btn:disabled{background:#cbd5e1;box-shadow:none;cursor:default;}

/* STATUS LINE */
.status-line{font-size:.75rem;color:#94a3b8;padding:2px 14px;min-height:18px;flex-shrink:0;background:#fff;}
</style>
</head>
<body>

<div class="chat-header">
  <a href="index.php?page=employee_scan" class="back-btn"><i class="fa-solid fa-arrow-left"></i></a>
  <div class="header-avatar"><i class="fa-solid fa-user-tie"></i></div>
  <div class="header-info">
    <h3>Direction</h3>
    <span><span class="online-dot"></span>Votre équipe d'administration</span>
  </div>
</div>

<div class="messages-zone" id="messagesZone">
<?php
$prevDay=null;
foreach($history as $m):
  $day=date('d/m/Y',strtotime($m['created_at']));
  if($day!==$prevDay):$prevDay=$day;$dayLabel=(date('Y-m-d')===date('Y-m-d',strtotime($m['created_at'])))?"Aujourd'hui":$day;
?>
  <div class="day-sep"><span><?= $dayLabel ?></span></div>
<?php endif; $isSent=($m['direction']==='to_admin'); $cls=$isSent?'sent':'recv'; ?>
  <div class="bubble-wrap <?= $cls ?>">
    <div class="bubble <?= $cls ?>">
      <?= Security::html($m['content']) ?>
      <span class="bubble-time">
        <?= date('H:i',strtotime($m['created_at'])) ?>
        <?php if($isSent): ?><i class="fa-solid fa-check-double check"></i><?php endif; ?>
      </span>
    </div>
  </div>
<?php endforeach; ?>
</div>

<div class="status-line" id="statusLine"></div>

<div class="input-bar">
  <textarea id="msgInput" placeholder="Message..." rows="1" maxlength="255"
    onkeydown="handleKey(event)" oninput="autoResize(this)"></textarea>
  <button class="send-btn" id="sendBtn" onclick="sendMsg()">
    <i class="fa-solid fa-paper-plane"></i>
  </button>
</div>

<script>
const CSRF=`<?= $csrf ?>`,USER_ID=<?= (int)$user_id ?>;
let lastId=<?= $lastId ?>,polling=true;
const zone=document.getElementById('messagesZone'),input=document.getElementById('msgInput'),sendBtn=document.getElementById('sendBtn'),statusLn=document.getElementById('statusLine');

function scrollBottom(){zone.scrollTop=zone.scrollHeight;}
scrollBottom();

function dayLabel(dateStr){const today=new Date().toISOString().slice(0,10),d=new Date(dateStr),dStr=d.toISOString().slice(0,10);return dStr===today?"Aujourd'hui":d.toLocaleDateString('fr-FR');}
let currentDay=null;
function renderBubble(m){
  const mDay=new Date(m.created_at).toISOString().slice(0,10);
  if(mDay!==currentDay){currentDay=mDay;zone.insertAdjacentHTML('beforeend',`<div class="day-sep"><span>${dayLabel(m.created_at)}</span></div>`);}
  const isSent=m.direction==='to_admin',cls=isSent?'sent':'recv';
  const time=new Date(m.created_at).toLocaleTimeString('fr-FR',{hour:'2-digit',minute:'2-digit'});
  const check=isSent?'<i class="fa-solid fa-check-double check"></i>':'';
  const safe=m.content.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
  zone.insertAdjacentHTML('beforeend',`<div class="bubble-wrap ${cls}"><div class="bubble ${cls}">${safe}<span class="bubble-time">${time} ${check}</span></div></div>`);
}

async function poll(){if(!polling)return;try{const r=await fetch(`index.php?page=api_msg_poll&last_id=${lastId}`),data=await r.json();if(data.messages&&data.messages.length>0){data.messages.forEach(m=>{renderBubble(m);lastId=Math.max(lastId,m.id);});scrollBottom();}}catch(e){}setTimeout(poll,2500);}
poll();

async function sendMsg(){
  const text=input.value.trim();if(!text||sendBtn.disabled)return;
  sendBtn.disabled=true;statusLn.textContent='Envoi...';
  const form=new FormData();form.append('csrf_token',CSRF);form.append('content',text);form.append('last_id',lastId);
  input.value='';autoResize(input);
  try{const r=await fetch('index.php?page=api_msg_send',{method:'POST',body:form});
    if(!r.ok){const e=await r.json().catch(()=>({}));statusLn.textContent=e.message||'Erreur serveur';sendBtn.disabled=false;return;}
    const data=await r.json();
    if(data.ok&&data.messages){data.messages.forEach(m=>{renderBubble(m);lastId=Math.max(lastId,m.id);});scrollBottom();statusLn.textContent='';}
    else statusLn.textContent=data.message||'Erreur';
  }catch(e){statusLn.textContent='Problème de connexion.';}
  sendBtn.disabled=false;input.focus();
}

function handleKey(e){if(e.key==='Enter'&&!e.shiftKey){e.preventDefault();sendMsg();}}
function autoResize(el){el.style.height='auto';el.style.height=Math.min(el.scrollHeight,120)+'px';}
document.addEventListener('visibilitychange',()=>{polling=!document.hidden;if(!document.hidden)poll();});
</script>
</body>
</html>
