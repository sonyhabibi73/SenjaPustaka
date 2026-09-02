@extends('layouts.app')

@section('title', 'Dashboard')
@section('page', 'dashboard')

@section('content')

<section class="section">
    <div class="container" style="max-width:980px;">

        {{-- 1. Profile header + 2. Salam dinamis --}}
        <div class="dash-hero reveal">
            @include('partials.avatar', ['user' => $user, 'class' => 'avatar--lg avatar--ring'])
            <div class="dash-hero__info">
                <p class="dash-hero__greeting">{{ $greeting['teks'] }} {{ $greeting['emoji'] }}</p>
                <h2 class="dash-hero__name">{{ $user->name }}</h2>
                <p class="dash-hero__email">{{ $user->email }} · Member sejak {{ $user->created_at->format('M Y') }}</p>
            </div>
            <a href="{{ route('profile.edit') }}" class="btn btn--ghost"><i data-lucide="pencil" aria-hidden="true"></i> Edit Profil</a>
        </div>

        {{-- 3. Level & Poin card (paling atas, motivasi utama) --}}
        <div class="level-card reveal" style="--d:60ms">
            <div class="level-badge">
                <span class="level-badge__num">{{ $level['number'] }}</span>
                <span class="level-badge__label">LEVEL</span>
            </div>
            <div class="level-card__body">
                <p class="eyebrow" style="margin-bottom:2px;">Level &amp; Poin</p>
                <h3>{{ $level['name'] }}</h3>
                <p style="margin:0;">
                    <span class="mono">{{ number_format($level['points']) }}</span>
                    <span class="text-muted"> poin</span>
                </p>
                <div class="level-card__progress-info">
                    <span>Level {{ $level['number'] }} · {{ number_format($level['current_threshold']) }} poin</span>
                    @if ($level['next'])
                        <span>{{ $level['next']['remaining'] }} poin lagi ke <strong>{{ $level['next']['name'] }}</strong></span>
                    @else
                        <span><i data-lucide="trophy" aria-hidden="true"></i> Level tertinggi tercapai!</span>
                    @endif
                </div>
                <div class="progress progress--gold" data-percent="{{ $level['progress'] }}">
                    <div class="progress__bar"></div>
                </div>
            </div>
        </div>

        {{-- 4. Quick stats --}}
        <div class="stat-grid">
            <div class="stat-card reveal" style="--d:0ms">
                <span class="icon-chip"><i data-lucide="book-open" aria-hidden="true"></i></span>
                <div>
                    <div class="stat-card__num count-up" data-count="{{ $stats['reading'] }}">{{ $stats['reading'] }}</div>
                    <div class="stat-card__label">Sedang Dibaca</div>
                </div>
            </div>
            <div class="stat-card reveal" style="--d:50ms">
                <span class="icon-chip"><i data-lucide="circle-check" aria-hidden="true"></i></span>
                <div>
                    <div class="stat-card__num count-up" data-count="{{ $stats['finished'] }}">{{ $stats['finished'] }}</div>
                    <div class="stat-card__label">Buku Selesai</div>
                </div>
            </div>
            <div class="stat-card reveal" style="--d:100ms">
                <span class="icon-chip"><i data-lucide="file-text" aria-hidden="true"></i></span>
                <div>
                    <div class="stat-card__num count-up" data-count="{{ $stats['pages'] }}">{{ $stats['pages'] }}</div>
                    <div class="stat-card__label">Halaman Dibaca</div>
                </div>
            </div>
            <div class="stat-card reveal" style="--d:150ms">
                <span class="icon-chip"><i data-lucide="heart" aria-hidden="true"></i></span>
                <div>
                    <div class="stat-card__num count-up" data-count="{{ $stats['favorites'] }}">{{ $stats['favorites'] }}</div>
                    <div class="stat-card__label">Buku Favorit</div>
                </div>
            </div>
        </div>

        {{-- 5. Newsletter card --}}
        <div class="newsletter-card reveal">
            <span class="icon-chip"><i data-lucide="mail" aria-hidden="true"></i></span>
            <div class="newsletter-card__body">
                <h3>Newsletter Mingguan</h3>
                <p>Rekomendasi buku pilihan + info badge baru, setiap minggu di inbox-mu.</p>
            </div>
            @if ($newsletter && $newsletter->subscribed)
                <span class="badge-pill"><i data-lucide="circle-check" aria-hidden="true"></i> Aktif</span>
                <form method="POST" action="{{ route('newsletter.toggle') }}" style="margin-left:8px;">
                    @csrf
                    <button type="submit" class="btn btn--ghost btn--sm">Nonaktifkan</button>
                </form>
            @else
                <span class="badge-pill" style="opacity:.7;">Belum Aktif</span>
                <form method="POST" action="{{ route('newsletter.toggle') }}" style="margin-left:8px;">
                    @csrf
                    <button type="submit" class="btn btn--primary btn--sm">Aktifkan</button>
                </form>
            @endif
        </div>

        {{-- 6. Tabs: Sedang Dibaca / Selesai / Favorit --}}
        <div class="js-tabs-wrap reveal">
            <div class="tabs js-tabs" role="tablist">
                <button type="button" class="tab-btn is-active" data-tab="reading" role="tab"><i data-lucide="book-open" aria-hidden="true"></i> Sedang Dibaca <span class="tab-count">{{ $reading->count() }}</span></button>
                <button type="button" class="tab-btn" data-tab="finished" role="tab"><i data-lucide="circle-check" aria-hidden="true"></i> Selesai <span class="tab-count">{{ $finished->count() }}</span></button>
                <button type="button" class="tab-btn" data-tab="favorites" role="tab"><i data-lucide="heart" aria-hidden="true"></i> Favorit <span class="tab-count">{{ $favorites->count() }}</span></button>
            </div>

            <div class="tab-pane is-active" data-tab="reading">
                @if ($reading->isEmpty())
                    <x-empty-state icon="book-open" title="Belum ada buku yang sedang dibaca" text="Pilih buku dari koleksi dan mulai baca — progresmu akan otomatis tersimpan." actionLabel="Jelajahi Koleksi" actionUrl="{{ route('library') }}" />
                @else
                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:var(--sp-4);">
                        @foreach ($reading as $item)
                            <a href="{{ route('reader', $item->book) }}" class="book-card book-card--row">
                                <div class="book-card__cover">
                                    <x-book-cover :book="$item->book" />
                                    @unless ($item->book->cover_image)
                                        <div class="book-card__title-cover"><span style="font-size:0.75rem;">{{ $item->book->title }}</span></div>
                                    @endunless
                                </div>
                                <div class="book-card__body">
                                    <span class="book-card__title">{{ $item->book->title }}</span>
                                    <span class="book-card__author">{{ $item->book->author?->name }}</span>
                                    <div class="progress" data-percent="{{ $item->progress_percent }}" style="margin-top:8px;">
                                        <div class="progress__bar"></div>
                                    </div>
                                    <span class="book-card__author">Halaman {{ $item->current_page }}/{{ $item->book->pages }} · {{ $item->progress_percent }}%</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="tab-pane" data-tab="finished">
                @if ($finished->isEmpty())
                    <x-empty-state icon="party-popper" title="Belum ada buku selesai" text="Selesaikan buku pertamamu untuk membuka badge 'Langkah Pertama'!" actionLabel="Jelajahi Koleksi" actionUrl="{{ route('library') }}" />
                @else
                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:var(--sp-4);">
                        @foreach ($finished as $item)
                            <a href="{{ route('books.show', $item->book) }}" class="book-card book-card--row">
                                <div class="book-card__cover">
                                    <x-book-cover :book="$item->book" />
                                    @unless ($item->book->cover_image)
                                        <div class="book-card__title-cover"><span style="font-size:0.75rem;">{{ $item->book->title }}</span></div>
                                    @endunless
                                </div>
                                <div class="book-card__body">
                                    <span class="book-card__title">{{ $item->book->title }}</span>
                                    <span class="book-card__author">{{ $item->book->author?->name }}</span>
                                    <span class="book-card__author" style="color:var(--color-amber);"><i data-lucide="circle-check" aria-hidden="true"></i> Selesai · {{ $item->finished_at?->diffForHumans() }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="tab-pane" data-tab="favorites">
                @if ($favorites->isEmpty())
                    <x-empty-state icon="heart" title="Belum ada buku favorit" text="Ketuk tombol hati di halaman buku untuk menyimpannya di sini." actionLabel="Jelajahi Koleksi" actionUrl="{{ route('library') }}" />
                @else
                    <div class="book-grid">
                        @foreach ($favorites as $book)
                            <x-book-card :book="$book" />
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- 7. Reading Goals --}}
        <div style="margin-top:var(--sp-12);" class="reveal">
            <div class="section-head">
                <div>
                    <p class="eyebrow">Target Tahun {{ $year }}</p>
                    <h2><i data-lucide="target" aria-hidden="true"></i> Reading Goals</h2>
                </div>
            </div>

            @if ($goalData)
                <div class="goal-grid">
                    <div class="goal-card">
                        <div class="goal-card__head">
                            <strong><i data-lucide="book-check" aria-hidden="true"></i> Buku Selesai</strong>
                            <span class="mono">{{ $goalData['books']['current'] }} / {{ $goalData['books']['target'] }}</span>
                        </div>
                        <div class="progress progress--gold" data-percent="{{ $goalData['books']['target'] > 0 ? min(100, round($goalData['books']['current'] / $goalData['books']['target'] * 100)) : 0 }}">
                            <div class="progress__bar {{ $goalData['books']['target'] > 0 && $goalData['books']['current'] >= $goalData['books']['target'] ? 'is-done' : '' }}"></div>
                        </div>
                    </div>
                    <div class="goal-card">
                        <div class="goal-card__head">
                            <strong><i data-lucide="file-text" aria-hidden="true"></i> Halaman</strong>
                            <span class="mono">{{ number_format($goalData['pages']['current']) }} / {{ number_format($goalData['pages']['target']) }}</span>
                        </div>
                        <div class="progress progress--gold" data-percent="{{ $goalData['pages']['target'] > 0 ? min(100, round($goalData['pages']['current'] / $goalData['pages']['target'] * 100)) : 0 }}">
                            <div class="progress__bar {{ $goalData['pages']['target'] > 0 && $goalData['pages']['current'] >= $goalData['pages']['target'] ? 'is-done' : '' }}"></div>
                        </div>
                    </div>
                </div>
            @else
                <div class="goal-grid">
                    <div class="goal-card" style="grid-column:1/-1;display:flex;align-items:center;justify-content:space-between;gap:var(--sp-4);flex-wrap:wrap;">
                        <div>
                            <strong>Belum ada target tahun ini</strong>
                            <p class="small text-muted" style="margin:4px 0 0;">Atur target buku &amp; halaman untuk tetap termotivasi.</p>
                        </div>
                        <button type="button" class="btn btn--primary btn--sm" onclick="document.getElementById('goal-form')?.classList.toggle('show')"><i data-lucide="target" aria-hidden="true"></i> Atur Target</button>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('goals.store') }}" id="goal-form" class="goal-form" style="display:{{ $goalData ? 'block' : 'none' }};">
                @csrf
                <input type="hidden" name="year" value="{{ $year }}">
                <div class="goal-form__grid">
                    <div class="field" style="margin:0;">
                        <label for="target_books">Target Buku</label>
                        <input type="number" id="target_books" name="target_books" class="input" min="0" value="{{ $goal?->target_books ?? 12 }}">
                    </div>
                    <div class="field" style="margin:0;">
                        <label for="target_pages">Target Halaman</label>
                        <input type="number" id="target_pages" name="target_pages" class="input" min="0" value="{{ $goal?->target_pages ?? 3650 }}">
                    </div>
                    <button type="submit" class="btn btn--primary">Simpan</button>
                </div>
            </form>
        </div>

        {{-- 8. Gamification: Streak + Badges --}}
        <div style="margin-top:var(--sp-12);" class="reveal">
            <div class="section-head">
                <div>
                    <p class="eyebrow">Gamifikasi</p>
                    <h2><i data-lucide="flame" aria-hidden="true"></i> Streak &amp; <i data-lucide="medal" aria-hidden="true"></i> Badges</h2>
                </div>
            </div>

            <div class="streak-card {{ $user->streak_days >= 7 ? 'is-hot' : '' }}">
                <span class="streak-card__icon"><i data-lucide="flame" aria-hidden="true"></i></span>
                <div class="streak-card__body">
                    <div class="streak-card__num">{{ $user->streak_days }}<span style="font-size:1rem;color:var(--color-muted);"> hari</span></div>
                    <p>Streak membaca harianmu{{ $user->streak_days >= 7 ? ' — luar biasa, pertahankan!' : ' — baca hari ini untuk menjaganya.' }}</p>
                </div>
                <div style="text-align:right;">
                    <div class="mono text-amber" style="font-size:1.2rem;font-weight:700;">{{ $user->longest_streak }} <i data-lucide="flame" aria-hidden="true"></i></div>
                    <span class="small text-muted">streak terpanjang</span>
                </div>
            </div>

            <div class="badge-grid">
                @foreach ($badges as $badge)
                    @php
                        $earned = $ownedBadgeIds->contains($badge->id);
                    @endphp
                    <div class="badge-item {{ $earned ? 'badge-item--earned' : 'badge-item--locked' }}">
                        <span class="badge-item__emoji">{{ $badge->emoji }}</span>
                        <strong>{{ $badge->name }}</strong>
                        <span>{{ $badge->description }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- 9. Rekomendasi Untukmu --}}
        <div style="margin-top:var(--sp-12);" class="reveal">
            <div class="section-head">
                <div>
                    <p class="eyebrow">Berdasarkan minat bacamu</p>
                    <h2><i data-lucide="sparkles" aria-hidden="true"></i> Rekomendasi Untukmu</h2>
                </div>
            </div>
            @if ($recommendations->isEmpty())
                <x-empty-state icon="book-marked" title="Baca beberapa buku dulu biar makin pas" text="Rekomendasi personal akan muncul setelah kamu membaca beberapa buku." actionLabel="Jelajahi Koleksi" actionUrl="{{ route('library') }}" />
            @else
                <div class="book-grid">
                    @foreach ($recommendations as $book)
                        <x-book-card :book="$book" badge="Rekomendasi" badge-icon="sparkles" />
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</section>

@endsection
