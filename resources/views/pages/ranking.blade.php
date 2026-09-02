@extends('layouts.app')

@section('title', 'Peringkat Buku')
@section('page', 'ranking')

@section('content')

<section class="page-hero">
    <div class="container page-hero__inner">
        <p class="eyebrow">Karya Terbaik</p>
        <h1><i data-lucide="trophy" aria-hidden="true"></i> Peringkat Buku</h1>
        <p>{{ $books->count() }} buku dengan rating tertinggi dari ulasan pembaca SenjaPustaka.</p>
        <div style="margin-top:var(--sp-6);">
            <a href="{{ route('leaderboard') }}" class="btn btn--light"><i data-lucide="medal" aria-hidden="true"></i> Peringkat Pembaca</a>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        @if ($books->isEmpty())
            <x-empty-state
                icon="trophy"
                title="Belum ada peringkat"
                text="Belum ada cukup ulasan untuk menyusun peringkat. Baca buku dan beri rating pertamamu!"
                actionLabel="Jelajahi Buku"
                actionUrl="{{ route('library') }}"
            />
        @else
            @foreach ($books as $index => $book)
                <div class="rank-row reveal" style="--d: {{ $index * 40 }}ms">
                    <span class="rank-row__num {{ $index === 0 ? 'is-1' : ($index === 1 ? 'is-2' : ($index === 2 ? 'is-3' : '')) }}">
                        {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                    </span>
                    <div class="rank-row__cover">
                        <x-book-cover :book="$book" />
                    </div>
                    <div class="rank-row__info">
                        <strong><a href="{{ route('books.show', $book) }}" style="color:inherit">{{ $book->title }}</a></strong>
                        <span>{{ $book->author?->name }}</span>
                    </div>
                    <div class="rank-row__rating">
                        <span class="stars">★ {{ number_format($book->rating_avg, 1) }} dari 5</span>
                        <small class="text-muted">({{ $book->rating_count }} ulasan)</small>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</section>

@endsection
