@props(['emoji' => null, 'icon' => null, 'title', 'text', 'actionLabel' => null, 'actionUrl' => null])

<div class="empty-state">
    <span class="empty-state__icon">
        @if ($icon)
            <i data-lucide="{{ $icon }}" aria-hidden="true"></i>
        @else
            {{ $emoji }}
        @endif
    </span>
    <h3>{{ $title }}</h3>
    <p>{{ $text }}</p>
    @if ($actionLabel && $actionUrl)
        <a href="{{ $actionUrl }}" class="btn btn--primary">{{ $actionLabel }}</a>
    @endif
</div>
