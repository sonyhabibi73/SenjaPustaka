@props(['book'])

@if ($book->coverUrl())
    <img
        src="{{ $book->coverUrl() }}"
        alt="{{ $book->title }}"
        class="book-cover-img"
        loading="lazy"
    >
@else
    <div class="book-card__gradient" style="--cover-grad: {{ $book->coverGradient() }}"></div>
@endif
