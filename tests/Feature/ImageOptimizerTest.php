<?php

use App\Services\ImageOptimizer;
use Illuminate\Support\Facades\Storage;

function makePng(int $w, int $h, int $r = 200, int $g = 100, int $b = 50): string
{
    $img = imagecreatetruecolor($w, $h);
    $fill = imagecolorallocate($img, $r, $g, $b);
    imagefill($img, 0, 0, $fill);
    ob_start();
    imagepng($img);
    $png = (string) ob_get_clean();
    imagedestroy($img);

    return $png;
}

test('toWebp mengubah PNG menjadi WebP', function () {
    Storage::fake('public');

    Storage::disk('public')->put('covers/test.png', makePng(100, 150));

    $webp = ImageOptimizer::toWebp('covers/test.png');

    expect($webp)->not->toBeNull()
        ->and($webp)->toEndWith('.webp');

    Storage::disk('public')->assertExists($webp);
})->skip(! function_exists('imagepng'), 'Ekstensi GD tidak tersedia.');

test('toWebp meresize gambar yang lebih besar dari batas', function () {
    Storage::fake('public');

    Storage::disk('public')->put('avatars/big.png', makePng(2000, 1000, 100, 150, 200));

    $webp = ImageOptimizer::toWebp('avatars/big.png', 256);

    expect($webp)->not->toBeNull();

    $size = getimagesize(Storage::disk('public')->path($webp));
    expect($size[0])->toBeLessThanOrEqual(256);
})->skip(! function_exists('imagepng'), 'Ekstensi GD tidak tersedia.');

test('toWebp mengembalikan null untuk file non-gambar', function () {
    Storage::fake('public');

    Storage::disk('public')->put('covers/bukan-gambar.txt', 'teks biasa');

    expect(ImageOptimizer::toWebp('covers/bukan-gambar.txt'))->toBeNull();
});
