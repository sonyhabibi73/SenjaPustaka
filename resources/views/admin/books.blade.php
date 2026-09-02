@extends('layouts.admin')

@section('title', 'Kelola Buku')

@section('content')

<div class="admin-topbar">
    <h1><i data-lucide="library-big" aria-hidden="true"></i> Buku</h1>
    <a href="{{ route('admin.buku.create') }}" class="btn btn--primary">+ Tambah Buku</a>
</div>

<div class="admin-card" style="padding:var(--sp-4);">
    <form method="GET" action="{{ route('admin.buku.index') }}" style="display:flex;gap:var(--sp-3);">
        <input type="search" name="q" class="input" value="{{ request('q') }}" placeholder="Cari judul buku…">
        <button type="submit" class="btn btn--primary">Cari</button>
    </form>
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Buku</th>
                <th>Penulis</th>
                <th>Halaman</th>
                <th>Views</th>
                <th>Rating</th>
                <th>Status</th>
                <th style="text-align:right;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($books as $book)
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div class="table-cover">
                                <x-book-cover :book="$book" />
                            </div>
                            <strong style="font-size:.9rem;">{{ $book->title }}</strong>
                        </div>
                    </td>
                    <td>{{ $book->author?->name }}</td>
                    <td class="mono">{{ $book->pages }}</td>
                    <td class="mono">{{ number_format($book->views) }}</td>
                    <td class="mono text-amber">★ {{ number_format($book->rating_avg, 1) }}</td>
                    <td>
                        @if ($book->is_published)
                            <span class="badge-pill">Published</span>
                        @else
                            <span class="badge-pill" style="opacity:.6;">Draft</span>
                        @endif
                    </td>
                    <td>
                        <div class="admin-table__actions">
                            <a href="{{ route('books.show', $book) }}" class="btn btn--ghost" target="_blank">Lihat</a>
                            <a href="{{ route('admin.buku.edit', $book) }}" class="btn btn--gold">Edit</a>
                            <form method="POST" action="{{ route('admin.buku.destroy', $book) }}" onsubmit="return confirm('Hapus buku ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn--danger">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center;padding:var(--sp-12);color:var(--color-muted);">
                        Belum ada buku. <a href="{{ route('admin.buku.create') }}">Tambahkan sekarang</a>.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $books->links('pagination.senja') }}

@endsection
