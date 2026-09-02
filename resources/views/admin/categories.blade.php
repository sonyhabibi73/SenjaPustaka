@extends('layouts.admin')

@section('title', 'Kelola Kategori')

@section('content')

<div class="admin-topbar">
    <h1><i data-lucide="tags" aria-hidden="true"></i> Kategori</h1>
    <a href="{{ route('admin.kategori.index') }}" class="btn btn--ghost">↻ Segarkan</a>
</div>

@if ($edit)
    <div class="admin-card">
        <h2><i data-lucide="pencil" aria-hidden="true"></i> Edit: {{ $edit->name }}</h2>
        <form method="POST" action="{{ route('admin.kategori.update', $edit) }}">
            @csrf
            @method('PUT')
            <div class="admin-card__grid">
                <div class="field"><label>Nama</label><input type="text" name="name" class="input" value="{{ old('name', $edit->name) }}" required></div>
                <div class="field"><label>Ikon (nama Lucide)</label><input type="text" name="emoji" class="input" value="{{ old('emoji', $edit->emoji) }}" maxlength="32" placeholder="contoh: book-open, sparkles, heart"></div>
            </div>
            <div class="field"><label>Deskripsi</label><input type="text" name="description" class="input" value="{{ old('description', $edit->description) }}"></div>
            <button type="submit" class="btn btn--primary">Simpan</button>
            <a href="{{ route('admin.kategori.index') }}" class="btn btn--ghost">Batal</a>
        </form>
    </div>
@else
    <div class="admin-card">
        <h2><i data-lucide="plus" aria-hidden="true"></i> Tambah Kategori</h2>
        <form method="POST" action="{{ route('admin.kategori.store') }}">
            @csrf
            <div class="admin-card__grid">
                <div class="field"><label>Nama</label><input type="text" name="name" class="input" placeholder="Contoh: Fiksi" required></div>
                <div class="field"><label>Ikon (nama Lucide)</label><input type="text" name="emoji" class="input" placeholder="contoh: book-open, sparkles, heart" maxlength="32"></div>
            </div>
            <div class="field"><label>Deskripsi</label><input type="text" name="description" class="input" placeholder="Deskripsi singkat kategori"></div>
            <button type="submit" class="btn btn--primary">Tambah</button>
        </form>
    </div>
@endif

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Kategori</th>
                <th>Jumlah Buku</th>
                <th style="text-align:right;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($categories as $category)
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <span class="icon-chip icon-chip--sm"><i data-lucide="{{ $category->iconName() }}" aria-hidden="true"></i></span>
                            <strong>{{ $category->name }}</strong>
                        </div>
                    </td>
                    <td class="mono">{{ $category->books_count }}</td>
                    <td>
                        <div class="admin-table__actions">
                            <a href="{{ route('admin.kategori.index', ['edit' => $category->id]) }}" class="btn btn--gold">Edit</a>
                            <form method="POST" action="{{ route('admin.kategori.destroy', $category) }}" onsubmit="return confirm('Hapus kategori ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn--danger">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" style="text-align:center;padding:var(--sp-12);color:var(--color-muted);">Belum ada kategori.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
