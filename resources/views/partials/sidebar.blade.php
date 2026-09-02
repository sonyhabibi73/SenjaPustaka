<div class="sidebar-overlay"></div>
<aside class="mobile-sidebar">
    <div class="mobile-sidebar__header">
        <a href="{{ route('home') }}" class="logo">Senja<em>Pustaka</em></a>
        <button type="button" class="icon-btn" aria-label="Tutup menu" onclick="document.querySelector('.mobile-sidebar')?.classList.remove('is-open');document.querySelector('.sidebar-overlay')?.classList.remove('is-open')">✕</button>
    </div>
    <nav class="mobile-sidebar__nav">
        <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'is-active' : '' }}"><i data-lucide="home" aria-hidden="true"></i> Beranda</a>
        <a href="{{ route('library') }}" class="{{ request()->routeIs('library') ? 'is-active' : '' }}"><i data-lucide="library-big" aria-hidden="true"></i> Koleksi</a>
        <a href="{{ route('ranking') }}" class="{{ request()->routeIs('ranking') ? 'is-active' : '' }}"><i data-lucide="trophy" aria-hidden="true"></i> Peringkat</a>
        <a href="{{ route('leaderboard') }}" class="{{ request()->routeIs('leaderboard') ? 'is-active' : '' }}"><i data-lucide="medal" aria-hidden="true"></i> Peringkat Pembaca</a>
        <a href="{{ route('categories.index') }}" class="{{ request()->routeIs('categories.*') ? 'is-active' : '' }}"><i data-lucide="tags" aria-hidden="true"></i> Kategori</a>
        <a href="{{ route('authors.index') }}" class="{{ request()->routeIs('authors.*') ? 'is-active' : '' }}"><i data-lucide="feather" aria-hidden="true"></i> Penulis</a>
        <a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'is-active' : '' }}"><i data-lucide="sunset" aria-hidden="true"></i> Tentang</a>
        <a href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'is-active' : '' }}"><i data-lucide="mail" aria-hidden="true"></i> Kontak</a>
    </nav>
    <div class="mobile-sidebar__footer">
        @auth
            @if (auth()->user()->is_admin)
                <a href="{{ route('admin.dashboard') }}" class="btn btn--ghost btn--block"><i data-lucide="wrench" aria-hidden="true"></i> Panel Admin</a>
            @endif
            <a href="{{ route('dashboard') }}" class="btn btn--primary btn--block"><i data-lucide="layout-dashboard" aria-hidden="true"></i> Dashboard</a>
            <a href="{{ route('profile.edit') }}" class="btn btn--ghost btn--block"><i data-lucide="user" aria-hidden="true"></i> Profil</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn--ghost btn--block">Keluar</button>
            </form>
        @else
            <a href="{{ route('login') }}" class="btn btn--primary btn--block">Masuk</a>
            <a href="{{ route('register') }}" class="btn btn--ghost btn--block">Daftar Gratis</a>
        @endauth
    </div>
</aside>
