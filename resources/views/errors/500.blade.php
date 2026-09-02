<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Terjadi Kesalahan · SenjaPustaka</title>
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
            <div class="error-page__code">500</div>
            <div class="error-page__emoji"><i data-lucide="plug-zap" aria-hidden="true"></i></div>
            <h1 class="error-page__title">Ups, Terjadi Kesalahan</h1>
            <p class="error-page__desc">
                Lampu perpustakaan sempat padam sejenak. Coba muat ulang
                halaman ini dalam beberapa saat lagi ya.
            </p>
            <div class="error-page__actions">
                <a href="{{ url('/') }}" class="btn btn--primary">← Kembali ke Beranda</a>
            </div>
        </div>
    </main>
</body>
</html>
