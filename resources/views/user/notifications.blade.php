@extends('layouts.app')

@section('title', 'Notifikasi')
@section('page', 'notifications')

@section('content')

<section class="section">
    <div class="container" style="max-width:760px;">
        <div class="section-head reveal">
            <div>
                <p class="eyebrow">Kotak Masuk</p>
                <h2><i data-lucide="bell" aria-hidden="true"></i> Notifikasi</h2>
            </div>
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <button type="submit" class="btn btn--ghost btn--sm">✓ Tandai semua dibaca</button>
            </form>
        </div>

        @if ($notifications->isEmpty())
            <x-empty-state icon="bell" title="Tidak ada notifikasi" text="Kamu akan mendapat notifikasi saat mendapat badge, menyelesaikan buku, atau ada kabar menarik." />
        @else
            @foreach ($notifications as $notification)
                <div class="notif-card {{ $notification->read_at ? '' : 'notif-card--unread' }}">
                    <span class="icon-chip icon-chip--sm">{{ mb_substr($notification->title, 0, 2) }}</span>
                    <div class="notif-card__body">
                        <strong>{{ $notification->title }}</strong>
                        @if ($notification->message)
                            <p>{{ $notification->message }}</p>
                        @endif
                        <time>{{ $notification->created_at->diffForHumans() }}</time>
                    </div>
                    @if ($notification->url)
                        <a href="{{ $notification->url }}" class="btn btn--ghost btn--sm">Buka →</a>
                    @endif
                </div>
            @endforeach

            {{ $notifications->links('pagination.senja') }}
        @endif
    </div>
</section>

@endsection
