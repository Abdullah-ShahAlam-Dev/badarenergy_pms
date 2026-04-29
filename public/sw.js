/* 
    Badar PMS Service Worker - v3
    Strategy: Network-First (ALL HTML/AJAX) + Stale-While-Revalidate (True Static Assets only)
    This version eliminates stale ticket/task status in modals and detail views.
*/

const CACHE_NAME = 'badar-pms-v10';
const STATIC_ASSETS = [];

// 1. Install Event
self.addEventListener('install', event => {
    self.skipWaiting();
});

// 2. Activate Event: Clean up all previous caches
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys => Promise.all(
            keys.map(key => {
                if (key !== CACHE_NAME) return caches.delete(key);
            })
        ))
    );
    self.clients.claim();
});

// 3. Fetch Handler
self.addEventListener('fetch', event => {
    const url = new URL(event.request.url);

    // Never intercept non-GET requests (POST, PUT, DELETE etc)
    if (event.request.method !== 'GET') {
        return;
    }

    // Never intercept auth, API, or branding routes
    if (
        url.pathname.includes('/login') ||
        url.pathname.includes('/logout') ||
        url.pathname.includes('/manifest.json') ||
        url.pathname.includes('/favicon') ||
        url.pathname.includes('/user-uploads/favicon') ||
        url.pathname.includes('/user-uploads/app-logo') ||
        url.pathname.includes('/user-uploads/pwa-icons') ||
        url.pathname.includes('/api/')
    ) {
        return;
    }

    // STRATEGY: Network-First for ALL HTML (navigation + AJAX partials)
    // This covers: page navigations, modal AJAX loads, ticket/task detail views
    const acceptHeader = event.request.headers.get('Accept') || '';
    if (
        event.request.mode === 'navigate' ||
        acceptHeader.includes('text/html')
    ) {
        event.respondWith(
            fetch(event.request).catch(() => caches.match(event.request))
        );
        return;
    }

    // STRATEGY: Stale-While-Revalidate ONLY for true static assets (CSS, JS, fonts, images)
    const isStaticAsset = (
        url.pathname.match(/\.(css|js|woff2?|ttf|eot|otf|png|jpg|jpeg|gif|svg|ico|webp)(\?.*)?$/)
    );

    if (isStaticAsset) {
        event.respondWith(
            caches.match(event.request).then(cachedResponse => {
                const fetchPromise = fetch(event.request).then(networkResponse => {
                    if (networkResponse && networkResponse.status === 200) {
                        const responseToCache = networkResponse.clone();
                        caches.open(CACHE_NAME).then(cache => {
                            cache.put(event.request, responseToCache);
                        });
                    }
                    return networkResponse;
                }).catch(() => null);

                return cachedResponse || fetchPromise;
            })
        );
        return;
    }

    // Everything else: Network-only (no caching)
    event.respondWith(fetch(event.request));
});
