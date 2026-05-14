/**
 * Dashboard Lazy Load - V1.6
 * Gère le chargement asynchrone des pointages pour une meilleure UX
 */
document.addEventListener('DOMContentLoaded', function() {
    const feedBody = document.getElementById('pointage-feed-body');
    const loadMoreBtn = document.getElementById('load-more-btn');
    const loadMoreContainer = document.getElementById('load-more-btn-container');
    const loadingArea = document.getElementById('loading-area');
    const emptyState = document.getElementById('empty-state');

    let currentOffset = 0;
    const limit = 20;
    let isLoading = false;

    // Récupération des filtres injectés par PHP
    const filters = window.dashboardFilters || {};

    /**
     * Charge une page de données depuis l'API
     */
    async function loadPointages() {
        if (isLoading) return;
        
        isLoading = true;
        loadingArea.style.display = 'block';
        loadMoreContainer.style.display = 'none';
        emptyState.style.display = 'none';

        try {
            const queryParams = new URLSearchParams({
                page: 'api_pointages',
                offset: currentOffset,
                limit: limit,
                user_id: filters.user_id || '',
                date_start: filters.date_start || '',
                date_end: filters.date_end || ''
            });

            const response = await fetch(`index.php?${queryParams.toString()}`);
            const result = await response.json();

            if (result.success) {
                const isFirstLoad = currentOffset === 0;
                appendRows(result.data);
                
                currentOffset += result.data.length;
                
                // Gestion de l'affichage du bouton "Charger plus" ou état vide
                if (result.data.length === 0 && isFirstLoad) {
                    emptyState.style.display = 'block';
                } else if (result.has_more) {
                    loadMoreContainer.style.display = 'block';
                }
            } else {
                console.error('Erreur API:', result.message);
                alert('Erreur lors du chargement des données.');
            }
        } catch (error) {
            console.error('Erreur réseau:', error);
        } finally {
            isLoading = false;
            loadingArea.style.display = 'none';
        }
    }

    /**
     * Génère et insère le HTML des lignes du tableau
     */
    function appendRows(data) {
        if (!data || data.length === 0) return;

        data.forEach(item => {
            const tr = document.createElement('tr');
            tr.style.borderBottom = '1px solid #f3f4f6';
            
            const badgeBg = item.is_arrivee ? '#dcfce7' : '#fee2e2';
            const badgeColor = item.is_arrivee ? '#166534' : '#991b1b';
            const avatarBg = item.is_arrivee ? '#D1FAE5' : '#FEE2E2';
            const avatarColor = item.is_arrivee ? '#065F46' : '#991B1B';

            tr.innerHTML = `
                <td style="padding:12px;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div class="avatar" style="width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:bold; background:${avatarBg}; color:${avatarColor}">
                            ${item.initial}
                        </div>
                        <div>
                            <div style="font-weight:500; color:#111827;">${item.nom} ${item.prenom}</div>
                        </div>
                    </div>
                </td>
                <td style="padding:12px;">
                    <span class="badge" style="padding:4px 8px; border-radius:12px; font-size:11px; font-weight:600; background:${badgeBg}; color:${badgeColor}">
                        ${item.type}
                    </span>
                </td>
                <td style="padding:12px;">
                    <div style="font-size:13px; font-weight:600; color:#374151;">${item.heure}</div>
                    <div style="font-size:11px; color:#6b7280;">${item.date}</div>
                </td>
                <td style="padding:12px;">
                    <i class="fa-solid fa-location-dot" style="color:#9ca3af; margin-right:5px; font-size:11px;"></i> 
                    <span style="color:#4b5563; font-size:13px;">${item.lieu}</span>
                </td>
                <td style="padding:12px; color:#6b7280; font-size:13px;">
                    ${item.distance} m
                </td>
            `;
            feedBody.appendChild(tr);
        });
    }

    // Event listeners
    loadMoreBtn.addEventListener('click', loadPointages);

    // Chargement initial
    loadPointages();
});
