/**
 * RHXtimes - Synchronisation Engine
 * Gère la transmission des données stockées dans IndexedDB vers le serveur.
 */
const SyncEngine = {
    isSyncing: false,

    async sync() {
        if (this.isSyncing || !navigator.onLine) return;
        
        const pending = await PointageDB.getAll();
        if (pending.length === 0) return;

        this.isSyncing = true;
        console.log(`SyncEngine: Tentative de synchronisation de ${pending.length} pointages...`);

        let successCount = 0;

        for (const item of pending) {
            try {
                // Déterminer l'endpoint selon le type de scan (QR vs Direct)
                const endpoint = item.qr_token ? 'api.php' : 'index.php?page=api_scan_direct';
                
                // On utilise le token CSRF global s'il est défini, sinon celui du snapshot
                const payload = { ...item };
                if (typeof CSRF_TOKEN !== 'undefined') payload.csrf_token = CSRF_TOKEN;

                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();

                // On considère comme traité si succès ou si doublon (déjà enregistré)
                if (result.success || (result.message && result.message.includes('déjà pointé'))) {
                    await PointageDB.delete(item.id);
                    successCount++;
                }
            } catch (error) {
                console.error("SyncEngine: Erreur lors de l'envoi d'un pointage", error);
                // On arrête la boucle en cas d'erreur réseau pour réessayer plus tard
                break;
            }
        }

        if (successCount > 0) {
            this.notifySuccess(successCount);
        }

        this.isSyncing = false;
    },

    notifySuccess(count) {
        console.log(`SyncEngine: ${count} pointage(s) synchronisé(s) !`);
        
        // Utilisation de SweetAlert2 si disponible (injecté dans index.php)
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Synchronisation Terminée',
                text: `${count} pointage(s) enregistré(s) hors-ligne ont été transmis avec succès.`,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true
            });
        }
    }
};

// Écouteurs globaux
window.addEventListener('online', () => SyncEngine.sync());
window.addEventListener('load', () => {
    // Petit délai pour laisser la page s'initialiser
    setTimeout(() => SyncEngine.sync(), 2000);
});
// Synchronisation périodique si l'onglet reste ouvert
setInterval(() => SyncEngine.sync(), 60000); // Toutes les minutes
