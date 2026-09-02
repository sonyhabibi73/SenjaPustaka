@extends('layouts.reader')

@section('title', $book->title)

@section('reader-content')

<div class="reader" id="reader-app" data-book-id="{{ $book->id }}" data-mode="{{ $mode }}" data-pages="{{ $book->pages }}" data-start-page="{{ $startPage }}">

    <div class="reader-progress">
        <div class="reader-progress__bar" id="reader-progress-fill" style="width:{{ $mode === 'pdf' ? '0%' : ($startPage / max(1, $book->pages) * 100) . '%' }}"></div>
    </div>

    <div class="reader-toolbar" id="reader-toolbar">
        <a href="{{ route('books.show', $book) }}" class="icon-btn" aria-label="Kembali">←</a>
        <span class="reader-toolbar__title">{{ $book->title }}</span>

        @if ($mode === 'text')
            <button type="button" class="icon-btn" id="reader-zoom-out" aria-label="Perkecil teks">−</button>
            <button type="button" class="icon-btn" id="reader-zoom-in" aria-label="Perbesar teks">＋</button>
        @elseif ($mode === 'pdf')
            <div class="reader-zoom" role="group" aria-label="Perbesaran dokumen">
                <button type="button" class="icon-btn" id="pdf-zoom-out" aria-label="Perkecil dokumen">−</button>
                <button type="button" class="zoom-level" id="pdf-zoom-level" title="Klik untuk reset ke 100%">100%</button>
                <button type="button" class="icon-btn" id="pdf-zoom-in" aria-label="Perbesar dokumen">＋</button>
            </div>
            <button type="button" class="icon-btn" id="pdf-dark" aria-label="Halaman gelap"><i data-lucide="moon" aria-hidden="true"></i></button>
        @endif

        <button
            type="button"
            class="icon-btn {{ $bookmark ? 'is-active' : '' }}"
            id="reader-bookmark"
            aria-label="{{ $bookmark ? 'Hapus bookmark' : 'Tambahkan bookmark' }}"
        ><i data-lucide="bookmark" aria-hidden="true"></i></button>
        <button type="button" class="icon-btn" id="reader-fullscreen" aria-label="Layar penuh"><i data-lucide="maximize" aria-hidden="true"></i></button>
        <button type="button" class="icon-btn" id="reader-theme" aria-label="Ganti tema"><i data-lucide="moon" aria-hidden="true"></i></button>
        <button type="button" class="icon-btn" id="reader-help" aria-label="Bantuan">?</button>
    </div>

    <div class="reader-content {{ $mode === 'pdf' ? 'reader-content--pdf' : '' }}">

        @if ($mode === 'pdf')
            <div class="reader-pdf" id="pdf-viewer" data-url="{{ $pdfUrl }}">
                <div class="pdf-loading" id="pdf-loading" role="status" aria-live="polite">
                    <span class="pdf-spinner"></span>
                    <p>Memuat dokumen…</p>
                </div>
                <p class="pdf-error" id="pdf-error" hidden>
                    Dokumen tidak bisa dimuat.
                    <a href="{{ $pdfUrl }}" target="_blank" rel="noopener">Buka dokumen langsung</a>
                    · <a href="{{ route('books.show', $book) }}">Kembali ke detail buku</a>
                </p>
            </div>

        @elseif ($mode === 'cbz')
            @for ($i = 1; $i <= $cbzPages; $i++)
                <img
                    src="{{ route('reader.cbz', [$book, $i]) }}"
                    alt="Halaman {{ $i }}"
                    class="reader-cbz-img"
                    data-page="{{ $i }}"
                    loading="lazy"
                    @if ($i !== $startPage) hidden @endif
                >
            @endfor

        @else
            <div class="reader-text">
                @foreach ($pages as $index => $page)
                    <div class="reader-page" data-page="{{ $index + 1 }}" @if (($index + 1) !== $startPage) hidden @endif>
                        {!! nl2br(e($page)) !!}
                    </div>
                @endforeach
            </div>
        @endif

        <div class="reader-nav">
            <span class="reader-nav__page" id="reader-page-label">{{ $startPage }} / {{ $book->pages }}</span>
            <div class="reader-nav__btns">
                <button type="button" class="btn btn--ghost" id="reader-prev" {{ $startPage <= 1 ? 'disabled' : '' }}>← Sebelumnya</button>
                <button type="button" class="btn btn--primary" id="reader-next" {{ $startPage >= $book->pages ? 'disabled' : '' }}>Berikutnya →</button>
            </div>
        </div>
    </div>

    <div class="help-modal" id="help-modal">
        <div class="help-modal__panel">
            <h3><i data-lucide="keyboard" aria-hidden="true"></i> Pintasan Keyboard</h3>

            @if ($mode === 'pdf')
                <div class="help-row"><span>Halaman sebelumnya / berikutnya</span><span><kbd>←</kbd> <kbd>→</kbd></span></div>
                <div class="help-row"><span>Gulir halaman</span><span><kbd>Spasi</kbd> <kbd>PgDn</kbd></span></div>
                <div class="help-row"><span>Perbesar / perkecil</span><span><kbd>+</kbd> <kbd>−</kbd></span></div>
                <div class="help-row"><span>Reset zoom</span><kbd>0</kbd></div>
                <div class="help-row"><span>Halaman gelap / terang</span><kbd>D</kbd></div>
            @else
                <div class="help-row"><span>Halaman sebelumnya</span><kbd>←</kbd></div>
                <div class="help-row"><span>Halaman berikutnya</span><span><kbd>→</kbd> <kbd>Spasi</kbd></span></div>
            @endif

            <div class="help-row"><span>Layar penuh</span><kbd>F</kbd></div>
            <div class="help-row"><span>Bookmark halaman ini</span><kbd>B</kbd></div>
            <div class="help-row"><span>Bantuan</span><kbd>?</kbd></div>
            <div class="help-row"><span>Tutup bantuan</span><kbd>Esc</kbd></div>
            <button type="button" class="btn btn--primary help-modal__close" id="help-close">Mengerti!</button>
        </div>
    </div>
</div>

@endsection
