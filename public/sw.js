const CACHE_NAME = 'hrm-cache-v1';

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
    if (!event.request.url.startsWith('http')) return;

    event.respondWith(
        fetch(event.request)
            .then(response => {
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

// ─── Push Notification ────────────────────────────────────────────────────────
self.addEventListener('push', event => {
    if (!event.data) return;

    let data = {};
    try { data = event.data.json(); } catch { data = { title: 'HR Manajemen', body: event.data.text() }; }

    const title   = data.title  || 'HR Manajemen';
    const options = {
        body:    data.body  || '',
        icon:    data.icon  || '/images/iconhrm.png',
        badge:   data.badge || '/images/iconhrm.png',
        data:  { link: data.link || '/' },
        vibrate: [200, 100, 200],
        requireInteraction: false,
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

// Klik notifikasi → buka link
self.addEventListener('notificationclick', event => {
    event.notification.close();
    const link = event.notification.data?.link || '/';
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(windowClients => {
            for (const client of windowClients) {
                if (client.url === link && 'focus' in client) return client.focus();
            }
            if (clients.openWindow) return clients.openWindow(link);
        })
    );
});
