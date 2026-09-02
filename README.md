# Senja Pustaka

Senja Pustaka adalah aplikasi perpustakaan digital berbasis web untuk menemukan, mengelola, dan membaca koleksi buku. Aplikasi ini dibangun dengan Laravel 13, Blade, dan Vite.

## Fungsi utama

### Untuk pengunjung

- Menjelajahi halaman beranda dengan buku unggulan, koleksi terbaru, dan informasi platform.
- Melihat koleksi buku, detail buku, penulis, kategori, serta seri buku.
- Mencari buku dan mendapatkan saran pencarian secara langsung.
- Melihat peringkat buku dan papan peringkat pembaca.
- Membaca halaman Tentang, Kontak, Kebijakan Privasi, serta Syarat dan Ketentuan.
- Berlangganan newsletter melalui formulir berlangganan.

### Untuk pengguna terdaftar

- Mendaftar, masuk, keluar, dan memperbarui profil.
- Membaca buku melalui reader; reader mendukung berkas buku dan halaman CBZ.
- Menyimpan progres membaca agar pembacaan dapat dilanjutkan.
- Menambah atau menghapus bookmark pada buku.
- Menandai buku sebagai favorit.
- Memberi ulasan dan rating, serta menghapus ulasan milik sendiri.
- Membuat target membaca.
- Melihat dashboard berisi aktivitas dan statistik membaca.
- Melihat notifikasi, menandai semua notifikasi sebagai telah dibaca, serta melihat jumlah notifikasi yang belum dibaca.
- Mengatur status langganan newsletter dari akun.

### Untuk administrator

- Memantau ringkasan aplikasi melalui dashboard admin.
- Mengelola buku, termasuk metadata dan berkas/sampulnya.
- Mengelola kategori, penulis, penerbit, dan seri.
- Memoderasi atau menghapus ulasan.
- Mengelola pengguna, termasuk pembaruan data dan penghapusan akun.
- Mengelola pelanggan newsletter.

## Teknologi

- PHP 8.3 dan Laravel 13
- Blade untuk antarmuka server-rendered
- Vite, JavaScript, dan CSS untuk aset front-end
- SQLite atau MySQL melalui konfigurasi Laravel
- Pest untuk pengujian

## Menjalankan secara lokal

### Prasyarat

- PHP 8.3+
- Composer
- Node.js dan npm
- SQLite atau MySQL

### Instalasi

```bash
git clone <URL_REPOSITORI>
cd SenjaPustaka
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

Jalankan server pengembangan dengan:

```bash
composer run dev
```

Atau, jalankan Laravel dan Vite secara terpisah:

```bash
php artisan serve
npm run dev
```

## Pengujian dan pemeriksaan kode

```bash
composer test
npm run lint:check
npm run format:check
npm run types:check
```

## Keamanan repositori

File konfigurasi lokal dan rahasia tidak disertakan dalam Git. Ini mencakup `.env`, variasi `.env.*` (kecuali `.env.example`), sertifikat/kunci privat, berkas kredensial, dan folder `secrets/`. Dokumen internal `desain.md` juga sengaja tidak dipublikasikan.

Jangan pernah menaruh kata sandi, API key, token, atau berkas produksi di `.env.example` maupun berkas lain yang akan dikomit.

## Lisensi

Lisensi belum ditentukan. Tambahkan file `LICENSE` sebelum mendistribusikan proyek.
