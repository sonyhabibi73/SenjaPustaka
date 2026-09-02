@extends('layouts.app')

@section('title', 'Penulis')
@section('page', 'authors')

@section('content')

<section class="page-hero">
    <div class="container page-hero__inner">
        <p class="eyebrow">Di Balik Cerita</p>
        <h1><i data-lucide="feather" aria-hidden="true"></i> Penulis</h1>
        <p>Kenali para penulis yang menghidupkan cerita di rak SenjaPustaka.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        @if ($authors->isEmpty())
            <x-empty-state
                icon="feather"
                title="Belum ada penulis"
                text="Profil penulis akan muncul setelah admin menambahkannya."
            />
        @else
            <div class="author-grid">
                @foreach ($authors as $index => $author)
                    <a href="{{ route('authors.show', $author) }}" class="author-card reveal" style="--d: {{ $index * 40 }}ms">
                        <span class="author-card__avatar">{{ $author->initials() }}</span>
                        <span class="author-card__body">
                            <strong>{{ $author->name }}</strong>
                            <span>{{ $author->books_count }} buku</span>
                        </span>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</section>

@endsection
