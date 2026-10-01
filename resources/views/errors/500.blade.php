<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>500 - Kesalahan Server</title>
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
        <div class="error-code">500</div>
        <h1>Kesalahan Server</h1>
        <p>Maaf, terjadi kesalahan pada server. Silakan coba lagi nanti.</p>
        <div class="error-actions">
            <a href="{{ route('home') }}" class="btn btn--primary">Kembali ke Beranda</a>
        </div>
    </div>
</body>
</html>
