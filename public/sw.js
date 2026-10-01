/* SENJAPUSTAKA 2.0 — Service Worker */
const CACHE_VERSION = 'senja-v12';
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

/* ── Halaman "tidak ada koneksi" ──────────────────────────────────
   Sengaja dibedakan dari landing page. Dulu fallback-nya
   `caches.match('/')`, jadi ketika server gagal SEMUA URL (nav ke
   /tentang, /koleksi?page=2, dst) disajikan beranda — URL berubah,
   isinya tetap halaman awal. Itu bug, bukan fitur offline. */
function offlinePage(requestUrl) {
    const url = new URL(requestUrl);
    const path = url.pathname + url.search;

    const html = `<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Tidak ada koneksi — SenjaPustaka</title>
<style>
  body{margin:0;min-height:100vh;display:grid;place-items:center;
       background:#f9f4ec;color:#231a13;font:16px/1.6 system-ui,sans-serif}
  main{max-width:32rem;padding:2rem;text-align:center}
  h1{font-size:1.35rem;margin:0 0 .5rem}
  p{color:#6c5d50;margin:0 0 1.25rem}
  a{color:#b4491a}
</style>
</head>
<body>
<main>
  <h1>Halaman ini belum bisa dimuat</h1>
  <p>Koneksi ke server SenjaPustaka terputus, dan halaman ini tidak tersimpan di cache offline.</p>
  <a href="${path}">Coba lagi</a>
</main>
</body>
</html>`;

    return new Response(html, {
        status: 503,
        headers: { 'Content-Type': 'text/html; charset=utf-8' },
    });
}

self.addEventListener('fetch', (event) => {
    const request = event.request;

    if (request.method !== 'GET' || !request.url.startsWith(self.location.origin)) {
        return;
    }

    // Navigasi: SELALU network-first, dan fallback-nya hanya boleh untuk
    // URL yang PERSIS sama. Tidak pernah menyajikan halaman lain di URL
    // yang berbeda — kalau tidak ada yang cocok, kirim halaman 503 jujur.
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    // Hanya 2xx yang layak disimpan; respons error jangan
                    // ditimpan, nanti bisa terus-terusan muncul offline.
                    if (response.ok) {
                        const copy = response.clone();
                        caches.open(CORE_CACHE).then((cache) => cache.put(request, copy));
                    }
                    return response;
                })
                .catch(() =>
                    caches
                        .match(request)
                        .then((cached) => cached || offlinePage(request.url))
                )
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
