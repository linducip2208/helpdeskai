const VERSION = 'helpdeskai-v1';
const SHELL = ['/offline.html', '/manifest.json'];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(VERSION).then((cache) => cache.addAll(SHELL)).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter((k) => k !== VERSION).map((k) => caches.delete(k)))
        ).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const req = event.request;
    if (req.method !== 'GET') return;

    const url = new URL(req.url);
    if (url.origin !== self.location.origin) return;

    if (req.mode === 'navigate') {
        event.respondWith(
            fetch(req).catch(() => caches.match('/offline.html'))
        );
        return;
    }

    if (/\.(css|js|svg|png|jpg|jpeg|woff2?)$/.test(url.pathname)) {
        event.respondWith(
            caches.match(req).then((cached) =>
                cached || fetch(req).then((res) => {
                    const copy = res.clone();
                    caches.open(VERSION).then((cache) => cache.put(req, copy));
                    return res;
                }).catch(() => cached)
            )
        );
    }
});

self.addEventListener('push', (event) => {
    let data = { title: 'HelpDesk AI', body: 'You have a new notification', url: '/' };
    if (event.data) {
        try { data = { ...data, ...event.data.json() }; }
        catch { data.body = event.data.text(); }
    }
    event.waitUntil(
        self.registration.showNotification(data.title, {
            body: data.body,
            icon: '/icons/icon-192.svg',
            badge: '/icons/icon-192.svg',
            data: { url: data.url },
        })
    );
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const target = event.notification.data?.url || '/';
    event.waitUntil(
        self.clients.matchAll({ type: 'window' }).then((clients) => {
            for (const client of clients) {
                if (client.url.includes(target) && 'focus' in client) return client.focus();
            }
            return self.clients.openWindow(target);
        })
    );
});
