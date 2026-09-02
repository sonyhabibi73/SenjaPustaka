<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Halaman Tidak Ditemukan · SenjaPustaka</title>
    <meta name="theme-color" content="#101b26">
    <link rel="icon" href="/icons/icon.svg" type="image/svg+xml">
    <script>
        (() => {
            const stored = localStorage.getItem('senja-theme');
            const system = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            document.documentElement.dataset.theme = stored ?? system;
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/main.js'])
</head>
<body>
    <main class="error-page">
        <div class="error-page__card">
            <div class="error-page__code">404</div>
            <div class="error-page__emoji"><i data-lucide="telescope" aria-hidden="true"></i></div>
            <h1 class="error-page__title">Halaman Tidak Ditemukan</h1>
            <p class="error-page__desc">
                Sepertinya halaman atau buku yang kamu cari tersesat di lorong senja.
                Yuk kembali dan coba cari yang lain.
            </p>
            <div class="error-page__actions">
                <a href="{{ url('/') }}" class="btn btn--primary">← Kembali ke Beranda</a>
                <a href="{{ url('/koleksi') }}" class="btn btn--ghost"><i data-lucide="library-big" aria-hidden="true"></i> Cari Buku</a>
            </div>
        </div>
    </main>
</body>
</html>
