<?php

namespace App\Console\Commands;

use App\Models\Book;
use App\Services\FileStorage;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Cache;
use ZipArchive;

/**
 * Ekstrak halaman CBZ menjadi berkas terpisah.
 *
 * Cloud object storage (R2) tidak bisa membuka ZIP dari jauh — ZipArchive
 * butuh file lokal. Jadi untuk disk cloud, halaman CBZ diekstrak sekali di
 * sini lalu disimpan sebagai gambar terpisah + manifest pages.json, sehingga
 * reader cukup redirect ke gambar itu.
 *
 * Jalankan sekali setelah buku CBZ diunggah ke R2:
 *   php artisan catalog:extract-cbz
 *   php artisan catalog:extract-cbz --book=slug-buku
 */
class ExtractCbzPages extends Command
{
    protected $signature = 'catalog:extract-cbz
        {--book= : hanya ekstrak buku dengan slug tertentu}
        {--force : ekstrak ulang walau manifest sudah ada}';

    protected $description = 'Ekstrak halaman CBZ menjadi berkas gambar terpisah (untuk disk cloud)';

    public function handle(): int
    {
        $disk = FileStorage::books();
        $target = FileStorage::booksDisk();
        $remote = FileStorage::isRemote();

        $books = Book::query()
            ->whereNotNull('file_path')
            ->whereLike('file_path', '%.cbz')
            ->when($this->option('book'), fn ($q) => $q->where('slug', $this->option('book')))
            ->get();

        if ($books->isEmpty()) {
            $this->warn('Tidak ada buku CBZ.');

            return self::SUCCESS;
        }

        $this->info("Disk buku: {$target}".($remote ? ' (cloud — akan diekstrak)' : ' (lokal)'));
        $this->newLine();

        foreach ($books as $book) {
            $source = $this->localSource($book->file_path);

            if ($source === null) {
                $this->warn("  ✗ {$book->slug}: file CBZ lokal tidak ditemukan, lewati.");

                continue;
            }

            $manifest = 'books/'.$book->slug.'/pages.json';

            if (! $this->option('force') && $disk->exists($manifest)) {
                $count = count($this->readManifest($disk->get($manifest)));
                $this->line("  · {$book->slug}: sudah ada manifest ({$count} halaman), lewati. --force untuk ulang.");

                continue;
            }

            $names = $this->extract($source, $book->slug, $disk);

            if ($names === []) {
                $this->error("  ✗ {$book->slug}: tidak ada gambar di dalam CBZ.");

                continue;
            }

            $disk->put($manifest, (string) json_encode($names));

            $book->pages = count($names);
            $book->saveQuietly();

            Cache::forget('cbz-manifest:'.$book->id.':'.$book->file_path);

            $this->info("  ✓ {$book->slug}: {$book->pages} halaman ditulis ke {$target} + manifest");
        }

        $this->newLine();
        $this->info('Selesai.');

        return self::SUCCESS;
    }

    /**
     * Patch file CBZ di disk lokal (bukan cloud) untuk dibaca ZipArchive.
     */
    private function localSource(string $filePath): ?string
    {
        // Disk cloud tidak punya path lokal; andalkan file lokal sebagai sumber.
        $local = storage_path('app/public/'.$filePath);

        if (is_file($local)) {
            return $local;
        }

        // Coba path lokal dari disk aktif kalau books_disk = local.
        $full = FileStorage::books()->path($filePath);

        return is_file($full) ? $full : null;
    }

    /**
     * @return array<int, string> nama berkas halaman yang ditulis
     */
    private function extract(string $source, string $slug, Filesystem $disk): array
    {
        $zip = new ZipArchive;

        if ($zip->open($source) !== true) {
            return [];
        }

        $images = [];

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);

            if ($name === false || ! preg_match('/\.(png|jpe?g|webp)$/i', $name)) {
                continue;
            }

            $images[] = ['index' => $i, 'name' => $name];
        }

        // Urutkan berdasarkan awalan angka supaya tidak ikut urutan leksikal
        // (2.png akan mendahului 10.png).
        usort($images, fn ($a, $b) => $this->numericPrefix($a['name']) <=> $this->numericPrefix($b['name']));

        $written = [];
        $targetDir = 'books/'.$slug.'/pages/';

        foreach ($images as $position => $image) {
            $ext = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
            $out = $targetDir.sprintf('%04d', $position + 1).'.'.$ext;
            $content = $zip->getFromIndex($image['index']);

            if ($content === false || $content === '') {
                continue;
            }

            $disk->put($out, $content);
            $written[] = basename($out);
        }

        $zip->close();

        return $written;
    }

    private function numericPrefix(string $name): int
    {
        preg_match('/(\d+)/', basename($name), $m);

        return (int) ($m[1] ?? PHP_INT_MAX);
    }

    /**
     * @return array<int, string>
     */
    private function readManifest(string $json): array
    {
        $decoded = json_decode($json, true);

        return is_array($decoded) ? array_values(array_filter($decoded, 'is_string')) : [];
    }
}
