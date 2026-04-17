const CACHE_NAME = 'agriassist-v2';
const STATIC_ASSETS = [
    '/manifest.json',
    'https://unpkg.com/lucide@latest',
    'https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js'
];

// Install Event
self.addEventListener('install', event => {
    self.skipWaiting();
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
    self.clients.claim();
});

// Fetch Event
self.addEventListener('fetch', event => {
    const url = new URL(event.request.url);

    // FIX: Only handle http/https schemes to prevent "chrome-extension" errors
    if (!['http:', 'https:'].includes(url.protocol)) return;

    // Only cache GET requests
    if (event.request.method !== 'GET') return;

    // CRITICAL: Never cache HTML pages or Auth routes to ensure Login state is always real
    // We check for absence of extension or specifically .php / routes
    const isHtmlRequest = event.request.mode === 'navigate' || 
                         url.pathname === '/' || 
                         url.pathname.startsWith('/login') || 
                         url.pathname.startsWith('/register');

    if (isHtmlRequest) {
        // Network Only for HTML/Navigation to preserve auth state
        return; 
    }

    // Strategy: Cache First for static assets
    const isStatic = url.pathname.includes('/build/') || 
                     url.pathname.includes('/images/') || 
                     STATIC_ASSETS.includes(url.pathname);

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
    }
});
