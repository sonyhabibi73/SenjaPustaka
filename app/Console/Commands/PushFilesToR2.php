<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Unggah isi disk lokal ke disk cloud (R2).
 *
 * Dipakai sekali saat pertama kali menyiapkan deployment: buku (436 MB) dan
 * sampul ada di storage/app/public, sementara disk hosting ephemeral. Perintah
 * ini memindahkan salinannya ke bucket R2 dengan nama path yang sama persis,
 * jadi kolom file_path / cover_image di database tidak perlu diubah.
 *
 * Contoh:
 *   BOOKS_DISK=r2 UPLOADS_DISK=r2 php artisan catalog:push-to-r2
 *   php artisan catalog:push-to-r2 --only=books
 *   php artisan catalog:push-to-r2 --dry-run
 */
class PushFilesToR2 extends Command
{
    protected $signature = 'catalog:push-to-r2
        {--only= : books | uploads | semua (default semua)}
        {--dry-run : hanya menghitung tanpa mengirim}';

    protected $description = 'Unggah berkas buku dan sampul dari disk lokal ke disk R2';

    public function handle(): int
    {
        // Konfigurasi dicek lebih dulu, sebelum disk cloud instantiated:
        // tanpa bucket, Flysystem akan melempar TypeError saat dibangun.
        if (blank(config('filesystems.disks.r2.bucket'))) {
            $this->error('R2_BUCKET belum diisi di .env.');
            $this->line('  Isi R2_BUCKET, R2_ENDPOINT, R2_ACCESS_KEY_ID, R2_SECRET_ACCESS_KEY dulu.');

            return self::FAILURE;
        }

        $local = Storage::disk('public');
        $target = Storage::disk('r2');

        $only = strtolower((string) $this->option('only'));

        $jobs = [];

        if (in_array($only, ['', 'books', 'semua'], true)) {
            $jobs['buku'] = [$local, 'books', 'books'];
        }

        if (in_array($only, ['', 'uploads', 'covers', 'semua'], true)) {
            $jobs['sampul'] = [$local, 'covers', 'covers'];
            $jobs['avatar'] = [$local, 'avatars', 'avatars'];
        }

        $dry = (bool) $this->option('dry-run');
        $total = 0;
        $bytes = 0;

        foreach ($jobs as $label => [$disk, $prefix, $targetPrefix]) {
            $files = $disk->allFiles($prefix);
            $this->info("{$label}: ".count($files)." berkas di storage/app/public/{$prefix}");

            foreach ($files as $file) {
                $size = $disk->size($file);
                $total++;
                $bytes += $size;

                if ($dry) {
                    $this->line(sprintf('    [dry-run] %s — %s', $file, $this->human($size)));

                    continue;
                }

                $ok = $target->put($file, $disk->get($file));

                if (! $ok) {
                    $this->error("    ✗ gagal: {$file}");

                    return self::FAILURE;
                }

                $this->line(sprintf('    ✓ %s — %s', $file, $this->human($size)));
            }
        }

        $this->newLine();
        $this->info(sprintf(
            '%s: %d berkas, %s total',
            $dry ? 'Akan dikirim' : 'Terkirim',
            $total,
            $this->human($bytes)
        ));

        if (! $dry) {
            $this->line('Lanjut: php artisan catalog:extract-cbz  (untuk buku CBZ)');
        }

        return self::SUCCESS;
    }

    private function human(int $bytes): string
    {
        foreach (['B', 'KB', 'MB', 'GB'] as $unit) {
            if ($bytes < 1024) {
                return round($bytes, 1).' '.$unit;
            }

            $bytes /= 1024;
        }

        return round($bytes, 1).' TB';
    }
}
