// MoneySmart PNG Service Worker
// Caching strategy: NetworkFirst for pages/API, CacheFirst for static assets

const CACHE_NAME = 'moneysmartpng-v1';
const OFFLINE_URL = '/offline.html';

const PRECACHE_URLS = [
    OFFLINE_URL,
];

// Install: pre-cache the offline page
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(PRECACHE_URLS))
    );
    self.skipWaiting();
});

// Activate: clean up old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) =>
            Promise.all(
                cacheNames
                    .filter((name) => name !== CACHE_NAME)
                    .map((name) => caches.delete(name))
            )
        )
    );
    self.clients.claim();
});

// Fetch: apply caching strategies
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Only handle same-origin requests
    if (url.origin !== self.location.origin) {
        return;
    }

    // Skip non-GET requests
    if (request.method !== 'GET') {
        return;
    }

    // CacheFirst for hashed static assets (CSS, JS, fonts, images in /build/)
    if (url.pathname.startsWith('/build/')) {
        event.respondWith(cacheFirst(request));
        return;
    }

    // CacheFirst for PWA icons and static images
    if (
        url.pathname.match(/\.(png|jpg|jpeg|svg|ico|webp|woff2?|ttf|eot)$/)
    ) {
        event.respondWith(cacheFirst(request));
        return;
    }

    // NetworkFirst for HTML pages (Inertia navigations)
    if (request.headers.get('Accept')?.includes('text/html')) {
        event.respondWith(networkFirst(request));
        return;
    }

    // NetworkFirst for API/Inertia JSON requests
    if (
        request.headers.get('X-Inertia') ||
        url.pathname.startsWith('/api/')
    ) {
        event.respondWith(networkFirst(request));
        return;
    }

    // Default: network with cache fallback
    event.respondWith(networkFirst(request));
});

async function cacheFirst(request) {
    const cached = await caches.match(request);
    if (cached) {
        return cached;
    }

    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, response.clone());
        }
        return response;
    } catch {
        return new Response('', { status: 408, statusText: 'Offline' });
    }
}

async function networkFirst(request) {
    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, response.clone());
        }
        return response;
    } catch {
        const cached = await caches.match(request);
        if (cached) {
            return cached;
        }

        // If it's a page request, show offline page
        if (request.headers.get('Accept')?.includes('text/html')) {
            return caches.match(OFFLINE_URL);
        }

        return new Response('', { status: 408, statusText: 'Offline' });
    }
}
