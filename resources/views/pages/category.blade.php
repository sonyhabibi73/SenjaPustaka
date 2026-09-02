@extends('layouts.app')

@section('title', $category->name)
@section('page', 'category')

@section('content')

<section class="page-hero">
    <div class="container page-hero__inner">
        <p class="eyebrow">Kategori</p>
        <h1><i data-lucide="{{ $category->iconName() }}" aria-hidden="true"></i> {{ $category->name }}</h1>
        <p>{{ $category->description }}</p>
    </div>
</section>

<section class="section">
    <div class="container">
        @if ($books->isEmpty())
            <x-empty-state
                icon="{{ $category->iconName() }}"
                title="Belum ada buku di kategori ini"
                text="Coba jelajahi kategori lain atau lihat semua koleksi."
                actionLabel="Lihat Semua Buku"
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
