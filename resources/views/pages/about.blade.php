@extends('layouts.app')

@section('title', 'Tentang Kami')
@section('page', 'about')

@section('content')

<section class="page-hero">
    <div class="container page-hero__inner">
        <p class="eyebrow">Cerita Kami</p>
        <h1><i data-lucide="sunset" aria-hidden="true"></i> Tentang SenjaPustaka</h1>
        <p>Kami percaya membaca harus terasa hangat — seperti memasuki toko buku favoritmu saat senja.</p>
    </div>
</section>

<section class="section">
    <div class="container" style="max-width:760px;">
        <div class="reveal">
            <p class="eyebrow">Visi Kami</p>
            <h2 style="font-size:1.8rem;">Mengubah perpustakaan digital menjadi tempat yang terasa hidup</h2>
            <p style="color:var(--color-muted);font-size:1.05rem;line-height:1.8;">
                SenjaPustaka lahir dari satu pertanyaan sederhana: kenapa membaca digital terasa begitu kaku?
                Kami merancang ulang semuanya — dari palet warna langit senja, tipografi serif yang dramatis,
                sampai gamifikasi yang memotivasi kamu untuk terus membaca.
            </p>
            <p style="color:var(--color-muted);font-size:1.05rem;line-height:1.8;">
                Di SenjaPustaka, setiap halaman yang kamu baca bernilai. Kumpulkan poin, buka badge,
                jaga streak harian, dan lihat dirimu naik dari Pembaca Baru sampai Dewa Baca.
            </p>
        </div>

        <div class="about-stats">
            <div class="about-stat reveal" style="--d:0ms">
                <span class="mono count-up" data-count="{{ App\Models\Book::where('is_published', true)->count() }}">{{ App\Models\Book::where('is_published', true)->count() }}</span>
                <span class="small text-muted">Buku Digital</span>
            </div>
            <div class="about-stat reveal" style="--d:80ms">
                <span class="mono count-up" data-count="{{ App\Models\User::count() }}">{{ App\Models\User::count() }}</span>
                <span class="small text-muted">Pembaca Terdaftar</span>
            </div>
            <div class="about-stat reveal" style="--d:160ms">
                <span class="mono">{{ App\Models\Badge::count() }}</span>
                <span class="small text-muted">Badge Pencapaian</span>
            </div>
            <div class="about-stat reveal" style="--d:240ms">
                <span class="mono">{{ count(App\Services\Level::LEVELS) }}</span>
                <span class="small text-muted">Level Pembaca</span>
            </div>
        </div>

        <div class="reveal" style="margin-top:var(--sp-12);">
            <p class="eyebrow">Filosofi</p>
            <h2 style="font-size:1.8rem;">"Mulai kapan pun, berhenti kapan pun — kembali persis di halaman terakhirmu."</h2>
            <p style="color:var(--color-muted);font-size:1.05rem;line-height:1.8;">
                Kami tidak mengejar jumlah, tapi kenyamanan membaca. Karena itu semua fitur kami dirancang
                untuk membuatmu betah: progres otomatis yang menyimpan halaman terakhirmu, bookmark,
                rekomendasi personal, dan komunitas pembaca yang saling memberi ulasan jujur.
            </p>
        </div>
    </div>
</section>

@endsection
