<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 - Halaman Tidak Ditemukan</title>
    <link rel="icon" href="/icons/icon.svg" type="image/svg+xml">
    <script>
        (() => {
            const stored = localStorage.getItem('senja-theme');
            const system = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            document.documentElement.dataset.theme = stored ?? system;
        })();
    </script>
    @vite(['resources/css/app.css'])
</head>
<body class="error-page">
    <div class="error-container">
        <div class="error-code">404</div>
        <h1>Halaman Tidak Ditemukan</h1>
        <p>Maaf, halaman yang Anda cari tidak ada atau telah dipindahkan.</p>
        <div class="error-actions">
            <a href="{{ route('home') }}" class="btn btn--primary">Kembali ke Beranda</a>
            <a href="{{ route('library') }}" class="btn btn--ghost">Jelajahi Koleksi</a>
        </div>
    </div>
</body>
</html>
