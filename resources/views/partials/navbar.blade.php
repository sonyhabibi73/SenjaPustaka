<header class="navbar">
    <div class="navbar__inner">
        <a href="{{ route('home') }}" class="logo">Senja<em>Pustaka</em></a>

        <nav class="nav-links" aria-label="Navigasi utama">
            <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'is-active' : '' }}">Beranda</a>
            <a href="{{ route('library') }}" class="nav-link {{ request()->routeIs('library') ? 'is-active' : '' }}">Koleksi</a>
            <a href="{{ route('ranking') }}" class="nav-link {{ request()->routeIs('ranking') ? 'is-active' : '' }}">Peringkat</a>
            <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*') ? 'is-active' : '' }}">Kategori</a>
            <a href="{{ route('authors.index') }}" class="nav-link {{ request()->routeIs('authors.*') ? 'is-active' : '' }}">Penulis</a>
            <a href="{{ route('about') }}" class="nav-link {{ request()->routeIs('about') ? 'is-active' : '' }}">Tentang</a>
        </nav>

        <div class="navbar__actions">
            @auth
                @if (auth()->user()->is_admin)
                    <a href="{{ route('admin.dashboard') }}" class="btn btn--ghost btn--sm navbar__admin" aria-label="Panel admin"><i data-lucide="wrench" aria-hidden="true"></i> Admin</a>
                @endif
                <a href="{{ route('notifications.index') }}" class="icon-btn" aria-label="Notifikasi">
                    <i data-lucide="bell" aria-hidden="true"></i>
                    @if (auth()->user()->unreadNotifications()->exists())
                        <span class="icon-btn__dot" data-notif-dot></span>
                    @else
                        <span class="icon-btn__dot" data-notif-dot style="display:none"></span>
                    @endif
                </a>
            @endauth

            <button type="button" class="icon-btn theme-toggle" aria-label="Ganti tema">
                <span class="theme-icon" id="theme-icon" data-lucide="moon" aria-hidden="true"></span>
            </button>

            @auth
                <a href="{{ route('dashboard') }}" class="icon-btn" aria-label="Dashboard">
                    @include('partials.avatar', ['user' => auth()->user(), 'class' => 'avatar--sm'])
                </a>
                <form method="POST" action="{{ route('logout') }}" class="navbar__logout">
                    @csrf
                    <button type="submit" class="btn btn--ghost btn--sm" aria-label="Keluar">Keluar</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn btn--primary btn--sm">Masuk</a>
            @endauth

            <button type="button" class="icon-btn burger" aria-label="Buka menu" aria-expanded="false">
                ☰
            </button>
        </div>
    </div>
</header>
