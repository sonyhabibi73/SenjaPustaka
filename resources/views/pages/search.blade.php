@extends('layouts.app')

@section('title', 'Hasil Pencarian')
@section('page', 'search')

@section('content')

<section class="page-hero">
    <div class="container page-hero__inner">
        <p class="eyebrow">Pencarian</p>
        <h1>Hasil Pencarian</h1>
        <p>Menampilkan hasil untuk pencarian di seluruh koleksi SenjaPustaka.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="filter-bar">
            <input
                type="search"
                name="q"
                form="search-form"
                value="{{ $q }}"
                class="input"
                placeholder="Cari judul atau penulis…"
                aria-label="Cari buku"
            >
            <button type="submit" form="search-form" class="btn btn--primary"><i data-lucide="search" aria-hidden="true"></i> Cari</button>
            <form id="search-form" method="GET" action="{{ route('search') }}" hidden></form>
        </div>

        @if ($q !== '')
            <p class="filter-result">{{ $books->total() }} hasil untuk "{{ $q }}"</p>
        @else
            <p class="filter-result">Ketik kata kunci untuk mulai mencari.</p>
        @endif

        @if ($q !== '' && $books->isEmpty())
            <x-empty-state
                icon="search"
                title="Tidak ada hasil"
                text="Kami tidak menemukan buku dengan kata kunci itu. Coba kata lain atau telusuri koleksi lengkap."
                actionLabel="Lihat Semua Buku"
                actionUrl="{{ route('library') }}"
            />
        @elseif ($books->isNotEmpty())
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
