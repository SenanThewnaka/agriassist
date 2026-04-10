const CACHE_NAME = 'agriassist-v1';
const STATIC_ASSETS = [
    '/',
    '/manifest.json',
    'https://unpkg.com/lucide@latest',
    'https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js'
];

// Install Event
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            return cache.addAll(STATIC_ASSETS);
        })
    );
});

// Activate Event
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys => {
            return Promise.all(keys
                .filter(key => key !== CACHE_NAME)
                .map(key => caches.delete(key))
            );
        })
    );
});

// Fetch Event
self.addEventListener('fetch', event => {
    // Only cache GET requests
    if (event.request.method !== 'GET') return;

    // Strategy: Network First, falling back to cache for pages/data
    // Strategy: Cache First for static assets
    const url = new URL(event.request.url);
    const isStatic = url.pathname.includes('/build/') || url.pathname.includes('/images/') || STATIC_ASSETS.includes(url.pathname);

    if (isStatic) {
        event.respondWith(
            caches.match(event.request).then(response => {
                return response || fetch(event.request).then(fetchRes => {
                    return caches.open(CACHE_NAME).then(cache => {
                        cache.put(event.request, fetchRes.clone());
                        return fetchRes;
                    });
                });
            })
        );
    } else {
        event.respondWith(
            fetch(event.request).then(fetchRes => {
                const copy = fetchRes.clone();
                caches.open(CACHE_NAME).then(cache => {
                    cache.put(event.request, copy);
                });
                return fetchRes;
            }).catch(() => caches.match(event.request))
        );
    }
});
