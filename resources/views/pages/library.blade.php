@extends('layouts.app')

@section('title', 'Koleksi Buku')
@section('page', 'library')

@section('content')

<section class="page-hero">
    <div class="container page-hero__inner">
        <p class="eyebrow">Perpustakaan Digital</p>
        <h1>Koleksi Buku</h1>
        <p>Temukan cerita berikutnya di antara {{ number_format($books->total()) }} buku digital yang siap dibaca.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="filter-bar">
            <input
                type="search"
                name="q"
                form="filter-form"
                value="{{ $filters['q'] }}"
                class="input"
                placeholder="Cari judul atau penulis…"
                aria-label="Cari buku"
            >
            <select name="kategori" form="filter-form" class="select" aria-label="Filter kategori">
                <option value="">Semua Kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->slug }}" @selected($filters['kategori'] === $category->slug)>{{ $category->name }}</option>
                @endforeach
            </select>
            <select name="series" form="filter-form" class="select" aria-label="Filter series">
                <option value="">Semua Series</option>
                @foreach ($series as $s)
                    <option value="{{ $s->slug }}" @selected($filters['series'] === $s->slug)>{{ $s->name }}</option>
                @endforeach
            </select>
            <select name="sort" form="filter-form" class="select" aria-label="Urutkan">
                <option value="terbaru" @selected($filters['sort'] === 'terbaru')>Terbaru</option>
                <option value="populer" @selected($filters['sort'] === 'populer')>Paling Populer</option>
                <option value="rating" @selected($filters['sort'] === 'rating')>Rating Tertinggi</option>
                <option value="judul" @selected($filters['sort'] === 'judul')>Judul A–Z</option>
            </select>
            <button type="submit" form="filter-form" class="btn btn--primary">Terapkan</button>
            <form id="filter-form" method="GET" action="{{ route('library') }}" hidden></form>
        </div>

        <p class="filter-result">{{ $books->total() }} buku ditemukan</p>

        @if ($books->isEmpty())
            <x-empty-state
                icon="search"
                title="Tidak ada buku ditemukan"
                text="Coba ubah kata kunci pencarian atau bersihkan filter untuk melihat semua koleksi."
                actionLabel="Reset Filter"
                actionUrl="{{ route('library') }}"
            />
        @else
            <div class="book-grid">
                @foreach ($books as $book)
                    <x-book-card :book="$book" />
                @endforeach
            </div>
            {{ $books->links('pagination.senja') }}
        @endif
    </div>
</section>

@endsection
