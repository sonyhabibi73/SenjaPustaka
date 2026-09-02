<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Akses Ditolak · SenjaPustaka</title>
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
            <div class="error-page__code">403</div>
            <div class="error-page__emoji"><i data-lucide="lock" aria-hidden="true"></i></div>
            <h1 class="error-page__title">Akses Ditolak</h1>
            <p class="error-page__desc">
                @auth
                    Kamu login sebagai <strong>{{ auth()->user()->email }}</strong>.
                    Halaman ini khusus akun <strong>admin</strong> SenjaPustaka —
                    keluar dulu, lalu login pakai akun admin.
                @else
                    Halaman ini khusus admin SenjaPustaka.
                    Login dulu pakai akun admin ya.
                @endauth
            </p>
            <div class="error-page__actions">
                @auth
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn--primary">Keluar &amp; ganti akun</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn--primary">Masuk</a>
                @endauth
                <a href="{{ url('/') }}" class="btn btn--ghost">← Kembali ke Beranda</a>
            </div>
        </div>
    </main>
</body>
</html>
