@extends('layouts.app')

@section('title', 'SenjaPustaka — Perpustakaan Digital')
@section('page', 'home')

@section('content')

{{-- ══ HERO — "Jendela Senja" ══ --}}
<section class="hero">
    <div class="hero-stars" aria-hidden="true"></div>
    <div class="container">
        <div class="hero__grid">
            <div class="hero__content">
                <p class="hero__eyebrow">Perpustakaan Digital</p>
                <h1 class="hero__title">
                    Mulai kapan pun, berhenti kapan pun —<br>
                    kembali <em>persis</em> di halaman terakhirmu.
                </h1>
                <p class="hero__sub">
                    SenjaPustaka adalah perpustakaan digital yang terasa hidup — baca ebook PDF &amp; CBZ,
                    simpan progres otomatis, dan kumpulkan poin untuk naik level pembaca.
                </p>
                <div class="hero__ctas">
                    <a href="{{ route('library') }}" class="btn btn--primary btn--lg"><i data-lucide="library-big" aria-hidden="true"></i> Jelajahi Koleksi</a>
                    <a href="{{ auth()->check() ? route('dashboard') : route('register') }}" class="btn btn--ghost btn--lg">
                        @if (auth()->check())
                            <i data-lucide="layout-dashboard" aria-hidden="true"></i> Dashboard
                        @else
                            Daftar Gratis
                        @endif
                    </a>
                </div>
                <div class="hero__stats">
                    <div class="hero__stat">
                        <span class="mono count-up" data-count="{{ $stats['buku'] }}">{{ $stats['buku'] }}</span>
                        <span>Buku Digital</span>
                    </div>
                    <div class="hero__stat">
                        <span class="mono count-up" data-count="{{ $stats['pembaca'] }}">{{ $stats['pembaca'] }}</span>
                        <span>Pembaca Terdaftar</span>
                    </div>
                    <div class="hero__stat">
                        <span class="mono count-up" data-count="{{ $stats['halaman'] }}">{{ $stats['halaman'] }}</span>
                        <span>Halaman Cerita</span>
                    </div>
                </div>
            </div>

            <div class="bookshelf js-tilt" aria-hidden="true">
                <div class="bookshelf__row">
                    @foreach ($bookshelf->take(8) as $index => $book)
                        <div
                            class="book-spine"
                            data-title="{{ $book->title }}"
                            data-spine="{{ ($index % 6) + 1 }}"
                            style="
                                --w: {{ 38 + (($index * 7) % 22) }}px;
                                --h: {{ 200 + (($index * 23) % 130) }}px;
                                --bd: {{ $index * 0.07 }}s;
                            "
                        ></div>
                    @endforeach
                </div>
                <div class="bookshelf__base"></div>
            </div>
        </div>
    </div>
    <div class="scroll-hint">
        <div class="scroll-hint__mouse"></div>
        Gulir
    </div>
</section>

{{-- ══ SEARCH BAR — glass card floating ══ --}}
<div class="container">
    <div class="search-card">
        <form method="GET" action="{{ route('library') }}" class="search-form" role="search">
            <input
                type="search"
                name="q"
                class="input js-search"
                placeholder="Cari judul buku, penulis, atau kata kunci…"
                aria-label="Cari buku"
                autocomplete="off"
            >
            <button type="submit" class="btn btn--primary"><i data-lucide="search" aria-hidden="true"></i> Cari</button>
        </form>
        <div class="search-suggest" aria-live="polite"></div>
    </div>
</div>

{{-- ══ LANJUT BACA (kalau login) ══ --}}
@if ($continue->isNotEmpty())
    <section class="section--tight">
        <div class="container">
            <div class="section-head reveal">
                <div>
                    <p class="eyebrow">Lanjutkan Cerita</p>
                    <h2>Lanjut Baca</h2>
                </div>
                <a href="{{ route('dashboard') }}" class="section-head__link">Lihat semua →</a>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:var(--sp-4)">
                @foreach ($continue as $item)
                    <a href="{{ route('reader', $item->book) }}" class="book-card book-card--row">
                        <div class="book-card__cover">
                            <x-book-cover :book="$item->book" />
                            @unless ($item->book->cover_image)
                                <div class="book-card__title-cover">
                                    <span style="font-size:0.75rem;">{{ $item->book->title }}</span>
                                </div>
                            @endunless
                        </div>
                        <div class="book-card__body">
                            <span class="book-card__title">{{ $item->book->title }}</span>
                            <span class="book-card__author">{{ $item->book->author?->name }}</span>
                            <div class="progress" data-percent="{{ $item->progress_percent }}" style="margin-top:8px;">
                                <div class="progress__bar {{ $item->progress_percent >= 100 ? 'is-done' : '' }}"></div>
                            </div>
                            <span class="book-card__author">
                                @if ($item->progress_percent >= 100)
                                    <i data-lucide="circle-check" aria-hidden="true"></i> Selesai
                                @else
                                    Halaman {{ $item->current_page }}/{{ $item->book->pages }} · {{ $item->progress_percent }}%
                                @endif
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif

