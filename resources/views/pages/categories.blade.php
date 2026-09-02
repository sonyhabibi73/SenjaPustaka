@extends('layouts.app')

@section('title', 'Kategori')
@section('page', 'categories')

@section('content')

<section class="page-hero">
    <div class="container page-hero__inner">
        <p class="eyebrow">Jelajahi Perpustakaan</p>
        <h1><i data-lucide="tags" aria-hidden="true"></i> Kategori Buku</h1>
        <p>Dari fantasi sampai bisnis — temukan genre yang paling kamu suka.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        @if ($categories->isEmpty())
            <x-empty-state
                icon="tags"
                title="Belum ada kategori"
                text="Kategori akan muncul setelah admin menambahkannya."
            />
        @else
            <div class="cat-grid">
                @foreach ($categories as $index => $category)
                    <a href="{{ route('categories.show', $category) }}" class="cat-card reveal" style="--d: {{ $index * 40 }}ms">
                        <span class="icon-chip"><i data-lucide="{{ $category->iconName() }}" aria-hidden="true"></i></span>
                        <span class="cat-card__body">
                            <strong>{{ $category->name }}</strong>
                            <span>{{ $category->books_count }} buku</span>
                        </span>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</section>

@endsection
