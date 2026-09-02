@props(['book', 'badge' => null, 'badgeIcon' => null, 'tilt' => false])

<a
    href="{{ route('books.show', $book) }}"
    class="book-card {{ $tilt ? 'js-tilt' : '' }}"
    @if ($tilt) style="transform-style:preserve-3d" @endif
>
    <div class="book-card__cover">
        <x-book-cover :book="$book" />
        @unless ($book->cover_image)
            <span class="book-card__spine"></span>
            <div class="book-card__title-cover">
                <span>{{ $book->title }}</span>
                <em>{{ $book->author?->name }}</em>
            </div>
        @endunless
        @if ($badge)
            <span class="book-card__badge">
                @if ($badgeIcon)<i data-lucide="{{ $badgeIcon }}" aria-hidden="true"></i>@endif
                {{ $badge }}
            </span>
        @endif
        @if ($book->rating_count > 0)
            <span class="book-card__rating-badge">★ {{ number_format($book->rating_avg, 1) }} dari 5</span>
        @endif
        <span class="book-card__views"><i data-lucide="eye" aria-hidden="true"></i> {{ number_format($book->views) }} dibaca</span>
    </div>
    <div class="book-card__body">
        <span class="book-card__title">{{ $book->title }}</span>
        <span class="book-card__author">{{ $book->author?->name }}</span>
    </div>
</a>
