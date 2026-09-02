@extends('layouts.admin')

@section('title', 'Kelola Penerbit')

@section('content')

<div class="admin-topbar">
    <h1><i data-lucide="building-2" aria-hidden="true"></i> Penerbit</h1>
    <a href="{{ route('admin.penerbit.index') }}" class="btn btn--ghost">↻ Segarkan</a>
</div>

@if ($edit)
    <div class="admin-card">
        <h2><i data-lucide="pencil" aria-hidden="true"></i> Edit: {{ $edit->name }}</h2>
        <form method="POST" action="{{ route('admin.penerbit.update', $edit) }}">
            @csrf
            @method('PUT')
            <div class="field"><label>Nama</label><input type="text" name="name" class="input" value="{{ old('name', $edit->name) }}" required></div>
            <button type="submit" class="btn btn--primary">Simpan</button>
            <a href="{{ route('admin.penerbit.index') }}" class="btn btn--ghost">Batal</a>
        </form>
    </div>
@else
    <div class="admin-card">
        <h2><i data-lucide="plus" aria-hidden="true"></i> Tambah Penerbit</h2>
        <form method="POST" action="{{ route('admin.penerbit.store') }}">
            @csrf
            <div class="field"><label>Nama</label><input type="text" name="name" class="input" placeholder="Nama penerbit" required></div>
            <button type="submit" class="btn btn--primary">Tambah</button>
        </form>
    </div>
@endif

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Penerbit</th>
                <th>Jumlah Buku</th>
                <th style="text-align:right;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($publishers as $publisher)
                <tr>
                    <td><strong>{{ $publisher->name }}</strong></td>
                    <td class="mono">{{ $publisher->books_count }}</td>
                    <td>
                        <div class="admin-table__actions">
                            <a href="{{ route('admin.penerbit.index', ['edit' => $publisher->id]) }}" class="btn btn--gold">Edit</a>
                            <form method="POST" action="{{ route('admin.penerbit.destroy', $publisher) }}" onsubmit="return confirm('Hapus penerbit ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn--danger">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" style="text-align:center;padding:var(--sp-12);color:var(--color-muted);">Belum ada penerbit.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
