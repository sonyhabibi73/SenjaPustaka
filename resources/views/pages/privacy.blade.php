@extends('layouts.app')

@section('title', 'Kebijakan Privasi')
@section('page', 'privacy')

@section('content')

<section class="page-hero">
    <div class="container page-hero__inner">
        <p class="eyebrow">Kebijakan Privasi</p>
        <h1><i data-lucide="shield-check" aria-hidden="true"></i> Kebijakan Privasi</h1>
        <p>Terakhir diperbarui: {{ now()->translatedFormat('d F Y') }}</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="legal reveal">
            <p class="eyebrow">Ringkasan</p>
            <h2 style="font-size:1.8rem;">Data kamu, urusan kamu</h2>
            <p>
                Kebijakan ini menjelaskan data apa saja yang SenjaPustaka simpan, untuk apa data
                tersebut digunakan, dan hak kamu atas data tersebut. Kami menulisnya dengan jujur
                sesuai fitur yang benar-benar ada di situs ini — bukan template generik.
            </p>

            <h2><i data-lucide="database" aria-hidden="true"></i> Data yang Kami Kumpulkan</h2>
            <p>Saat kamu menggunakan SenjaPustaka, kami menyimpan data berikut:</p>
            <ul>
                <li><strong>Data akun</strong> — nama, alamat email, kata sandi (disimpan dalam bentuk terenkripsi/hash, tidak pernah tersimpan sebagai teks asli), bio, dan foto profil (opsional).</li>
                <li><strong>Riwayat membaca</strong> — progres halaman terakhir yang kamu baca, bookmark, buku favorit, ulasan yang kamu tulis, dan target membaca tahunanmu.</li>
                <li><strong>Data gamifikasi</strong> — jumlah poin, streak membaca harian, level, dan badge yang kamu peroleh.</li>
                <li><strong>Newsletter</strong> — alamat email, hanya jika kamu berlangganan newsletter kami.</li>
                <li><strong>Data teknis</strong> — alamat IP dan log server standar, serta data sesi login yang disimpan dalam cookie sesi.</li>
            </ul>

            <h2><i data-lucide="sparkles" aria-hidden="true"></i> Untuk Apa Data Digunakan</h2>
            <ul>
                <li>Membuat dan mengamankan akunmu (login, logout, pemulihan sesi).</li>
                <li>Menyimpan progres baca agar kamu bisa melanjutkan tepat di halaman terakhir.</li>
                <li>Menghitung poin, level, streak, dan peringkat pembaca.</li>
                <li>Memberikan rekomendasi buku berdasarkan kategori yang sering kamu baca.</li>
                <li>Mengirim newsletter mingguan, hanya jika kamu berlangganan (bisa dinonaktifkan kapan saja dari dashboard).</li>
                <li>Keamanan layanan dan pencegahan penyalahgunaan.</li>
            </ul>

            <h2><i data-lucide="cookie" aria-hidden="true"></i> Cookie &amp; Penyimpanan Lokal</h2>
            <ul>
                <li><strong>Cookie sesi</strong> — digunakan untuk menjaga kamu tetap masuk, dan otomatis kedaluwarsa sesuai pengaturan sesi.</li>
                <li><strong>localStorage</strong> — preferensi tema gelap/terang kamu disimpan di peramban, dan tidak dikirim ke server.</li>
            </ul>

            <h2><i data-lucide="handshake" aria-hidden="true"></i> Berbagi Data dengan Pihak Lain</h2>
            <p>
                Kami <strong>tidak menjual atau menyewakan</strong> data pribadimu ke pihak mana pun.
                Data kamu disimpan di server yang kami sewa dari penyedia hosting. Apabila newsletter
                diaktifkan, email dikirim melalui layanan pengiriman email yang kami gunakan.
            </p>

            <h2><i data-lucide="clock" aria-hidden="true"></i> Retensi &amp; Hak Kamu</h2>
            <ul>
                <li>Data akun disimpan selama akunmu aktif.</li>
                <li>Kamu dapat memperbarui profil dan foto melalui halaman Profil.</li>
                <li>Kamu dapat menghapus ulasanmu sendiri kapan saja.</li>
                <li>Kamu dapat berhenti berlangganan newsletter dari dashboard.</li>
                <li>Untuk penghapusan akun beserta seluruh datanya, hubungi kami melalui halaman Kontak — saat ini penghapusan akun dilakukan secara manual oleh pengelola.</li>
            </ul>

            <h2><i data-lucide="mail" aria-hidden="true"></i> Kontak</h2>
            <p>
                Ada pertanyaan tentang data kamu? Kirim pesan melalui
                <a href="{{ route('contact') }}">halaman Kontak</a> dan kami akan meresponsnya.
            </p>
        </div>
    </div>
</section>

@endsection
