@extends('layouts.app')

@section('title', 'Peringkat Pembaca')
@section('page', 'leaderboard')

@section('content')

<section class="page-hero">
    <div class="container page-hero__inner">
        <p class="eyebrow">Para Pecinta Buku</p>
        <h1><i data-lucide="medal" aria-hidden="true"></i> Peringkat Pembaca</h1>
        <p>Pembaca dengan poin terbanyak — siapa yang akan jadi Dewa Baca berikutnya?</p>
        <div style="margin-top:var(--sp-6);">
            <a href="{{ route('ranking') }}" class="btn btn--light"><i data-lucide="trophy" aria-hidden="true"></i> Peringkat Buku</a>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        @if ($users->count() >= 3)
            <div class="podium">
                @foreach ([1, 0, 2] as $pos)
                    @php
                        $user = $users[$pos] ?? null;
                    @endphp
                    @if ($user)
                        <div class="podium__item podium__item--{{ $pos + 1 }} reveal" style="--d: {{ $pos * 80 }}ms">
                            <div class="podium__avatar">
                                @if ($pos === 0)
                                    <span class="crown"><i data-lucide="crown" aria-hidden="true"></i></span>
                                @endif
                                <span class="avatar avatar--lg {{ $pos === 0 ? 'avatar--ring' : '' }}">{{ $user->initials() }}</span>
                            </div>
                            <div>
                                <strong>{{ $user->name }}</strong>
                                <div class="podium__points">{{ number_format($user->points) }} poin</div>
                                <small class="text-muted">{{ $user->badges_count }} badge · streak {{ $user->streak_days }} <i data-lucide="flame" aria-hidden="true"></i></small>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif

        <div class="podium__fallback"></div>

        @foreach ($users as $index => $user)
            @if ($index >= 3)
                <div class="leader-row reveal {{ auth()->id() === $user->id ? 'is-me' : '' }}" style="--d: {{ $index * 30 }}ms">
                    <span class="leader-row__num">{{ $index + 1 }}</span>
                    <span class="avatar">{{ $user->initials() }}</span>
                    <div class="leader-row__info">
                        <strong>
                            {{ $user->name }}
                            @if (auth()->id() === $user->id)
                                <span class="me-label">Kamu</span>
                            @endif
                        </strong>
                    </div>
                    <span class="leader-row__badges">{{ $user->badges_count }} <i data-lucide="medal" aria-hidden="true"></i></span>
                    <span class="leader-row__points">{{ number_format($user->points) }} <i data-lucide="star" aria-hidden="true"></i></span>
                </div>
            @endif
        @endforeach

        @if ($users->isEmpty())
            <x-empty-state
                icon="medal"
                title="Belum ada peringkat pembaca"
                text="Jadilah pembaca pertama yang mengumpulkan poin! Mulai baca sekarang."
                actionLabel="Mulai Membaca"
                actionUrl="{{ route('library') }}"
            />
        @endif
    </div>
</section>

@endsection
