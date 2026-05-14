<!-- views/admin/lieu_config.php -->
<style>
    .config-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 15px;
        margin-top: 20px;
    }

    .day-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 20px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .day-card.repos {
        background: #f8fafc;
        border-color: #cbd5e1;
        opacity: 0.8;
    }

    .day-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #f1f5f9;
    }

    .day-name {
        font-weight: 800;
        font-size: 18px;
        color: var(--color-secondary);
    }

    .time-inputs {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }

    .repos-toggle {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #f1f5f9;
        padding: 6px 12px;
        border-radius: 20px;
        cursor: pointer;
        transition: all 0.2s;
        user-select: none;
    }

    .repos-toggle input {
        display: none;
    }

    .repos-toggle .toggle-label {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        color: #64748b;
    }

    .repos-toggle.active {
        background: #fee2e2;
    }

    .repos-toggle.active .toggle-label {
        color: #ef4444;
    }

    .repos-indicator {
        position: absolute;
        top: 0;
        right: 0;
        background: #ef4444;
        color: white;
        padding: 4px 12px;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        border-bottom-left-radius: 12px;
        display: none;
    }

    .day-card.repos .repos-indicator {
        display: block;
    }

    /* Desktop View */
    @media (min-width: 768px) {
        .config-grid {
            gap: 10px;
        }
        .day-card {
            display: grid;
            grid-template-columns: 150px 1fr 1fr 120px;
            align-items: center;
            padding: 12px 25px;
            border-radius: 12px;
        }
        .day-header {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }
        .time-inputs {
            gap: 20px;
        }
        .day-card.repos {
            opacity: 1;
            background: #fef2f2;
        }
    }

    .sticky-save {
        position: sticky;
        bottom: 20px;
        z-index: 100;
        margin-top: 30px;
        filter: drop-shadow(0 10px 15px rgba(0,0,0,0.1));
    }
</style>