{{-- ══ SEDANG TRENDING ══ --}}
<section class="section">
    <div class="container">
        <div class="section-head reveal">
            <div>
                <p class="eyebrow">Paling Banyak Dibaca</p>
                <h2><i data-lucide="flame" aria-hidden="true"></i> Sedang Trending</h2>
            </div>
            <a href="{{ route('library', ['sort' => 'populer']) }}" class="section-head__link">Lihat semua →</a>
        </div>
        <div class="book-grid">
            @foreach ($trending as $index => $book)
                <div class="reveal" style="--d: {{ $index * 50 }}ms">
                    <x-book-card :book="$book" badge="Trending" badge-icon="flame" tilt="{{ $index < 3 }}" />
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ══ KATEGORI POPULER ══ --}}
<section class="section" style="background: var(--color-paper-soft); border-block: 1px solid var(--color-border);">
    <div class="container">
        <div class="section-head reveal">
            <div>
                <p class="eyebrow">Temukan Minatmu</p>
                <h2>Kategori Populer</h2>
            </div>
            <a href="{{ route('categories.index') }}" class="section-head__link">Semua kategori →</a>
        </div>
        <div class="cat-strip">
            @foreach ($categories as $index => $category)
                <a href="{{ route('categories.show', $category) }}" class="cat-card reveal" style="--d: {{ $index * 50 }}ms">
                    <span class="icon-chip"><i data-lucide="{{ $category->iconName() }}" aria-hidden="true"></i></span>
                    <span class="cat-card__body">
                        <strong>{{ $category->name }}</strong>
                        <span>{{ $category->books_count }} buku</span>
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</section>

{{-- ══ EBOOK TERBARU ══ --}}
<section class="section">
    <div class="container">
        <div class="section-head reveal">
            <div>
                <p class="eyebrow">Baru Masuk Rak</p>
                <h2><i data-lucide="sparkles" aria-hidden="true"></i> Ebook Terbaru</h2>
            </div>
            <a href="{{ route('library', ['sort' => 'terbaru']) }}" class="section-head__link">Lihat semua →</a>
        </div>
        <div class="book-grid">
            @foreach ($latest as $index => $book)
                <div class="reveal" style="--d: {{ $index * 40 }}ms">
                    <x-book-card :book="$book" badge="Baru" badge-icon="sparkles" />
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ══ FITUR UNGGULAN ══ --}}
<section class="section" style="background: var(--color-paper-soft); border-block: 1px solid var(--color-border);">
    <div class="container">
        <div class="section-head reveal">
            <div>
                <p class="eyebrow">Kenapa SenjaPustaka</p>
                <h2>Fitur Unggulan</h2>
            </div>
        </div>
        <div class="feature-grid">
            <div class="feature-card reveal" style="--d: 0ms">
                <div class="feature-card__num">01</div>
                <h3><i data-lucide="target" aria-hidden="true"></i> Baca, Simpan, Lanjutkan</h3>
                <p>Buka ebook PDF, CBZ, atau teks langsung dari rak. Setiap halaman tersimpan otomatis — tutup kapan pun, dan kembali persis di halaman terakhirmu tanpa perlu mencarinya lagi.</p>
            </div>
            <div class="feature-card reveal" style="--d: 80ms">
                <div class="feature-card__num">02</div>
                <h3><i data-lucide="award" aria-hidden="true"></i> Poin, Badge &amp; Level</h3>
                <p>Setiap halaman memberi 2 poin dan menamatkan buku memberi bonus 50 poin. Buka {{ $stats['badge'] }} badge — dari streak harian sampai 10.000 halaman — dan naik {{ count(App\Services\Level::LEVELS) }} level menuju Dewa Baca.</p>
            </div>
            <div class="feature-card reveal" style="--d: 160ms">
                <div class="feature-card__num">03</div>
                <h3><i data-lucide="moon-star" aria-hidden="true"></i> Gelap yang Dramatis</h3>
                <p>Dua tema bawaan: "Langit Tengah Malam" yang lembut di mata untuk membaca larut malam, dan "Golden Hour" yang hangat untuk siang hari — beralih sekali klik dari navbar.</p>
            </div>
        </div>
    </div>
</section>

{{-- ══ CTA BANNER ══ --}}
<section class="section">
    <div class="container">
        <div class="cta-banner reveal">
            <h2>Mulai Petualangan Membacamu</h2>
            <p>Gratis selamanya. Buat akun dalam 30 detik dan langsung temukan buku pertamamu.</p>
            <a href="{{ auth()->check() ? route('dashboard') : route('register') }}" class="btn btn--light btn--lg">
                @if (auth()->check())
                    <i data-lucide="layout-dashboard" aria-hidden="true"></i> Buka Dashboard
                @else
                    <i data-lucide="sparkles" aria-hidden="true"></i> Buat Akun Gratis
                @endif
            </a>
        </div>
    </div>
</section>

@endsection
