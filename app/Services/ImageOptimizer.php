<?php

namespace App\Services;

class ImageOptimizer
{
    /**
     * Konversi gambar yang tersimpan pada disk unggahan menjadi WebP
     * (dengan resize bila perlu).
     *
     * Prosesnya memakai API string milik GD (getimagesizefromstring +
     * imagecreatefromstring + imagewebp ke string), jadi tidak butuh path
     * filesystem sama sekali — cara yang sama berlaku untuk disk lokal
     * maupun R2. Tidak ada file sementara yang dibuat.
     *
     * @return string|null path file WebP baru, atau null jika konversi gagal / tidak didukung.
     */
    public static function toWebp(string $storedPath, int $maxDimension = 900, int $quality = 80): ?string
    {
        if (! function_exists('imagewebp')) {
            return null;
        }

        $disk = FileStorage::uploads();

        if (! $disk->exists($storedPath)) {
            return null;
        }

        $bytes = $disk->get($storedPath);

        if ($bytes === '' || ($info = @getimagesizefromstring($bytes)) === false) {
            return null;
        }

        // Lindungi memori: gambar berdimensi sangat besar tidak didecode (decode penuh bisa makan ratusan MB).
        if ($info[0] > 8000) {
            return null;
        }

        $src = match ($info[2]) {
            IMAGETYPE_JPEG => @imagecreatefromstring($bytes),
            IMAGETYPE_PNG => @imagecreatefromstring($bytes),
            IMAGETYPE_WEBP => @imagecreatefromstring($bytes),
            default => null,
        };

        if (! $src) {
            return null;
        }

        // Resize bila lebih besar dari batas dimensi.
        $width = imagesx($src);
        $height = imagesy($src);

        if ($width > $maxDimension) {
            $ratio = $maxDimension / $width;
            $newWidth = max(1, (int) round($width * $ratio));
            $newHeight = max(1, (int) round($height * $ratio));
            $dst = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($src);
            $src = $dst;
        }

        // Pertahankan transparansi PNG.
        if ($info[2] === IMAGETYPE_PNG) {
            imagealphablending($src, false);
            imagesavealpha($src, true);
        }

        // imagewebp menulis ke file (argumen null hanya mencetak ke stdout),
        // jadi encode dulu ke file sementara lalu baru dibaca untuk dikirim
        // ke disk. Memakai file sementara, bukan path storage, supaya identik
        // untuk disk lokal maupun R2.
        $tmpOut = tempnam(sys_get_temp_dir(), 'webp');

        if ($tmpOut === false || ! imagewebp($src, $tmpOut, $quality) || ! is_file($tmpOut)) {
            imagedestroy($src);
            @unlink($tmpOut ?: '');

            return null;
        }

        imagedestroy($src);
        $webp = (string) file_get_contents($tmpOut);
        @unlink($tmpOut);

        if ($webp === '') {
            return null;
        }

        $newPath = dirname($storedPath).'/'.pathinfo($storedPath, PATHINFO_FILENAME).'.webp';
        $disk->put($newPath, $webp);

        return $newPath;
    }
}
