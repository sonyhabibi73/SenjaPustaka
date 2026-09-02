# 🚀 Panduan Deployment SenjaPustaka

Checklist lengkap untuk go-live. Baca sesuai skenario hosting kamu (shared hosting, VPS, atau PaaS).
Bagian **1–4** wajib di semua skenario.

---

## 1. Kebutuhan Server (Requirements)

| Kebutuhan | Versi |
|---|---|
| **PHP** | **8.3 atau lebih baru** (direkomendasikan 8.3 / 8.4) |
| **Composer** | 2.x (untuk install dependency) |
| **Node.js + npm** | 18+ (untuk build asset CSS/JS — bisa dilakukan di lokal) |
| **Database** | SQLite (default) atau MySQL 5.7+ / MariaDB 10.3+ |
| **Web server** | Apache (sudah ada `.htaccess`) atau Nginx |

### Ekstensi PHP yang wajib ada
Jalankan `php -m` di server dan pastikan ada:

| Ekstensi | Untuk apa | Wajib? |
|---|---|---|
| `pdo_mysql` **atau** `pdo_sqlite` | koneksi database | ✅ wajib (sesuai DB) |
| `mbstring` | teks UTF-8 (konten buku Indonesia) | ✅ wajib |
| `fileinfo` | deteksi tipe file upload | ✅ wajib |
| `openssl` | enkripsi sesi & hash | ✅ wajib |
| `tokenizer`, `ctype`, `json`, `xml` | runtime Laravel | ✅ wajib |
| `zip` | **membaca buku CBZ** (fitur reader) | ✅ wajib |
| `curl` | HTTP client / integrasi email | ✅ wajib |
| `gd` **atau** `imagick` | konversi cover/avatar ke WebP | ⭐ disarankan |
| `intl` | format tanggal/locale | ⭐ disarankan |
| `bcmath` | perhitungan poin | opsional |

> 💡 Di cPanel: pilih versi PHP lewat **MultiPHP Manager** dan centang ekstensi di **Select PHP Version → Extensions**.

---

## 2. Siapkan File `.env` di Server

1. Salin template: `cp .env.example .env`
2. Edit nilai penting berikut:

```env
APP_NAME=SenjaPustaka
APP_ENV=production
APP_KEY=            # Wajib diisi — jangan kosong!
APP_DEBUG=false     # WAJIB false di production
APP_URL=https://domainkamu.com

# Database (pilih salah satu)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database
DB_USERNAME=user_database
DB_PASSWORD=password_kuat

SESSION_DRIVER=database   # sudah default, aman
SESSION_SECURE_COOKIE=true   # WAJIB true kalau pakai HTTPS
SESSION_LIFETIME=120

MAIL_MAILER=smtp          # ganti dari 'log' → smtp agar form kontak benar-benar terkirim
MAIL_HOST=smtp.domainmu.com
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS=halo@domainkamu.com
MAIL_FROM_NAME="${APP_NAME}"
```

3. Generate kunci aplikasi (sekali saja):
```bash
php artisan key:generate
```

> ⚠️ **Jangan pernah meng-commit file `.env`** — sudah di-exclude di `.gitignore`.
> ⚠️ `APP_DEBUG=false` membuat halaman error tidak menampilkan detail teknis ke pengunjung (detail tetap masuk `storage/logs`).

---

## 3. Migrasi Database dari Lokal ke Server

### Opsi A — SQLite (paling mudah)
1. Di lokal, pastikan data sudah final.
2. Salin file `database/database.sqlite` ke server (folder `database/`).
3. Di server, jalankan `php artisan migrate` (untuk tabel yang mungkin belum ada).

### Opsi B — MySQL/MariaDB
1. **Tanpa data lama** (mulai kosong): cukup jalankan di server
   ```bash
   php artisan migrate --force
   php artisan db:seed --force   # opsional, untuk data contoh admin/demo
   ```
2. **Dengan data lokal**: export dari lokal, import ke server
   ```bash
   # Di lokal
   mysqldump -u root -p nama_database > senja_backup.sql
   # Di server
   mysql -u user_database -p nama_database < senja_backup.sql
   ```

> Akun admin & user demo dibuat oleh seeder (email `admin@senjapustaka.test` / `user@senjapustaka.test`).
> **Ganti password akun demo segera setelah go-live** (via panel admin → Pengguna, atau SQL).

---

## 4. Perintah Setelah Upload (semua skenario)

```bash
# 1. Install dependency PHP (tanpa dev tools)
composer install --no-dev --optimize-autoloader

# 2. Build asset frontend (jalankan di lokal ATAU di server)
npm ci
npm run build

# 3. Link storage (agar /storage bisa diakses publik)
php artisan storage:link

# 4. Izinkan folder tulis-menulis
chmod -R 775 storage bootstrap/cache

# 5. Cache konfigurasi (percepatan + aman)
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Jalankan worker antrian (untuk email & job)
php artisan queue:work --daemon
# (opsional) jadwalkan scheduler via cron setiap menit:
# * * * * * cd /path/proyek && php artisan schedule:run >> /dev/null 2>&1
```

