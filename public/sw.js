/* 
    Badar PMS Service Worker - v2
    Strategy: Network-First (Pages) + Stale-While-Revalidate (Assets)
    This version prevents Auth-redirect crashes and stale-cache issues.
*/

const CACHE_NAME = 'badar-pms-v7'; // Bumping for dynamic changes
const STATIC_ASSETS = [];

// 1. Install Event: Cache only critical static items
self.addEventListener('install', event => {
    self.skipWaiting();
});

// 2. Activate Event: Clean up all previous broken caches
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

// 3. The Smart Fetch Handler: Handles Laravel Auth and Redirects Safely
self.addEventListener('fetch', event => {
    const url = new URL(event.request.url);

    // CRITICAL: Bypass Service Worker for Authentication, API, and Side-Effecting requests
    if (
        url.pathname.includes('/login') ||
        url.pathname.includes('/logout') ||
        url.pathname.includes('/manifest.json') ||
        url.pathname.includes('/api/') ||
        event.request.method !== 'GET'
    ) {
        return; // Network-Only
    }

    // STRATEGY: Network-First for Navigation (Ensures session/auth stays accurate)
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request)
                .catch(() => caches.match(event.request))
        );
        return;
    }

    // STRATEGY: Stale-While-Revalidate for Assets (CSS, JS, Fonts, Images)
    event.respondWith(
        caches.match(event.request).then(cachedResponse => {
            const fetchPromise = fetch(event.request).then(networkResponse => {
                // Background update of the cache
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
});
