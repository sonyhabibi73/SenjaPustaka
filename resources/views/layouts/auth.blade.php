<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#101b26">
    <link rel="icon" href="/icons/icon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,500;0,600;0,700;1,500;1,600;1,700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <title>@yield('title') · SenjaPustaka</title>
    <script>
        (() => {
            const stored = localStorage.getItem('senja-theme');
            const system = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            document.documentElement.dataset.theme = stored ?? system;
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/main.js'])
</head>
<body data-page="@yield('page', 'login')">

    <div class="auth-page">
        <aside class="auth-panel">
            <div class="hero-stars" aria-hidden="true"></div>
            <div class="auth-panel__top">
                <a href="{{ route('home') }}" class="logo">Senja<em>Pustaka</em></a>
                <a href="{{ route('home') }}" style="color:rgba(255,255,255,0.85);font-size:0.85rem;">← Beranda</a>
            </div>
            <blockquote class="auth-panel__quote">
                "Buku adalah <em>jendela senja</em> yang selalu terbuka untuk siapa saja yang mau melihat."
            </blockquote>
            <div class="auth-panel__stats">
                <span><i data-lucide="library-big" aria-hidden="true"></i> {{ App\Models\Book::where('is_published', true)->count() }} buku digital</span>
                <span><i data-lucide="award" aria-hidden="true"></i> {{ App\Models\Badge::count() }} badge</span>
                <span><i data-lucide="flame" aria-hidden="true"></i> streak harian</span>
            </div>
        </aside>

        <div class="auth-form-wrap">
            <div class="auth-form">
                @if (session('success'))
                    <div class="alert alert--success">✨ {{ session('success') }}</div>
                @endif
                @if (isset($errors) && $errors->any())
                    <div class="alert alert--error"><i data-lucide="triangle-alert" aria-hidden="true"></i> {{ $errors->first() }}</div>
                @endif
                @yield('form')
            </div>
        </div>
    </div>
</body>
</html>
