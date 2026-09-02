@extends('layouts.app')

@section('title', $book->title)
@section('page', 'book')

@section('content')

<section class="section">
    <div class="container">
        <div class="book-detail">
            {{-- Kolom kiri: cover dengan glow --}}
            <div class="book-detail__cover-wrap reveal">
                <div class="book-detail__cover">
                    <x-book-cover :book="$book" />
                    @unless ($book->cover_image)
                        <span class="book-card__spine"></span>
                        <div class="book-card__title-cover" style="font-size:1.35rem;position:absolute;inset:0;z-index:1;">
                            <span>{{ $book->title }}</span>
                            <em>{{ $book->author?->name }}</em>
                        </div>
                    @endunless
                </div>
            </div>

            {{-- Kolom kanan: info --}}
            <div class="book-detail__info reveal" style="--d:80ms">
                <p class="eyebrow">
                    @foreach ($book->categories as $category)
                        <a href="{{ route('categories.show', $category) }}" style="color:inherit"><i data-lucide="{{ $category->iconName() }}" aria-hidden="true"></i> {{ $category->name }}</a>@if (! $loop->last) · @endif
                    @endforeach
                </p>
                <h1>{{ $book->title }}</h1>

                <div class="book-detail__meta">
                    <span><i data-lucide="pen-line" aria-hidden="true"></i> <a href="{{ route('authors.show', $book->author) }}">{{ $book->author?->name }}</a></span>
                    @if ($book->publisher)
                        <span><i data-lucide="building-2" aria-hidden="true"></i> {{ $book->publisher->name }}</span>
                    @endif
                    <span><i data-lucide="file-text" aria-hidden="true"></i> <span class="mono">{{ number_format($book->pages) }}</span> halaman</span>
                    @if ($book->year)
                        <span><i data-lucide="calendar" aria-hidden="true"></i> {{ $book->year }}</span>
                    @endif
                    <span><i data-lucide="globe" aria-hidden="true"></i> {{ strtoupper($book->language) }}</span>
                    <span><i data-lucide="eye" aria-hidden="true"></i> <span class="mono">{{ number_format($book->views) }}</span> dibaca</span>
                </div>

                <div class="book-detail__rating">
                    <span class="stars stars--lg" aria-label="Rating {{ number_format($book->rating_avg, 1) }}">
                        @for ($i = 1; $i <= 5; $i++)
                            <span style="{{ $i <= $book->rating_avg ? '' : 'color: var(--color-border);' }}">★</span>
                        @endfor
                    </span>
                    <span class="mono" style="font-weight:700;font-size:1.1rem;">{{ number_format($book->rating_avg, 1) }} dari 5</span>
                    <span class="text-muted small">({{ $book->rating_count }} ulasan)</span>
                </div>

                <div class="book-detail__actions">
                    <a href="{{ route('reader', $book) }}" class="btn btn--primary btn--lg"><i data-lucide="book-open" aria-hidden="true"></i> {{ $progress ? 'Lanjut Baca' : 'Mulai Baca' }}</a>

                    @auth
                        <button
                            type="button"
                            class="heart-btn js-favorite {{ $isFavorite ? 'is-active' : '' }}"
                            data-url="{{ route('favorites.toggle') }}"
                            data-book-id="{{ $book->id }}"
                            aria-label="{{ $isFavorite ? 'Hapus dari favorit' : 'Tambah ke favorit' }}"
                        >
                            <span class="heart-icon"><i data-lucide="heart" aria-hidden="true"></i></span>
                            <span class="burst" aria-hidden="true">
                                @for ($i = 0; $i < 8; $i++)<i data-lucide="heart" aria-hidden="true"></i>@endfor
                            </span>
                        </button>
                        <button
                            type="button"
                            class="icon-btn js-favorite-bookmark {{ $bookmark ? 'is-active' : '' }}"
                            data-url="{{ route('bookmarks.toggle') }}"
                            data-book-id="{{ $book->id }}"
                            style="width:46px;height:46px;"
                            aria-label="{{ $bookmark ? 'Hapus dari Baca Nanti' : 'Tambahkan ke Baca Nanti' }}"
                        >
                            <i data-lucide="bookmark" aria-hidden="true"></i>
                        </button>
                    @endauth
                </div>

                @if ($progress)
                    <div class="progress" data-percent="{{ $progress->progress_percent }}" style="margin-bottom:var(--sp-4);">
                        <div class="progress__bar {{ $progress->progress_percent >= 100 ? 'is-done' : '' }}"></div>
                    </div>
                    <p class="small text-muted">
                        @if ($progress->progress_percent >= 100)
                            <i data-lucide="circle-check" aria-hidden="true"></i> Kamu sudah menamatkan buku ini
                        @else
                            Progresmu <span class="mono">{{ $progress->current_page }}/{{ $book->pages }}</span> · {{ $progress->progress_percent }}%
                        @endif
                    </p>
                @endif

                <div class="book-detail__desc">
                    {!! nl2br(e($book->description ?? '')) !!}
                </div>

                {{-- Rating & review form --}}
                @auth
                    <form method="POST" action="{{ route('reviews.store') }}" class="rate-box">
                        @csrf
                        <input type="hidden" name="book_id" value="{{ $book->id }}">
                        <input type="hidden" name="rating" id="rating-input" value="{{ $userReview?->rating ?? 0 }}">
                        <div class="stars stars--input" data-target="rating-input" role="radiogroup" aria-label="Beri rating">
                            @for ($i = 1; $i <= 5; $i++)
                                <span class="star" data-value="{{ $i }}" style="{{ $i <= ($userReview?->rating ?? 0) ? '' : 'color: var(--color-border);' }}" role="radio" aria-label="{{ $i }} bintang">{{ $i <= ($userReview?->rating ?? 0) ? '★' : '☆' }}</span>
                            @endfor
                        </div>
                        <input type="text" name="comment" class="input" placeholder="{{ $userReview ? 'Perbarui ulasanmu…' : 'Tulis ulasan singkat (opsional)…' }}" value="{{ $userReview?->comment }}">
                        <button type="submit" class="btn btn--primary btn--sm">{{ $userReview ? 'Perbarui' : 'Kirim' }}</button>
                    </form>
                    <p class="small text-muted" style="margin-top:6px;">Rating dan ulasanmu membantu pembaca lain. <i data-lucide="star" class="text-amber" aria-hidden="true"></i></p>
                @else
                    <p class="small text-muted">
                        <a href="{{ route('login') }}">Masuk</a> untuk memberi rating &amp; ulasan.
                    </p>
                @endauth

                {{-- Daftar review --}}
                @if ($book->reviews->isNotEmpty())
                    <h2 style="margin-top:var(--sp-8);font-size:1.4rem;">Ulasan Pembaca</h2>
                    <div class="review-list">
                        @foreach ($book->reviews as $review)
                            <div class="review-card">
                                <span class="avatar">{{ $review->user->initials() }}</span>
                                <div class="review-card__body">
                                    <div class="review-card__head">
                                        <strong>{{ $review->user->name }}</strong>
                                        <x-stars :rating="$review->rating" />
                                        <time>{{ $review->created_at->diffForHumans() }}</time>
                                    </div>
                                    <p>{{ $review->comment }}</p>
                                </div>
                                @if (auth()->check() && ($review->user_id === auth()->id() || auth()->user()->is_admin))
                                    <div class="review-card__actions">
                                        <form method="POST" action="{{ route('reviews.destroy', $review) }}" onsubmit="return confirm('Hapus ulasan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn--danger btn--sm"><i data-lucide="trash-2" aria-hidden="true"></i></button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Series / chapter --}}
        @foreach ($book->seriesList as $serie)
            @php
                $chapters = $serie->books()
                    ->where('is_published', true)
                    ->withPivot('chapter_number')
                    ->orderBy('chapter_number')
                    ->get();
            @endphp
            @if ($chapters->count() > 1)
                <div style="margin-top:var(--sp-16);">
                    <div class="section-head reveal">
                        <div>
                            <p class="eyebrow">Series</p>
                            <h2><i data-lucide="library" aria-hidden="true"></i> {{ $serie->name }}</h2>
                        </div>
                    </div>
                    <div class="chapter-grid">
                        @foreach ($chapters as $chapter)
                            @php
                                $chapterProgress = $chapter->progressFor(auth()->user());
                                $done = $chapterProgress?->status === 'finished';
                            @endphp
                            <a href="{{ route('books.show', $chapter) }}" class="chapter-card reveal" style="--d:{{ $loop->index * 40 }}ms">
                                <span class="chapter-card__num">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <span class="chapter-card__body">
                                    <strong>{{ $chapter->title }}</strong>
                                    @if ($chapterProgress)
                                        <div class="progress progress--thin" data-percent="{{ $chapterProgress->progress_percent }}">
                                            <div class="progress__bar {{ $done ? 'is-done' : '' }}"></div>
                                        </div>
                                    @else
                                        <small class="text-muted">{{ $chapter->pages }} halaman</small>
                                    @endif
                                </span>
                                @if ($done)
                                    <span class="chapter-card__check"><i data-lucide="circle-check" aria-hidden="true"></i></span>
                                @else
                                    <span class="chapter-card__check">→</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach

        {{-- Buku terkait --}}
        @if ($related->isNotEmpty())
            <div style="margin-top:var(--sp-16);">
                <div class="section-head reveal">
                    <div>
                        <p class="eyebrow">Kamu mungkin suka</p>
                        <h2><i data-lucide="sparkles" aria-hidden="true"></i> Buku Terkait</h2>
                    </div>
                </div>
                <div class="book-grid">
                    @foreach ($related as $book)
                        <x-book-card :book="$book" />
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>

@endsection