<div class="container">
    <div style="margin-bottom: 20px;">
        <a href="index.php?page=admin_lieux" style="text-decoration:none; color:#64748b; font-weight: 600; font-size: 14px;">
            <i class="fa-solid fa-arrow-left"></i> Retour aux sites
        </a>
    </div>
    
    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 25px;">
        <div style="background: var(--color-primary); color: white; width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
            <i class="fa-solid fa-calendar-week"></i>
        </div>
        <div>
            <h1 class="text-h1" style="margin:0; font-size: 24px;"><?= htmlspecialchars($lieu['nom_lieu']) ?></h1>
            <p style="color: #64748b; font-size: 14px; margin: 0;">Configuration des horaires hebdomadaires</p>
        </div>
    </div>

    <?php if(isset($_GET['success'])): ?>
        <div style="padding:15px; background:#dcfce7; color:#166534; border-radius:12px; margin-bottom:25px; font-weight: 600; display: flex; align-items: center; gap: 10px; border-left: 5px solid #22c55e;">
            <i class="fa-solid fa-circle-check" style="font-size: 20px;"></i> 
            Planning mis à jour avec succès !
        </div>
    <?php endif; ?>

    <form action="index.php?page=admin_save_planning" method="POST" id="planningForm">
        <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken() ?>">
        <input type="hidden" name="id" value="<?= $lieu['id'] ?>">

        <div class="config-grid">
            <?php 
            $jours = [1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi', 4 => 'Jeudi', 5 => 'Vendredi', 6 => 'Samedi', 7 => 'Dimanche'];
            foreach($jours as $num => $nom): 
                $p = null;
                foreach($planning as $item) {
                    if ($item['jour_semaine'] == $num) {
                        $p = $item;
                        break;
                    }
                }
                if (!$p) {
                    $p = ['heure_debut' => '08:00', 'heure_fin' => '18:00', 'is_repos' => ($num > 5 ? 1 : 0)];
                }
                $isRepos = (bool)$p['is_repos'];
            ?>
            <div class="day-card <?= $isRepos ? 'repos' : '' ?>" id="card-<?= $num ?>">
                <div class="repos-indicator">En repos</div>
                
                <div class="day-header">
                    <div class="day-name"><?= $nom ?></div>
                    <label class="repos-toggle <?= $isRepos ? 'active' : '' ?>" onclick="toggleRepos(<?= $num ?>)">
                        <input type="checkbox" name="days[<?= $num ?>][repos]" id="check-<?= $num ?>" <?= $isRepos ? 'checked' : '' ?> onchange="updateCardStyle(<?= $num ?>)">
                        <i class="fa-solid <?= $isRepos ? 'fa-couch' : 'fa-briefcase' ?>" id="icon-<?= $num ?>"></i>
                        <span class="toggle-label" id="label-<?= $num ?>"><?= $isRepos ? 'Repos' : 'Travail' ?></span>
                    </label>
                </div>
                
                <div class="time-inputs" id="times-<?= $num ?>" style="<?= $isRepos ? 'display:none;' : '' ?>">
                    <div class="form-group" style="margin:0;">
                        <label style="font-size:11px; font-weight: 800; text-transform:uppercase; color:#94a3b8; display: block; margin-bottom: 5px;">Début</label>
                        <div style="position: relative;">
                            <i class="fa-solid fa-clock" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none;"></i>
                            <input type="time" name="days[<?= $num ?>][debut]" value="<?= substr($p['heure_debut'], 0, 5) ?>" class="form-control" style="padding-left: 35px; height: 45px; border-radius: 10px; font-weight: 600;">
                        </div>
                    </div>

                    <div class="form-group" style="margin:0;">
                        <label style="font-size:11px; font-weight: 800; text-transform:uppercase; color:#94a3b8; display: block; margin-bottom: 5px;">Fin</label>
                        <div style="position: relative;">
                            <i class="fa-solid fa-clock-check" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none;"></i>
                            <input type="time" name="days[<?= $num ?>][fin]" value="<?= substr($p['heure_fin'], 0, 5) ?>" class="form-control" style="padding-left: 35px; height: 45px; border-radius: 10px; font-weight: 600;">
                        </div>
                    </div>
                </div>

                <?php if ($isRepos): ?>
                    <div id="repos-msg-<?= $num ?>" style="color: #94a3b8; font-size: 13px; font-style: italic;">
                        Aucun service prévu ce jour.
                    </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="sticky-save">
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 18px; font-weight: 800; font-size: 16px; border-radius: 16px; box-shadow: 0 10px 25px rgba(79, 70, 229, 0.4);">
                <i class="fa-solid fa-cloud-arrow-up" style="margin-right: 10px;"></i> ENREGISTRER LES MODIFICATIONS
            </button>
        </div>
    </form>
</div>

<script>
function toggleRepos(dayNum) {
    // Le clic sur le label déclenche déjà le checkbox, cette fonction est là pour le style
}

function updateCardStyle(dayNum) {
    const checkbox = document.getElementById('check-' + dayNum);
    const card = document.getElementById('card-' + dayNum);
    const toggle = checkbox.parentElement;
    const label = document.getElementById('label-' + dayNum);
    const icon = document.getElementById('icon-' + dayNum);
    const timeInputs = document.getElementById('times-' + dayNum);
    
    if (checkbox.checked) {
        card.classList.add('repos');
        toggle.classList.add('active');
        label.innerText = 'Repos';
        icon.classList.remove('fa-briefcase');
        icon.classList.add('fa-couch');
        timeInputs.style.display = 'none';
        
        if (!document.getElementById('repos-msg-' + dayNum)) {
            const msg = document.createElement('div');
            msg.id = 'repos-msg-' + dayNum;
            msg.style.cssText = "color: #94a3b8; font-size: 13px; font-style: italic; margin-top: 10px;";
            msg.innerText = "Aucun service prévu ce jour.";
            card.appendChild(msg);
        }
    } else {
        card.classList.remove('repos');
        toggle.classList.remove('active');
        label.innerText = 'Travail';
        icon.classList.remove('fa-couch');
        icon.classList.add('fa-briefcase');
        timeInputs.style.display = 'grid';
        
        const msg = document.getElementById('repos-msg-' + dayNum);
        if (msg) msg.remove();
    }
}
</script>
