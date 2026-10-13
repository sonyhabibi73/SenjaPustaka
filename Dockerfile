# Image produksi untuk SenjaPustaka (deploy ke Render, plan free).
#
# Build tiga tahap:
#   1. assets  — Node/Vite membangun public/build
#   2. vendor  — Composer memasang dependensi tanpa dev
#   3. runtime — FrankenPHP (PHP-FPM + Caddy dalam satu gambar), serves public/
#
# Rahasia dan APP_KEY tidak pernah ikut ter-copy: semuanya lewat environment
# variable Render. Berkas buku & sampul juga tidak ikut (436 MB) — semuanya
# diambil dari Cloudflare R2 saat runtime.
#
# Development lokal tidak memakai image ini: `php artisan serve` + Vite dev
# server seperti biasa.

# ── 1. Aset statis ──────────────────────────────────────────────────────────
FROM node:22-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY vite.config.ts ./
COPY resources/ ./resources/
RUN npm run build

# ── 2. Dependensi PHP ───────────────────────────────────────────────────────
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

# ── 3. Runtime ──────────────────────────────────────────────────────────────
FROM dunglas/frankenphp:php8.3-bookworm AS runtime

# pdo_pgsql → Neon · zip → baca CBZ · gd → WebP · pdo_sqlite → test lokal
RUN install-php-extensions \
    pdo_pgsql \
    pdo_sqlite \
    zip \
    gd \
    intl \
    mbstring \
    bcmath \
    opcache

WORKDIR /app

COPY --from=vendor /app/vendor ./vendor
COPY . .

# public/build dibangun di tahap 1; public/hot hanya untuk `vite dev` dan
# kalau ikut ter-copy akan membuat Laravel & CSP menunjuk origin Vite yang
# tidak ada di server.
COPY --from=assets /app/public/build ./public/build
RUN rm -f public/hot

# HOST, APP_URL, dan kunci R2 disuntikkan lewat environment variable Render,
# bukan ditulis ke image.
#
# Direktori storage/framework/* sengaja dikosongkan di .dockerignore, jadi harus
# dibuat ulang di sini: Laravel gagal start kalau cache/sessions/views tidak ada
# atau tidak bisa ditulis.
#
# package:discover dijalankan di dalam image (composer install sengaja memakai
# --no-scripts karena script-nya butuh aplikasi lengkap) supaya
# bootstrap/cache/packages.php ikut ter-build, bukan ditulis saat request pertama.
RUN rm -f public/hot .env .env.* \
    && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && php artisan package:discover --ansi

# Render menyuntikkan PORT (bawaan 10000); Caddyfile membacanya dari sana.
# Nilai 8000 hanya untuk menjalankan image ini langsung di lokal.
EXPOSE 8000

CMD ["frankenphp", "run", "--config", "/app/Caddyfile"]