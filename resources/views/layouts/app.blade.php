<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="SenjaPustaka — perpustakaan digital yang terasa hidup. Baca ebook PDF & CBZ, kumpulkan poin, dan naik level pembaca.">
    <meta name="theme-color" content="#101b26">
    <meta property="og:title" content="SenjaPustaka">
    <meta property="og:description" content="Perpustakaan digital yang terasa hidup — baca, kumpulkan poin, naik level.">
    <meta property="og:type" content="website">
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="icon" href="/icons/icon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/icons/icon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,500;0,600;0,700;1,500;1,600;1,700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <title>@yield('title', 'SenjaPustaka')</title>
    <script>
        (() => {
            const stored = localStorage.getItem('senja-theme');
            const system = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            document.documentElement.dataset.theme = stored ?? system;
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/main.js'])
</head>
<body data-page="@yield('page', '')">

    @include('partials.navbar')
    @include('partials.sidebar')

    <main class="main">
        <div class="container">
            @if (session('success'))
                <div class="alert alert--success">✨ {{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert--error"><i data-lucide="triangle-alert" aria-hidden="true"></i> {{ session('error') }}</div>
            @endif
            @if (isset($errors) && $errors->any())
                <div class="alert alert--error">
                    <span><i data-lucide="triangle-alert" aria-hidden="true"></i> {{ $errors->first() }}</span>
                </div>
            @endif
        </div>
        @yield('content')
    </main>

    @include('partials.footer')
</body>
</html>