---

## 5. Skenario Hosting

### A. Shared Hosting (cPanel) — paling mudah, tanpa SSH penuh
1. Upload isi proyek ke `public_html/` (jika ada SSH, jalankan langkah 4 di atas).
2. **Tanpa SSH**: buat zip di lokal berisi `vendor/` (hasil `composer install --no-dev`) dan `public/build/` (hasil `npm run build`), lalu upload.
3. Pindahkan isi folder **`public/`** ke `public_html/`, dan sisanya (`app/`, `config/`, `database/`, dst.) ke folder di atas `public_html` (mis. `~/senjapustaka/`). Edit `public/index.php` jika path framework berbeda:
   ```php
   require __DIR__.'/../vendor/autoload.php';        // sesuaikan path
   $app = require_once __DIR__.'/../bootstrap/app.php'; // sesuaikan path
   ```
4. Buat symlink storage di panel **Terminal** (kalau tersedia) atau minta bantuan support:
   ```bash
   ln -s /home/user/senjapustaka/storage/app/public /home/user/public_html/storage
   ```
5. Pilih PHP 8.3+ & aktifkan ekstensi lewat MultiPHP Manager.
6. Buat database via **MySQL Databases**, isi di `.env`.

### B. VPS (Nginx atau Apache) — kendali penuh
Contoh konfigurasi **Nginx** (`/etc/nginx/sites-available/domainkamu.com`):

```nginx
server {
    listen 80;
    server_name domainkamu.com;
    root /var/www/senjapustaka/public;

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    location ~* \.(jpg|jpeg|png|webp|gif|svg|css|js|woff2?)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
}
```

Lalu:
```bash
sudo certbot --nginx -d domainkamu.com   # HTTPS gratis
```
Untuk Apache, `.htaccess` bawaan Laravel sudah siap dipakai.

> 🔒 Jangan lupa: folder **di luar** `public/` (termasuk `.env`, `storage/`) tidak boleh diakses publik.

### C. PaaS (Railway / Render / Heroku-style) — paling simpel
1. Repository di-push ke Git, hubungkan ke platform.
2. Set **Environment Variables** (isi `.env` di dashboard platform, termasuk `APP_KEY` hasil `php artisan key:generate`).
3. **Build command**: `composer install --no-dev --optimize-autoloader && npm ci && npm run build && php artisan storage:link`
4. **Start command**: `php artisan serve --host=0.0.0.0 --port=$PORT` (Railway/Render menyuntikkan `$PORT`), atau gunakan `php-fpm` + Nginx buildpack.
5. **Persistent disk**: `storage/app/public` wajib dipasang ke volume persist (kalau tidak, file upload hilang saat redeploy).
6. Jalankan `php artisan migrate --force` sekali setelah deploy (atau lewat post-deploy hook).
7. Worker terpisah untuk `php artisan queue:work` (email/newsletter).

---

## 6. Checklist Sebelum Go-Live 🎯

- [ ] `APP_DEBUG=false` di `.env` server
- [ ] `APP_KEY` terisi (bukan kosong)
- [ ] `APP_URL` = domain asli (https)
- [ ] `SESSION_SECURE_COOKIE=true` (kalau HTTPS)
- [ ] Database migrasi sukses & akun demo sudah diganti passwordnya
- [ ] `php artisan storage:link` sudah dijalankan (cover/avatar tampil)
- [ ] `composer install --no-dev` (tanpa tool development)
- [ ] Asset `public/build/` ter-build (bukan mode dev)
- [ ] Test upload cover & CBZ di server
- [ ] Test form kontak benar-benar mengirim email (mailer bukan `log`)
- [ ] Test halaman 404/500 tampil sesuai tema (bukan error mentah)
- [ ] `chmod 775` pada `storage/` & `bootstrap/cache`
- [ ] Backup database pertama (dan jadwalkan backup rutin)
- [ ] HTTPS aktif (SSL) — wajib kalau ada login

---

## 7. Masalah Umum

| Gejala | Penyebab & Solusi |
|---|---|
| Halaman putih/error 500 | `APP_DEBUG=false` menyembunyikan detail — cek `storage/logs/laravel.log` |
| Cover/avatar tidak tampil | `storage:link` belum dijalankan |
| Upload CBZ gagal | Ekstensi `zip` PHP belum aktif |
| Form kontak "berhasil" tapi tak ada email | `MAIL_MAILER=log` — pesan cuma ditulis ke log; ganti ke `smtp` |
| Login selalu gagal / session hilang | `APP_KEY` beda dengan saat migrate, atau `SESSION_DRIVER` salah |
| Gambar tidak ter-konversi WebP | Ekstensi `gd`/`imagick` belum aktif — situs tetap berfungsi, hanya gambar lebih berat |
| Error 500 saat upload gambar besar | `memory_limit` PHP terlalu rendah untuk proses decode+konversi GD — set minimal **128M** di `php.ini` |
