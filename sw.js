// sw.js (Service Worker) - RHXtimes SaaS
const CACHE_NAME = 'rhxtimes-pwa-v3';
const ASSETS_TO_CACHE = [
    './',
    './index.php',
    './assets/css/style.css',
    './assets/images/logo_texte_blanc.png',
    './assets/images/logo_texte_bleu.png',
    './assets/images/favicon.png',
    './assets/images/pwa.png',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
    'https://cdn.jsdelivr.net/npm/sweetalert2@11',
    'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap',
    'https://unpkg.com/html5-qrcode'
];

self.addEventListener('install', (event) => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return Promise.allSettled(
                ASSETS_TO_CACHE.map(url => cache.add(url))
            );
        })
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keyList) => {
            return Promise.all(keyList.map((key) => {
                if (key !== CACHE_NAME) {
                    return caches.delete(key);
                }
            }));
        }).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') return;

    const url = new URL(event.request.url);

    // Stratégie Network-First pour les pages PHP (Toujours avoir les données fraîches si possible)
    if (url.pathname.includes('.php') || url.search.includes('page=')) {
        event.respondWith(
            fetch(event.request).catch(() => {
                return caches.match(event.request);
            })
        );
        return;
    }

    // Stratégie Stale-While-Revalidate pour les assets (CSS, Images, Fonts)
    event.respondWith(
        caches.match(event.request).then((cachedResponse) => {
            const fetchPromise = fetch(event.request).then((networkResponse) => {
                caches.open(CACHE_NAME).then((cache) => {
                    cache.put(event.request, networkResponse.clone());
                });
                return networkResponse;
            }).catch(() => cachedResponse);
            
            return cachedResponse || fetchPromise;
        })
    );
});
