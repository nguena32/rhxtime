<?php
$pubFile='config/pub.json'; $pub=null;
if(file_exists($pubFile)){$c=file_get_contents($pubFile);if($c)$pub=json_decode($c,true);}
$nom=htmlspecialchars($_SESSION['user_nom']??'Employé');
$heure=date('H'); $salut=$heure<12?'Bonjour':($heure<18?'Bon après-midi':'Bonsoir');
?>
<style>
:root{--rhx-primary:#0f4c81;--rhx-accent:#f97316;--rhx-dark:#0f172a;--rhx-success:#10b981;--rhx-danger:#ef4444;--rhx-warn:#f59e0b;--rhx-muted:#64748b;--rhx-border:#e2e8f0;}
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Inter',sans-serif;background:linear-gradient(160deg,#0f172a 0%,#0f4c81 60%,#1e6fb8 100%);min-height:100vh;padding-bottom:80px;overflow-x:hidden;}
.emp-shell{max-width:480px;margin:0 auto;padding:16px 14px 24px;}
.topbar{display:flex;align-items:center;justify-content:space-between;padding:12px 0 20px;}
.topbar .logo img{height:36px;border-radius:8px;}
.logout-btn{width:44px;height:44px;border-radius:50%;background:rgba(239,68,68,.15);border:1.5px solid rgba(239,68,68,.3);color:#ef4444;font-size:18px;display:flex;align-items:center;justify-content:center;text-decoration:none;transition:all .2s;}
.greeting{text-align:center;margin-bottom:28px;}
.greeting .salut{color:rgba(255,255,255,.65);font-size:.85rem;font-weight:500;margin-bottom:4px;}
.greeting h1{color:#fff;font-size:1.6rem;font-weight:800;letter-spacing:-.3px;}
.status-pill{display:inline-flex;align-items:center;gap:6px;margin-top:10px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);color:rgba(255,255,255,.8);font-size:.78rem;font-weight:600;padding:5px 14px;border-radius:50px;}
.lieu-badge{display:inline-flex;align-items:center;gap:8px;margin-top:12px;background:rgba(16,185,129,.15);border:1.5px solid rgba(16,185,129,.3);color:#6ee7b7;font-size:.82rem;font-weight:700;padding:8px 18px;border-radius:50px;}
.alert-box{padding:14px 16px;border-radius:14px;margin-bottom:16px;font-size:.88rem;font-weight:600;display:flex;align-items:center;gap:10px;}
.alert-danger{background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.3);color:#fca5a5;}
.action-card{background:rgba(255,255,255,.08);backdrop-filter:blur(16px);border:1px solid rgba(255,255,255,.15);border-radius:24px;padding:24px;margin-bottom:20px;}
.btn-pointage{width:100%;padding:32px 20px;border:none;border-radius:20px;font-family:'Inter',sans-serif;font-size:1.1rem;font-weight:800;cursor:pointer;display:flex;flex-direction:column;align-items:center;gap:12px;transition:all .2s;min-height:44px;}
.btn-pointage:active{transform:scale(.97);}
.btn-arrive{background:linear-gradient(135deg,#10b981 0%,#059669 100%);color:#fff;box-shadow:0 12px 30px -5px rgba(16,185,129,.45);}
.btn-depart{background:linear-gradient(135deg,#ef4444 0%,#dc2626 100%);color:#fff;box-shadow:0 12px 30px -5px rgba(239,68,68,.45);}
.btn-pointage i{font-size:2.5rem;filter:drop-shadow(0 4px 8px rgba(0,0,0,.25));}
.btn-pointage .btn-label{font-size:1.15rem;font-weight:900;letter-spacing:.5px;}
.btn-pointage small{font-size:.78rem;font-weight:500;opacity:.85;}

/* Loading */
.loading-card{background:rgba(255,255,255,.95);border-radius:24px;padding:40px 24px;text-align:center;}
.loading-card h2{color:var(--rhx-primary);font-weight:800;font-size:1.1rem;margin-bottom:8px;}
.loading-card p{color:var(--rhx-muted);font-size:.85rem;margin-bottom:20px;}
.loading-icon{font-size:52px;margin-bottom:16px;color:var(--rhx-primary);}
.progress-bar{height:6px;background:#e2e8f0;border-radius:10px;overflow:hidden;margin-top:16px;}
.progress-fill{height:100%;width:0%;background:linear-gradient(90deg,var(--rhx-primary),var(--rhx-accent));border-radius:10px;transition:width .4s ease;}

/* History */
.hist-card{background:rgba(255,255,255,.07);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.12);border-radius:20px;padding:20px;margin-bottom:20px;}
.hist-title{color:rgba(255,255,255,.9);font-size:.9rem;font-weight:700;margin-bottom:14px;display:flex;align-items:center;gap:8px;}
.hist-title i{color:var(--rhx-accent);}
.hist-list{list-style:none;display:flex;flex-direction:column;gap:8px;}
.hist-item{display:flex;justify-content:space-between;align-items:center;background:rgba(255,255,255,.06);border-radius:12px;padding:10px 14px;}
.hist-date{color:rgba(255,255,255,.7);font-size:.82rem;font-weight:600;}
.hist-time{color:#6ee7b7;font-size:.82rem;font-weight:700;display:flex;align-items:center;gap:5px;}
.hist-empty{text-align:center;color:rgba(255,255,255,.4);font-size:.85rem;padding:12px 0;}

/* Pub */
.pub-area{margin-bottom:20px;}
.pub-label{text-align:center;font-size:.7rem;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:1.5px;margin-bottom:8px;}
.pub-area a{display:block;border-radius:16px;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,.3);}
.pub-area img{width:100%;display:block;}

/* Bottom Nav */
.bottom-nav{position:fixed;bottom:0;left:0;right:0;background:rgba(15,23,42,.95);backdrop-filter:blur(16px);border-top:1px solid rgba(255,255,255,.1);display:flex;justify-content:space-around;align-items:center;padding:8px 0 calc(8px + env(safe-area-inset-bottom));z-index:1000;}
.nav-item{display:flex;flex-direction:column;align-items:center;gap:3px;text-decoration:none;color:rgba(255,255,255,.4);font-size:.65rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;padding:6px 16px;border-radius:12px;transition:all .2s;min-width:60px;min-height:44px;justify-content:center;cursor:pointer;background:none;border:none;font-family:'Inter',sans-serif;}
.nav-item i{font-size:1.2rem;}
.nav-item.active{color:var(--rhx-accent);}
.nav-badge{position:relative;display:inline-block;}
.badge-dot{position:absolute;top:-4px;right:-8px;background:var(--rhx-danger);color:#fff;font-size:.6rem;font-weight:800;padding:1px 5px;border-radius:50px;min-width:16px;text-align:center;}

/* Modal */
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(15,23,42,.85);z-index:9999;align-items:center;justify-content:center;backdrop-filter:blur(6px);}
.modal-overlay.open{display:flex;}
.modal-box{background:#fff;border-radius:24px;width:90%;max-width:360px;padding:28px;position:relative;}
.modal-box h3{font-size:1.1rem;font-weight:800;color:var(--rhx-dark);margin-bottom:20px;}
.modal-close{position:absolute;top:14px;right:14px;background:#f1f5f9;border:none;width:32px;height:32px;border-radius:50%;font-size:16px;cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--rhx-muted);}
.modal-field{margin-bottom:14px;}
.modal-field label{display:block;font-size:.8rem;font-weight:700;color:var(--rhx-dark);margin-bottom:6px;}
.modal-field input{width:100%;padding:13px 14px;border:2px solid var(--rhx-border);border-radius:12px;font-family:'Inter',sans-serif;font-size:.9rem;outline:none;transition:border-color .2s;min-height:44px;}
.modal-field input:focus{border-color:var(--rhx-primary);}
.modal-submit{width:100%;padding:14px;background:linear-gradient(135deg,var(--rhx-primary),#1e6fb8);color:#fff;border:none;border-radius:14px;font-family:'Inter',sans-serif;font-size:.95rem;font-weight:700;cursor:pointer;min-height:44px;margin-top:6px;}

/* Alert */
.alert-overlay{display:none;position:fixed;inset:0;background:rgba(15,23,42,.9);z-index:10000;align-items:center;justify-content:center;backdrop-filter:blur(8px);}
.alert-overlay.open{display:flex;}
.alert-box-modal{background:#fff;border-radius:24px;width:85%;max-width:320px;padding:32px 24px;text-align:center;}
#alertIcon{font-size:52px;margin-bottom:16px;}
#alertTitle{font-size:1.2rem;font-weight:800;color:var(--rhx-dark);margin-bottom:8px;}
#alertMessage{font-size:.88rem;color:var(--rhx-muted);line-height:1.6;margin-bottom:24px;white-space:pre-wrap;}
#alertBtn{width:100%;padding:14px;border:none;border-radius:14px;font-family:'Inter',sans-serif;font-size:.95rem;font-weight:800;color:#fff;cursor:pointer;min-height:44px;}
</style>

<div class="emp-shell">
  <div class="topbar">
    <div class="logo"><a href="index.php"><img src="assets/images/logo_texte_blanc.png" alt="RHXtimes"></a></div>
    <a href="index.php?page=logout" class="logout-btn" title="Déconnexion"><i class="fa-solid fa-power-off"></i></a>
  </div>

  <div class="greeting">
    <div class="salut"><?= $salut ?>,</div>
    <h1><?= $nom ?></h1>
    <div style="display:flex;flex-direction:column;align-items:center;gap:8px;">
      <div class="lieu-badge"><i class="fa-solid fa-location-dot"></i><?= htmlspecialchars($lieu_nom) ?></div>
      <div class="status-pill"><i class="fa-solid fa-wifi" style="color:#10b981;"></i> Mode direct (sans QR)</div>
    </div>
  </div>

  <?php if(isset($_GET['error'])): ?>
  <div class="alert-box alert-danger"><i class="fa-solid fa-triangle-exclamation"></i><?= htmlspecialchars($_GET['error']) ?></div>
  <?php endif; ?>

  <!-- ACTION -->
  <div class="action-card" id="step1-choice">
    <?php if(isset($nextAction) && $nextAction==='ARRIVEE'): ?>
    <button id="btnArrivee" onclick="startDirectScan('ARRIVEE')" class="btn-pointage btn-arrive">
      <i class="fa-solid fa-location-dot"></i>
      <span class="btn-label">POINTER MON ARRIVÉE</span>
      <small><i class="fa-solid fa-wifi"></i> Avec One-Tap</small>
    </button>
    <?php else: ?>
    <button id="btnDepart" onclick="startDirectScan('DEPART')" class="btn-pointage btn-depart">
      <i class="fa-solid fa-person-walking-arrow-right"></i>
      <span class="btn-label">POINTER MON DÉPART</span>
      <small><i class="fa-solid fa-wifi"></i> Avec One-Tap</small>
    </button>
    <?php endif; ?>
  </div>

  <!-- LOADING -->
  <div id="step2-loading" style="display:none;">
    <div class="loading-card">
      <div id="loading-icon" class="loading-icon"><i class="fa-solid fa-spinner fa-spin"></i></div>
      <h2 id="loading-title">Localisation en cours...</h2>
      <p id="loading-msg">Veuillez patienter, on vérifie votre position GPS</p>
      <div class="progress-bar"><div class="progress-fill" id="gps-progress"></div></div>
    </div>
  </div>

  <!-- HISTORY -->
  <div id="step3-history">
    <?php if(!empty($history)): ?>
    <div class="hist-card">
      <div class="hist-title"><i class="fa-solid fa-clock-rotate-left"></i> Historique récent</div>
      <ul class="hist-list">
        <?php foreach($history as $item): ?>
        <li class="hist-item">
          <span class="hist-date"><i class="fa-regular fa-calendar" style="color:var(--rhx-accent);margin-right:5px;"></i><?= date('d/m/Y', strtotime($item['heure_pointage'])) ?></span>
          <span class="hist-time"><i class="fa-regular fa-clock"></i><?= date('H:i', strtotime($item['heure_pointage'])) ?></span>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php else: ?>
    <div class="hist-card"><p class="hist-empty"><i class="fa-solid fa-inbox" style="font-size:1.5rem;display:block;margin-bottom:8px;"></i>Aucune présence récente.</p></div>
    <?php endif; ?>
  </div>

  <?php if(!empty($pub['image'])): ?>
  <div class="pub-area" id="pub-section">
    <div class="pub-label">— Événement & Sponsor —</div>
    <a href="<?= htmlspecialchars($pub['link']) ?>" target="_blank"><img src="<?= htmlspecialchars($pub['image']) ?>" alt="Sponsor"></a>
  </div>
  <?php endif; ?>
</div>

<!-- BOTTOM NAV -->
<nav class="bottom-nav">
  <a href="index.php?page=employee_scan_direct" class="nav-item active">
    <i class="fa-solid fa-fingerprint"></i>Pointer
  </a>
  <?php if($hasMessaging ?? true): ?>
  <a href="index.php?page=employee_messages" class="nav-item">
    <div class="nav-badge"><i class="fa-solid fa-comments"></i>
      <?php if(($unreadMsgs ?? 0)>0): ?><span class="badge-dot"><?= $unreadMsgs ?></span><?php endif; ?>
    </div>Messages
  </a>
  <?php endif; ?>
  <button class="nav-item" onclick="document.getElementById('passModal').classList.add('open')">
    <i class="fa-solid fa-key"></i>Sécurité
  </button>
</nav>

<!-- PASSWORD MODAL -->
<div class="modal-overlay" id="passModal">
  <div class="modal-box">
    <button class="modal-close" onclick="document.getElementById('passModal').classList.remove('open')"><i class="fa-solid fa-times"></i></button>
    <h3><i class="fa-solid fa-lock" style="color:var(--rhx-primary);margin-right:8px;"></i>Changer le mot de passe</h3>
    <form method="POST" action="index.php?page=auth_update_password">
      <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken() ?>">
      <div class="modal-field"><label>Mot de passe actuel</label><input type="password" name="old_password" required></div>
      <div class="modal-field"><label>Nouveau mot de passe</label><input type="password" name="new_password" required minlength="4"></div>
      <div class="modal-field"><label>Confirmer</label><input type="password" name="confirm_password" required minlength="4"></div>
      <button type="submit" class="modal-submit">Valider</button>
    </form>
  </div>
</div>

<!-- CUSTOM ALERT -->
<div class="alert-overlay" id="customAlert">
  <div class="alert-box-modal">
    <div id="alertIcon"></div>
    <h3 id="alertTitle"></h3>
    <p id="alertMessage"></p>
    <button id="alertBtn" onclick="handleAlertClick()"></button>
  </div>
</div>

<script>
const CSRF_TOKEN='<?= Security::generateCsrfToken() ?>';
let alertCb=null,isReq=false,progInterval=null,watchId=null,bestPosition=null;

function showAlert(type,title,msg,btn,cb=null){
  const colors={success:{i:'#10b981',bg:'linear-gradient(135deg,#10b981,#059669)'},error:{i:'#ef4444',bg:'linear-gradient(135deg,#ef4444,#dc2626)'},warning:{i:'#f59e0b',bg:'linear-gradient(135deg,#f59e0b,#d97706)'}};
  const icons={success:'fa-circle-check',error:'fa-circle-xmark',warning:'fa-triangle-exclamation'};
  const c=colors[type]||colors.warning;
  document.getElementById('alertIcon').innerHTML=`<i class="fa-solid ${icons[type]||icons.warning}" style="color:${c.i};"></i>`;
  document.getElementById('alertBtn').style.background=c.bg;
  document.getElementById('alertTitle').innerText=title;
  document.getElementById('alertMessage').innerText=msg;
  document.getElementById('alertBtn').innerText=btn;
  alertCb=cb; document.getElementById('customAlert').classList.add('open');
}
function handleAlertClick(){document.getElementById('customAlert').classList.remove('open');if(alertCb)alertCb();}

function startProgress(){let p=0;const bar=document.getElementById('gps-progress');progInterval=setInterval(()=>{p=Math.min(p+5,95);bar.style.width=p+'%';},500);}
function endProgress(){clearInterval(progInterval);document.getElementById('gps-progress').style.width='100%';}

function startDirectScan(type){
  if(isReq)return; isReq=true;
  bestPosition=null;
  document.getElementById('step1-choice').style.display='none';
  document.getElementById('step3-history').style.display='none';
  const pub=document.getElementById('pub-section'); if(pub)pub.style.display='none';
  document.getElementById('step2-loading').style.display='block';
  document.getElementById('loading-title').innerText='Recherche satellite...';
  document.getElementById('loading-msg').innerText='Optimisation de la précision GPS en cours (Max 10s)';
  document.getElementById('loading-icon').innerHTML='<i class="fa-solid fa-satellite fa-beat-fade" style="color:var(--rhx-primary);"></i>';
  startProgress();

  if(!navigator.geolocation){resetUI();showAlert('error','Incompatible','GPS non disponible.','Fermer');return;}

  const ts=new Date().toISOString();
  let timeoutId = setTimeout(() => {
      if(watchId) navigator.geolocation.clearWatch(watchId);
      if(bestPosition) {
          submitScan(bestPosition.coords.latitude, bestPosition.coords.longitude, bestPosition.coords.accuracy, type, ts);
      } else {
          endProgress(); resetUI(); isReq=false;
          showAlert('warning','Signal faible',"Impossible de capter le signal GPS (même réseau). Essayez d'activer le Wi-Fi ou approchez-vous d'une fenêtre.",'Réessayer',()=>startDirectScan(type));
      }
  }, 15000); // 15 secondes pour laisser le temps aux vieux téléphones

  watchId = navigator.geolocation.watchPosition(p => {
      // Garder la meilleure position
      if (!bestPosition || p.coords.accuracy < bestPosition.coords.accuracy) {
          bestPosition = p;
      }
      // Si la précision est excellente (< 40m), on y va direct
      if(p.coords.accuracy <= 40) {
          clearTimeout(timeoutId);
          navigator.geolocation.clearWatch(watchId);
          submitScan(p.coords.latitude, p.coords.longitude, p.coords.accuracy, type, ts);
      } else {
          document.getElementById('loading-msg').innerText = `Précision actuelle : ${Math.round(p.coords.accuracy)}m. Amélioration...`;
      }
  }, e => {
      clearTimeout(timeoutId);
      if(watchId) navigator.geolocation.clearWatch(watchId);
      endProgress(); resetUI(); isReq=false;
      showAlert('warning','Action requise',e.code===1?"Autorisez l'accès à votre position GPS.":'Activez le GPS de votre téléphone.',"J'ai compris");
  }, {enableHighAccuracy:true, maximumAge:15000, timeout:15000});
}

function submitScan(lat,lng,accuracy,type,ts){
  document.getElementById('loading-title').innerText='Position validée !';
  document.getElementById('loading-msg').innerText=`Précision finale : ${Math.round(accuracy)}m. Envoi en cours...`;
  document.getElementById('loading-icon').innerHTML='<i class="fa-solid fa-paper-plane fa-bounce" style="color:var(--rhx-accent);"></i>';
  endProgress();
  fetch('index.php?page=api_scan_direct',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({gps_lat:lat,gps_lng:lng,accuracy:accuracy,type,offline_timestamp:ts,csrf_token:CSRF_TOKEN})})
  .then(r=>r.json()).then(d=>{resetUI();if(d.success)showAlert('success','Succès !',d.message,'Terminer',()=>window.location.reload());else{isReq=false;showAlert('error','Échec',d.message,'Réessayer');}})
  .catch(()=>PointageDB.save({gps_lat:lat,gps_lng:lng,accuracy:accuracy,type,offline_timestamp:ts,csrf_token:CSRF_TOKEN}).then(()=>{resetUI();isReq=false;showAlert('warning','Hors-ligne',"Pointage sauvegardé localement.",'Compris');}));
}

function resetUI(){
  document.getElementById('step2-loading').style.display='none';
  document.getElementById('step1-choice').style.display='block';
  document.getElementById('step3-history').style.display='block';
  const pub=document.getElementById('pub-section'); if(pub)pub.style.display='block';
  document.getElementById('gps-progress').style.width='0%'; clearInterval(progInterval);
}
</script>
