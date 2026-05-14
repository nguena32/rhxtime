<div class="header-actions" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px;">
    <div style="display:flex; align-items:center; gap:15px;">
        <div style="background:var(--color-primary); color:#fff; width:45px; height:45px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:20px; box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.4);">
            <i class="fa-solid fa-shield-virus"></i>
        </div>
        <div>
            <h2 style="font-weight: 800; color: var(--color-secondary); margin:0;">Audit de Sécurité</h2>
            <p style="margin:0; font-size:12px; color:#64748b;">Surveillance en temps réel des anomalies et tentatives de fraude.</p>
        </div>
    </div>
    
    <a href="index.php?page=superadmin_repair_signatures" class="btn btn-warning" onclick="return confirm('Cette action synchronisera toutes les licences avec le SEL actuel de cet ordinateur. À faire uniquement si vous changez de machine. Continuer ?');" style="border-radius:10px; font-weight:700;">
        <i class="fa-solid fa-sync"></i> Synchroniser les signatures (Multi-Machine)
    </a>
</div>

<div class="card" style="border-radius:20px; border:1px solid #fee2e2; background:#fff;">
    <div style="padding:20px; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center;">
        <h3 style="font-weight: 700; color: #991b1b; margin:0;"><i class="fa-solid fa-list-check"></i> Journal des Anomalies</h3>
        <span class="badge" style="background:#fee2e2; color:#b91c1c; border-radius:8px;"><?= count($logs) ?> entrées</span>
    </div>
    
    <div style="max-height: 600px; overflow-y: auto; padding:10px;">
        <?php if (!empty($logs)): ?>
            <div style="font-family: 'Courier New', Courier, monospace; font-size: 13px;">
                <?php foreach ($logs as $log): 
                    $isCritical = $log['is_critical'];
                ?>
                    <div style="padding: 12px 15px; border-bottom: 1px solid #f8fafc; <?= $isCritical ? 'background:#fff5f5; border-left:4px solid #ef4444;' : 'border-left:4px solid #cbd5e1;' ?> margin-bottom:5px; border-radius:4px;">
                        <span style="color: <?= $isCritical ? '#b91c1c' : '#64748b' ?>;">
                            <?= htmlspecialchars($log['raw']) ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="text-align:center; padding:50px; color:#94a3b8;">
                <i class="fa-solid fa-check-circle" style="font-size:48px; color:var(--color-primary); opacity:0.2; margin-bottom:15px;"></i>
                <p>Aucune anomalie détectée. Le système est sain.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* Style spécifique pour le log viewer */
::-webkit-scrollbar {
    width: 8px;
}
::-webkit-scrollbar-track {
    background: #f8fafc;
}
::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}
::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>
