@extends('layouts.admin')

@section('title', 'Kelola Review')

@section('content')

<div class="admin-topbar">
    <h1><i data-lucide="star" aria-hidden="true"></i> Review</h1>
</div>

<div class="admin-card" style="padding:var(--sp-4);">
    <form method="GET" action="{{ route('admin.review.index') }}" style="display:flex;gap:var(--sp-3);">
        <input type="search" name="q" class="input" value="{{ request('q') }}" placeholder="Cari pengguna atau buku…">
        <button type="submit" class="btn btn--primary">Cari</button>
    </form>
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Pengguna</th>
                <th>Buku</th>
                <th>Rating</th>
                <th>Ulasan</th>
                <th style="text-align:right;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($reviews as $review)
                <tr>
                    <td>{{ $review->user?->name }}</td>
                    <td>{{ Str::limit($review->book?->title, 40) }}</td>
                    <td><span class="mono text-amber">★ {{ $review->rating }}</span></td>
                    <td style="max-width:280px;">{{ Str::limit($review->comment ?? '—', 80) }}</td>
                    <td>
                        <div class="admin-table__actions">
                            <form method="POST" action="{{ route('admin.review.destroy', $review) }}" onsubmit="return confirm('Hapus review ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn--danger">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center;padding:var(--sp-12);color:var(--color-muted);">Belum ada review.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $reviews->links('pagination.senja') }}

@endsection
