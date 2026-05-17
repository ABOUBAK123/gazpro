const CACHE  = 'gazlivreur-v1';
const ASSETS = [
    'tailwind.min.js',
    'alpine.min.js',
    'fa.min.css',
    'icons/livreur-192.png',
    'icons/livreur-512.png',
].map(a => self.location.pathname.replace('sw-livreur.js', a));

// Install: cache static assets
self.addEventListener('install', e => {
    e.waitUntil(
        caches.open(CACHE)
              .then(c => c.addAll(ASSETS))
              .then(() => self.skipWaiting())
    );
});

// Activate: clean old caches
self.addEventListener('activate', e => {
    e.waitUntil(
        caches.keys()
              .then(keys => Promise.all(
                  keys.filter(k => k !== CACHE).map(k => caches.delete(k))
              ))
              .then(() => self.clients.claim())
    );
});

// Fetch strategy
self.addEventListener('fetch', e => {
    const url = e.request.url;

    // Always network for: API calls, status updates, location, CSRF
    if (url.includes('/statut') || url.includes('/position') ||
        url.includes('/api/')   || e.request.method !== 'GET') {
        return;
    }

    e.respondWith(
        caches.match(e.request).then(cached => {
            if (cached) return cached;
            return fetch(e.request).then(res => {
                // Cache static assets only
                if (res.ok && (url.includes('.js') || url.includes('.css') || url.includes('.png'))) {
                    const clone = res.clone();
                    caches.open(CACHE).then(c => c.put(e.request, clone));
                }
                return res;
            });
        })
    );
});
