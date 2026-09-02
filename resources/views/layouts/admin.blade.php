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
    <title>@yield('title', 'Admin') · SenjaPustaka</title>
    <script>
        (() => {
            const stored = localStorage.getItem('senja-theme');
            const system = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            document.documentElement.dataset.theme = stored ?? system;
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/main.js'])
</head>
<body data-page="admin">

    <div class="admin-layout">
        <aside class="admin-sidebar">
            <a href="{{ route('home') }}" class="logo">Senja<em>Pustaka</em></a>
            <nav class="admin-sidebar__nav">
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}"><i data-lucide="layout-dashboard" aria-hidden="true"></i> Dashboard</a>
                <a href="{{ route('admin.buku.index') }}" class="{{ request()->routeIs('admin.buku.*') ? 'is-active' : '' }}"><i data-lucide="library-big" aria-hidden="true"></i> Buku</a>
                <a href="{{ route('admin.kategori.index') }}" class="{{ request()->routeIs('admin.kategori.*') ? 'is-active' : '' }}"><i data-lucide="tags" aria-hidden="true"></i> Kategori</a>
                <a href="{{ route('admin.penulis.index') }}" class="{{ request()->routeIs('admin.penulis.*') ? 'is-active' : '' }}"><i data-lucide="feather" aria-hidden="true"></i> Penulis</a>
                <a href="{{ route('admin.penerbit.index') }}" class="{{ request()->routeIs('admin.penerbit.*') ? 'is-active' : '' }}"><i data-lucide="building-2" aria-hidden="true"></i> Penerbit</a>
                <a href="{{ route('admin.series.index') }}" class="{{ request()->routeIs('admin.series.*') ? 'is-active' : '' }}"><i data-lucide="book-open" aria-hidden="true"></i> Series</a>
                <a href="{{ route('admin.review.index') }}" class="{{ request()->routeIs('admin.review.*') ? 'is-active' : '' }}"><i data-lucide="star" aria-hidden="true"></i> Review</a>
                <a href="{{ route('admin.user.index') }}" class="{{ request()->routeIs('admin.user.*') ? 'is-active' : '' }}"><i data-lucide="users" aria-hidden="true"></i> Pengguna</a>
                <a href="{{ route('admin.newsletter.index') }}" class="{{ request()->routeIs('admin.newsletter.*') ? 'is-active' : '' }}"><i data-lucide="mail" aria-hidden="true"></i> Newsletter</a>
            </nav>
            <div class="admin-sidebar__foot">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                    <span class="avatar" style="width:32px;height:32px;font-size:0.8rem;">{{ auth()->user()->initials() }}</span>
                    <span>
                        <strong style="color:#f0ead9;font-size:0.85rem;display:block;">{{ auth()->user()->name }}</strong>
                        <small>{{ auth()->user()->is_admin ? 'Administrator' : 'Pengguna' }}</small>
                    </span>
                </div>
                <a href="{{ route('home') }}" style="color:#93a1ad;font-size:0.8rem;">← Kembali ke situs</a>
                <form method="POST" action="{{ route('logout') }}" style="margin-top:10px;">
                    @csrf
                    <button type="submit" class="btn btn--ghost btn--sm btn--block">Keluar</button>
                </form>
            </div>
        </aside>

        <main class="admin-main">
            @if (session('success'))
                <div class="alert alert--success">✨ {{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert--error"><i data-lucide="triangle-alert" aria-hidden="true"></i> {{ session('error') }}</div>
            @endif
            @if (isset($errors) && $errors->any())
                <div class="alert alert--error"><i data-lucide="triangle-alert" aria-hidden="true"></i> {{ $errors->first() }}</div>
            @endif
            @yield('content')
        </main>
    </div>
</body>
</html>
