@props(['rating' => 0, 'size' => ''])

@php
    $rating = (float) $rating;
@endphp

<span class="stars {{ $size }}" role="img" aria-label="Rating {{ number_format($rating, 1) }} dari 5">
    @for ($i = 1; $i <= 5; $i++)
        <span style="{{ $i <= $rating ? '' : 'color: var(--color-border);' }}">★</span>
    @endfor
</span>
