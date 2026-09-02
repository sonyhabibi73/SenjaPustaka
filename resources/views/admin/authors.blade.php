@extends('layouts.admin')

@section('title', 'Kelola Penulis')

@section('content')

<div class="admin-topbar">
    <h1><i data-lucide="feather" aria-hidden="true"></i> Penulis</h1>
    <a href="{{ route('admin.penulis.index') }}" class="btn btn--ghost">↻ Segarkan</a>
</div>

@if ($edit)
    <div class="admin-card">
        <h2><i data-lucide="pencil" aria-hidden="true"></i> Edit: {{ $edit->name }}</h2>
        <form method="POST" action="{{ route('admin.penulis.update', $edit) }}">
            @csrf
            @method('PUT')
            <div class="field"><label>Nama</label><input type="text" name="name" class="input" value="{{ old('name', $edit->name) }}" required></div>
            <div class="field"><label>Bio</label><textarea name="bio" class="textarea" style="min-height:90px;">{{ old('bio', $edit->bio) }}</textarea></div>
            <button type="submit" class="btn btn--primary">Simpan</button>
            <a href="{{ route('admin.penulis.index') }}" class="btn btn--ghost">Batal</a>
        </form>
    </div>
@else
    <div class="admin-card">
        <h2><i data-lucide="plus" aria-hidden="true"></i> Tambah Penulis</h2>
        <form method="POST" action="{{ route('admin.penulis.store') }}">
            @csrf
            <div class="field"><label>Nama</label><input type="text" name="name" class="input" placeholder="Nama penulis" required></div>
            <div class="field"><label>Bio</label><textarea name="bio" class="textarea" style="min-height:90px;" placeholder="Tentang penulis"></textarea></div>
            <button type="submit" class="btn btn--primary">Tambah</button>
        </form>
    </div>
@endif

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Penulis</th>
                <th>Jumlah Buku</th>
                <th style="text-align:right;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($authors as $author)
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <span class="author-card__avatar" style="width:36px;height:36px;font-size:.85rem;">{{ $author->initials() }}</span>
                            <strong>{{ $author->name }}</strong>
                        </div>
                    </td>
                    <td class="mono">{{ $author->books_count }}</td>
                    <td>
                        <div class="admin-table__actions">
                            <a href="{{ route('admin.penulis.index', ['edit' => $author->id]) }}" class="btn btn--gold">Edit</a>
                            <form method="POST" action="{{ route('admin.penulis.destroy', $author) }}" onsubmit="return confirm('Hapus penulis ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn--danger">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" style="text-align:center;padding:var(--sp-12);color:var(--color-muted);">Belum ada penulis.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
