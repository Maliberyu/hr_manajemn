const CACHE_NAME = 'hrm-cache-v1';

const STATIC_ASSETS = [
    // Diisi otomatis saat build — dikosongkan agar SW tidak block
];

self.addEventListener('install', event => {
    self.skipWaiting();
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(
                keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k))
            )
        )
    );
    self.clients.claim();
});

// Network-first: coba network, fallback ke cache
self.addEventListener('fetch', event => {
    if (event.request.method !== 'GET') return;

    // Lewati request non-http (chrome-extension, dll)
    if (!event.request.url.startsWith('http')) return;

    event.respondWith(
        fetch(event.request)
            .then(response => {
                // Cache aset statis (CSS, JS, gambar, font)
                if (
                    response.ok &&
                    event.request.url.match(/\.(css|js|png|jpg|jpeg|svg|gif|woff2?|ttf)(\?.*)?$/)
                ) {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
                }
                return response;
            })
            .catch(() => caches.match(event.request))
    );
});
