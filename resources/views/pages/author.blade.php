@extends('layouts.app')

@section('title', $author->name)
@section('page', 'author')

@section('content')

<section class="page-hero">
    <div class="container page-hero__inner">
        <p class="eyebrow">Penulis</p>
        <h1>{{ $author->name }}</h1>
        <p>{{ $author->bio }}</p>
    </div>
</section>

<section class="section">
    <div class="container">
        @if ($books->isEmpty())
            <x-empty-state
                icon="feather"
                title="Belum ada buku dari penulis ini"
                text="Coba lihat koleksi penulis lain di SenjaPustaka."
                actionLabel="Semua Penulis"
                actionUrl="{{ route('authors.index') }}"
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
