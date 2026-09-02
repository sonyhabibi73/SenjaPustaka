@extends('layouts.app')

@section('title', 'Syarat & Ketentuan')
@section('page', 'terms')

@section('content')

<section class="page-hero">
    <div class="container page-hero__inner">
        <p class="eyebrow">Syarat &amp; Ketentuan</p>
        <h1><i data-lucide="scroll-text" aria-hidden="true"></i> Syarat &amp; Ketentuan</h1>
        <p>Terakhir diperbarui: {{ now()->translatedFormat('d F Y') }}</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="legal reveal">
            <p class="eyebrow">Aturan Main</p>
            <h2 style="font-size:1.8rem;">Dengan menggunakan SenjaPustaka, kamu menyetujui ketentuan berikut</h2>

            <h2><i data-lucide="user-check" aria-hidden="true"></i> Akun</h2>
            <ul>
                <li>Kamu bertanggung jawab menjaga kerahasiaan kata sandi akunmu.</li>
                <li>Setiap orang hanya boleh memiliki satu akun. Informasi pendaftaran (nama &amp; email) harus akurat.</li>
                <li>Kami dapat menangguhkan akun yang terindikasi disalahgunakan.</li>
            </ul>

            <h2><i data-lucide="book-open" aria-hidden="true"></i> Konten Platform</h2>
            <p>
                Buku dan materi yang tersedia di SenjaPustaka dikelola oleh pengelola situs.
                Hak cipta atas setiap karya tetap milik pemegang hak masing-masing.
            </p>

            <h2><i data-lucide="message-square" aria-hidden="true"></i> Konten Pengguna (Ulasan)</h2>
            <ul>
                <li>Ulasan yang kamu tulis menjadi tanggung jawabmu.</li>
                <li>Dilarang menulis ulasan yang mengandung konten ilegal, ujaran kebencian, SARA, spam, atau konten dewasa.</li>
                <li>Pengelola berhak menghapus ulasan yang melanggar ketentuan ini.</li>
            </ul>

            <h2><i data-lucide="ban" aria-hidden="true"></i> Perilaku yang Dilarang</h2>
            <ul>
                <li>Mencoba merusak, membebani, atau mengganggu kelangsungan layanan (termasuk akses otomatis/scraping massal).</li>
                <li>Mengakses akun orang lain tanpa izin.</li>
                <li>Mengunggah berkas berbahaya atau menyalahgunakan fitur unggah.</li>
            </ul>

            <h2><i data-lucide="award" aria-hidden="true"></i> Poin, Level &amp; Badge</h2>
            <p>
                Poin, level, streak, dan badge adalah bentuk apresiasi membaca. Semuanya bersifat
                insentif dan tidak dapat ditukar dengan uang, barang, atau imbalan lain.
            </p>

            <h2><i data-lucide="refresh-cw" aria-hidden="true"></i> Perubahan Layanan &amp; Ketentuan</h2>
            <p>
                Fitur dan ketentuan dapat berubah seiring perkembangan layanan. Perubahan pada
                ketentuan ini akan diumumkan di halaman ini.
            </p>

            <h2><i data-lucide="info" aria-hidden="true"></i> Batasan Tanggung Jawab</h2>
            <p>
                Layanan disediakan <em>"sebagaimana adanya"</em> (as-is). Kami berusaha menjaga
                ketersediaan dan keamanan layanan, tetapi tidak menjamin layanan bebas gangguan.
            </p>

            <h2><i data-lucide="scale" aria-hidden="true"></i> Hukum yang Berlaku</h2>
            <p>
                Ketentuan ini diatur berdasarkan hukum Republik Indonesia. Ada pertanyaan?
                Hubungi kami melalui <a href="{{ route('contact') }}">halaman Kontak</a>.
            </p>
        </div>
    </div>
</section>

@endsection
