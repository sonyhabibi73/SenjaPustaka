@extends('layouts.admin')

@section('title', 'Kelola Series')

@section('content')

<div class="admin-topbar">
    <h1><i data-lucide="book-open" aria-hidden="true"></i> Series</h1>
    <a href="{{ route('admin.series.index') }}" class="btn btn--ghost">↻ Segarkan</a>
</div>

@if ($edit)
    <div class="admin-card">
        <h2><i data-lucide="pencil" aria-hidden="true"></i> Edit: {{ $edit->name }}</h2>
        <form method="POST" action="{{ route('admin.series.update', $edit) }}">
            @csrf
            @method('PUT')
            <div class="field"><label>Nama</label><input type="text" name="name" class="input" value="{{ old('name', $edit->name) }}" required></div>
            <div class="field"><label>Deskripsi</label><textarea name="description" class="textarea" style="min-height:90px;">{{ old('description', $edit->description) }}</textarea></div>
            <button type="submit" class="btn btn--primary">Simpan</button>
            <a href="{{ route('admin.series.index') }}" class="btn btn--ghost">Batal</a>
        </form>
    </div>
@else
    <div class="admin-card">
        <h2><i data-lucide="plus" aria-hidden="true"></i> Tambah Series</h2>
        <form method="POST" action="{{ route('admin.series.store') }}">
            @csrf
            <div class="field"><label>Nama</label><input type="text" name="name" class="input" placeholder="Nama series" required></div>
            <div class="field"><label>Deskripsi</label><textarea name="description" class="textarea" style="min-height:90px;" placeholder="Tentang series ini"></textarea></div>
            <button type="submit" class="btn btn--primary">Tambah</button>
        </form>
    </div>
@endif

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Series</th>
                <th>Jumlah Buku</th>
                <th style="text-align:right;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($series as $s)
                <tr>
                    <td><strong>{{ $s->name }}</strong></td>
                    <td class="mono">{{ $s->books_count }}</td>
                    <td>
                        <div class="admin-table__actions">
                            <a href="{{ route('admin.series.index', ['edit' => $s->id]) }}" class="btn btn--gold">Edit</a>
                            <form method="POST" action="{{ route('admin.series.destroy', $s) }}" onsubmit="return confirm('Hapus series ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn--danger">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" style="text-align:center;padding:var(--sp-12);color:var(--color-muted);">Belum ada series.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
