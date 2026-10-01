<header class="navbar" role="banner">
    <div class="navbar__inner">
        <a href="{{ route('home') }}" class="logo" aria-label="SenjaPustaka - Beranda">Senja<em>Pustaka</em></a>

        <nav class="nav-links" aria-label="Navigasi utama" role="navigation">
            <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'is-active' : '' }}" {{ request()->routeIs('home') ? 'aria-current="page"' : '' }}>Beranda</a>
            <a href="{{ route('library') }}" class="nav-link {{ request()->routeIs('library') ? 'is-active' : '' }}" {{ request()->routeIs('library') ? 'aria-current="page"' : '' }}>Koleksi</a>
            <a href="{{ route('ranking') }}" class="nav-link {{ request()->routeIs('ranking') ? 'is-active' : '' }}" {{ request()->routeIs('ranking') ? 'aria-current="page"' : '' }}>Peringkat</a>
            <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*') ? 'is-active' : '' }}" {{ request()->routeIs('categories.*') ? 'aria-current="page"' : '' }}>Kategori</a>
            <a href="{{ route('authors.index') }}" class="nav-link {{ request()->routeIs('authors.*') ? 'is-active' : '' }}" {{ request()->routeIs('authors.*') ? 'aria-current="page"' : '' }}>Penulis</a>
            <a href="{{ route('about') }}" class="nav-link {{ request()->routeIs('about') ? 'is-active' : '' }}" {{ request()->routeIs('about') ? 'aria-current="page"' : '' }}>Tentang</a>
        </nav>

        <div class="navbar__actions">
            @auth
                @if (auth()->user()->is_admin)
                    <a href="{{ route('admin.dashboard') }}" class="btn btn--ghost btn--sm navbar__admin" aria-label="Panel admin"><i data-lucide="wrench" aria-hidden="true"></i> Admin</a>
                @endif
                <a href="{{ route('notifications.index') }}" class="icon-btn" aria-label="Notifikasi {{ auth()->user()->unreadNotifications()->count() > 0 ? '(' . auth()->user()->unreadNotifications()->count() . ' belum dibaca)' : '' }}">
                    <i data-lucide="bell" aria-hidden="true"></i>
                    @if (auth()->user()->unreadNotifications()->exists())
                        <span class="icon-btn__dot" data-notif-dot aria-hidden="true"></span>
                    @else
                        <span class="icon-btn__dot" data-notif-dot style="display:none" aria-hidden="true"></span>
                    @endif
                </a>
            @endauth

            <button type="button" class="icon-btn theme-toggle" aria-label="Ganti tema" aria-pressed="false">
                <span class="theme-icon" id="theme-icon" data-lucide="moon" aria-hidden="true"></span>
            </button>

            @auth
                <a href="{{ route('dashboard') }}" class="icon-btn" aria-label="Dashboard {{ auth()->user()->name }}">
                    @include('partials.avatar', ['user' => auth()->user(), 'class' => 'avatar--sm'])
                </a>
                <form method="POST" action="{{ route('logout') }}" class="navbar__logout">
                    @csrf
                    <button type="submit" class="btn btn--ghost btn--sm" aria-label="Keluar dari akun">Keluar</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn btn--primary btn--sm">Masuk</a>
            @endauth

            <button type="button" class="icon-btn burger" aria-label="Buka menu navigasi" aria-expanded="false" aria-controls="mobile-sidebar">
                <span aria-hidden="true">☰</span>
            </button>
        </div>
    </div>
</header>
