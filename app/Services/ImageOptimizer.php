<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class ImageOptimizer
{
    /**
     * Konversi gambar yang tersimpan di disk 'public' menjadi WebP (dengan resize bila perlu).
     *
     * @return string|null path file WebP baru, atau null jika konversi gagal / tidak didukung.
     */
    public static function toWebp(string $storedPath, int $maxDimension = 900, int $quality = 80): ?string
    {
        if (! function_exists('imagewebp')) {
            return null;
        }

        $full = Storage::disk('public')->path($storedPath);

        if (! is_file($full)) {
            return null;
        }

        $info = @getimagesize($full);

        if ($info === false) {
            return null;
        }

        // Lindungi memori: gambar berdimensi sangat besar tidak didecode (decode penuh bisa makan ratusan MB).
        if ($info[0] > 8000) {
            return null;
        }

        $src = match ($info[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($full),
            IMAGETYPE_PNG => @imagecreatefrompng($full),
            IMAGETYPE_WEBP => @imagecreatefromwebp($full),
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

        $dir = dirname($storedPath);
        $newPath = $dir.'/'.pathinfo($storedPath, PATHINFO_FILENAME).'.webp';
        $tmp = tempnam(sys_get_temp_dir(), 'webp');

        if (imagewebp($src, $tmp, $quality) && is_file($tmp)) {
            Storage::disk('public')->put($newPath, (string) file_get_contents($tmp));
            imagedestroy($src);
            @unlink($tmp);

            return $newPath;
        }

        imagedestroy($src);
        @unlink($tmp);

        return null;
    }
}
