@extends('layouts.admin')

@section('title', 'Dashboard Admin')

@section('content')

<div class="admin-topbar">
    <h1><i data-lucide="layout-dashboard" aria-hidden="true"></i> Dashboard</h1>
    <a href="{{ route('admin.buku.create') }}" class="btn btn--primary">+ Tambah Buku</a>
</div>

<div class="admin-stats">
    <div class="admin-stat">
        <span class="admin-stat__icon"><i data-lucide="library-big" aria-hidden="true"></i></span>
        <div>
            <div class="admin-stat__num">{{ $stats['buku'] }}</div>
            <div class="admin-stat__label">Total Buku</div>
        </div>
    </div>
    <div class="admin-stat">
        <span class="admin-stat__icon"><i data-lucide="users" aria-hidden="true"></i></span>
        <div>
            <div class="admin-stat__num">{{ $stats['user'] }}</div>
            <div class="admin-stat__label">Pengguna</div>
        </div>
    </div>
    <div class="admin-stat">
        <span class="admin-stat__icon"><i data-lucide="star" aria-hidden="true"></i></span>
        <div>
            <div class="admin-stat__num">{{ $stats['review'] }}</div>
            <div class="admin-stat__label">Review</div>
        </div>
    </div>
    <div class="admin-stat">
        <span class="admin-stat__icon"><i data-lucide="mail" aria-hidden="true"></i></span>
        <div>
            <div class="admin-stat__num">{{ $stats['subscriber'] }}</div>
            <div class="admin-stat__label">Subscriber Aktif</div>
        </div>
    </div>
</div>

<div class="admin-dash-grid">

    <div class="admin-card">
        <h2><i data-lucide="flame" aria-hidden="true"></i> Buku Paling Populer</h2>
        <div class="admin-table-wrap" style="margin:0;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Buku</th>
                        <th>Views</th>
                        <th>Rating</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($topBooks as $book)
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <div class="table-cover">
                                        <x-book-cover :book="$book" />
                                    </div>
                                    <div>
                                        <strong style="display:block;">{{ $book->title }}</strong>
                                        <small class="text-muted">{{ $book->author?->name }}</small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="mono">{{ number_format($book->views) }}</span></td>
                            <td><span class="mono text-amber">★ {{ number_format($book->rating_avg, 1) }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div>
        <div class="admin-card">
            <h2><i data-lucide="users" aria-hidden="true"></i> Pengguna Terbaru</h2>
            @foreach ($recentUsers as $user)
                <div style="display:flex;align-items:center;gap:12px;padding:8px 0;border-bottom:1px solid var(--color-border);">
                    <span class="avatar" style="width:36px;height:36px;font-size:.75rem;">{{ $user->initials() }}</span>
                    <div style="flex:1;">
                        <strong style="font-size:.9rem;display:block;">{{ $user->name }}</strong>
                        <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                    </div>
                    <span class="badge-pill">{{ number_format($user->points) }} <i data-lucide="star" aria-hidden="true"></i></span>
                </div>
            @endforeach
        </div>

        <div class="admin-card">
            <h2><i data-lucide="star" aria-hidden="true"></i> Review Terbaru</h2>
            @foreach ($recentReviews as $review)
                <div style="padding:8px 0;border-bottom:1px solid var(--color-border);">
                    <strong style="font-size:.88rem;">{{ $review->user?->name }}</strong>
                    <span class="mono text-amber" style="font-size:.8rem;"> ★{{ $review->rating }}</span>
                    <p style="margin:2px 0 0;font-size:.82rem;color:var(--color-muted);">{{ Str::limit($review->comment ?? '—', 90) }}</p>
                </div>
            @endforeach
        </div>
    </div>
</div>

@endsection
