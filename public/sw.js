/* SENJAPUSTAKA 2.0 — Service Worker */
const CACHE_VERSION = 'senja-v6';
const CORE_CACHE = `${CACHE_VERSION}-core`;
const RUNTIME_CACHE = `${CACHE_VERSION}-runtime`;

const CORE_URLS = ['/', '/koleksi', '/cari', '/manifest.webmanifest'];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches
            .open(CORE_CACHE)
            // addAll bisa gagal kalau salah satu URL error → jangan sampai
            // memblokir aktivasi SW baru (kalau blokir, SW lama + cache lama
            // terus dipakai browser). allSettled membuat aktivasi selalu jalan.
            .then((cache) => Promise.allSettled(CORE_URLS.map((url) => cache.add(url))))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches
            .keys()
            .then((keys) =>
                Promise.all(
                    keys
                        .filter((key) => !key.startsWith(CACHE_VERSION))
                        .map((key) => caches.delete(key))
                )
            )
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;

    if (request.method !== 'GET' || !request.url.startsWith(self.location.origin)) {
        return;
    }

    // Navigasi: coba network dulu, fallback cache, lalu offline page.
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    const copy = response.clone();
                    caches.open(CORE_CACHE).then((cache) => cache.put(request, copy));
                    return response;
                })
                .catch(() => caches.match(request).then((cached) => cached || caches.match('/')))
        );

        return;
    }

    // Aset statis: cache-first dengan runtime cache.
    event.respondWith(
        caches.match(request).then(
            (cached) =>
                cached ||
                fetch(request).then((response) => {
                    if (response.ok && request.url.includes('/build/')) {
                        const copy = response.clone();
                        caches.open(RUNTIME_CACHE).then((cache) => cache.put(request, copy));
                    }
                    return response;
                })
        )
    );
});
