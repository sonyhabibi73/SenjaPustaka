@extends('layouts.admin')

@section('title', $book ? 'Edit Buku' : 'Tambah Buku')

@section('content')

<div class="admin-topbar">
    <h1><i data-lucide="{{ $book ? 'pencil' : 'plus' }}" aria-hidden="true"></i> {{ $book ? 'Edit Buku' : 'Tambah Buku' }}</h1>
    <a href="{{ route('admin.buku.index') }}" class="btn btn--ghost">← Kembali</a>
</div>

<div class="admin-card">
    <form
        method="POST"
        action="{{ $book ? route('admin.buku.update', $book) : route('admin.buku.store') }}"
        enctype="multipart/form-data"
    >
        @csrf
        @if ($book)
            @method('PUT')
        @endif

        <div class="admin-card__grid">
            <div class="field">
                <label for="title">Judul *</label>
                <input type="text" id="title" name="title" class="input" value="{{ old('title', $book?->title) }}" required>
            </div>
            <div class="field">
                <label for="author_id">Penulis *</label>
                <select id="author_id" name="author_id" class="select" required>
                    @foreach ($authors as $author)
                        <option value="{{ $author->id }}" @selected(old('author_id', $book?->author_id) == $author->id)>{{ $author->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="publisher_id">Penerbit</label>
                <select id="publisher_id" name="publisher_id" class="select">
                    <option value="">— Pilih —</option>
                    @foreach ($publishers as $publisher)
                        <option value="{{ $publisher->id }}" @selected(old('publisher_id', $book?->publisher_id) == $publisher->id)>{{ $publisher->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="pages">Jumlah Halaman *</label>
                <input type="number" id="pages" name="pages" class="input" min="1" value="{{ old('pages', $book?->pages ?? 1) }}" required>
            </div>
            <div class="field">
                <label for="year">Tahun Terbit</label>
                <input type="number" id="year" name="year" class="input" min="1900" max="2100" value="{{ old('year', $book?->year) }}">
            </div>
            <div class="field">
                <label for="language">Bahasa</label>
                <select id="language" name="language" class="select">
                    <option value="id" @selected(old('language', $book?->language ?? 'id') === 'id')>Indonesia</option>
                    <option value="en" @selected(old('language', $book?->language) === 'en')>English</option>
                </select>
            </div>
            <div class="field">
                <label for="cover_image">Gambar Cover (JPG/PNG/WebP, maks 2MB)</label>
                <input type="file" id="cover_image" name="cover_image" class="input" accept="image/jpeg,image/png,image/webp" onchange="previewCover(this)">
                <div id="cover-preview" style="margin-top:10px;">
                    @if ($book?->coverUrl())
                        <div style="position:relative;display:inline-block;">
                            <img src="{{ $book->coverUrl() }}" alt="Cover saat ini" style="width:96px;aspect-ratio:3/4;object-fit:cover;border-radius:10px;box-shadow:var(--shadow-soft);">
                            <span style="position:absolute;bottom:6px;left:0;right:0;text-align:center;font-size:.68rem;color:#fff;background:rgba(13,21,30,.6);padding:2px 6px;border-radius:6px;">Cover saat ini</span>
                        </div>
                    @endif
                </div>
                @if ($book?->cover_image)
                    <label style="display:inline-flex;align-items:center;gap:6px;margin-top:8px;font-size:.82rem;cursor:pointer;">
                        <input type="checkbox" name="remove_cover" value="1" style="accent-color:var(--color-danger);">
                        <i data-lucide="trash-2" aria-hidden="true"></i> Hapus gambar cover (kembali ke gradien warna)
                    </label>
                @endif
                <p class="small text-muted" style="margin:6px 0 0;">Jika diisi, gambar ini yang dipakai di semua halaman. Kosongkan untuk memakai gradien warna.</p>
            </div>
            <div class="field">
                <label for="cover_color">Warna Cover (hex) — fallback</label>
                <input type="color" id="cover_color" name="cover_color" class="input" value="{{ old('cover_color', $book?->cover_color ?? '#274A66') }}" style="padding:4px;height:46px;">
            </div>
            <div class="field">
                <label for="file">Berkas PDF / CBZ ({{ $book ? 'kosongkan jika tidak diganti' : 'opsional' }} — maks 200MB)</label>
                <input type="file" id="file" name="file" class="input" accept=".pdf,.cbz">
            </div>
        </div>

        <div class="field">
            <label for="description">Deskripsi</label>
            <textarea id="description" name="description" class="textarea" placeholder="Sinopsis singkat buku…">{{ old('description', $book?->description) }}</textarea>
        </div>

        <div class="field">
            <label for="content">Konten Teks (untuk mode baca teks, opsional)</label>
            <textarea id="content" name="content" class="textarea" style="min-height:200px;" placeholder="Tulis isi buku sebagai teks biasa, pisahkan paragraf dengan baris kosong. Tiap ±750 karakter menjadi satu halaman.">{{ old('content', $book?->content) }}</textarea>
        </div>

        <div class="admin-card__grid">
            <div class="field">
                <label>Kategori</label>
                <div style="display:flex;flex-wrap:wrap;gap:8px;">
                    @foreach ($categories as $category)
                        <label class="chip" style="cursor:pointer;">
                            <input type="checkbox" name="categories[]" value="{{ $category->id }}" @checked($book && $book->categories->contains($category->id)) style="display:none;">
                            <i data-lucide="{{ $category->iconName() }}" aria-hidden="true"></i> {{ $category->name }}
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="field">
                <label>Series</label>
                <div style="display:flex;flex-wrap:wrap;gap:8px;">
                    @foreach ($series as $s)
                        <label class="chip" style="cursor:pointer;">
                            <input type="checkbox" name="series[]" value="{{ $s->id }}" @checked($book && $book->seriesList->contains($s->id)) style="display:none;">
                            <i data-lucide="book-open" aria-hidden="true"></i> {{ $s->name }}
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <div style="display:flex;gap:var(--sp-5);margin-top:var(--sp-4);">
            <label class="chip" style="cursor:pointer;">
                <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $book?->is_published ?? true)) style="display:none;">
                <i data-lucide="circle-check" aria-hidden="true"></i> Published
            </label>
            <label class="chip" style="cursor:pointer;">
                <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $book?->is_featured)) style="display:none;">
                <i data-lucide="star" aria-hidden="true"></i> Unggulan
            </label>
        </div>

        <button type="submit" class="btn btn--primary btn--lg" style="margin-top:var(--sp-6);"><i data-lucide="save" aria-hidden="true"></i> Simpan Buku</button>
    </form>
</div>

<script>
    function previewCover(input) {
        const file = input.files?.[0];
        if (!file) return;
        const box = document.getElementById('cover-preview');
        box.innerHTML = '';
        const wrap = document.createElement('div');
        wrap.style.cssText = 'position:relative;display:inline-block;';
        const img = document.createElement('img');
        img.src = URL.createObjectURL(file);
        img.alt = 'Preview cover';
        img.style.cssText = 'width:96px;aspect-ratio:3/4;object-fit:cover;border-radius:10px;box-shadow:var(--shadow-soft);';
        const span = document.createElement('span');
        span.textContent = 'Preview baru';
        span.style.cssText = 'position:absolute;bottom:6px;left:0;right:0;text-align:center;font-size:.68rem;color:#fff;background:rgba(13,21,30,.6);padding:2px 6px;border-radius:6px;';
        wrap.append(img, span);
        box.appendChild(wrap);
    }
</script>

@endsection
