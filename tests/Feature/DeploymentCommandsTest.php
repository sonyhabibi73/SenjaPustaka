<?php

use App\Models\Book;
use App\Services\FileStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

/**
 * Perintah deployment: ekstraksi halaman CBZ (wajib untuk disk cloud) dan
 * push berkas ke R2. Keduanya hanya menyentuh Storage::fake, jadi tidak
 * butuh kredensial cloud sungguhan.
 */
uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
});

/**
 * Buat CBZ tiruan berisi N gambar, dikembalikan sebagai path lokal sementara.
 */
function makeFakeCbz(int $pageCount): string
{
    $path = tempnam(sys_get_temp_dir(), 'cbz').'.cbz';
    $zip = new ZipArchive;
    $zip->open($path, ZipArchive::CREATE);

    for ($i = 1; $i <= $pageCount; $i++) {
        $png = imagecreatetruecolor(4, 4);
        imagefill($png, 0, 0, imagecolorallocate($png, 10 * $i, 20, 30));
        ob_start();
        imagepng($png);
        $zip->addFromString(sprintf('%02d.png', $i), (string) ob_get_clean());
    }

    $zip->close();

    return $path;
}

test('catalog:extract-cbz menulis halaman dan manifest lalu memperbarui books.pages', function () {
    Storage::fake('r2');
    config([
        'filesystems.books_disk' => 'r2',
        'filesystems.disks.r2.driver' => 's3',
    ]);

    $tmp = makeFakeCbz(3);
    Storage::disk('public')->put('books/komik-uji.cbz', (string) file_get_contents($tmp));

    $book = Book::factory()->create([
        'slug' => 'komik-uji',
        'file_path' => 'books/komik-uji.cbz',
        'is_published' => true,
        'pages' => 1,
    ]);

    $this->artisan('catalog:extract-cbz')->assertSuccessful();

    $disk = Storage::disk('r2');

    $manifest = json_decode($disk->get('books/komik-uji/pages.json'), true);

    expect($manifest)->toBe(['0001.png', '0002.png', '0003.png'])
        ->and($disk->exists('books/komik-uji/pages/0001.png'))->toBeTrue()
        ->and($book->fresh()->pages)->toBe(3);

    @unlink($tmp);
});

test('catalog:extract-cbz mengurutkan halaman secara numerik, bukan leksikal', function () {
    Storage::fake('r2');
    config([
        'filesystems.books_disk' => 'r2',
        'filesystems.disks.r2.driver' => 's3',
    ]);

    $path = tempnam(sys_get_temp_dir(), 'cbz').'.cbz';
    $zip = new ZipArchive;
    $zip->open($path, ZipArchive::CREATE);

    foreach ([1, 2, 10, 11] as $n) {
        $png = imagecreatetruecolor(4, 4);
        imagefill($png, 0, 0, imagecolorallocate($png, $n, $n, $n));
        ob_start();
        imagepng($png);
        $zip->addFromString(sprintf('%d.png', $n), (string) ob_get_clean());
    }

    $zip->close();
    Storage::disk('public')->put('books/urut.cbz', (string) file_get_contents($path));

    Book::factory()->create([
        'slug' => 'urut',
        'file_path' => 'books/urut.cbz',
        'is_published' => true,
    ]);

    $this->artisan('catalog:extract-cbz')->assertSuccessful();

    $manifest = json_decode(Storage::disk('r2')->get('books/urut/pages.json'), true);

    // Kalau ikut urutan leksikal, 10.png dan 11.png akan mendahului 2.png.
    expect($manifest)->toBe(['0001.png', '0002.png', '0003.png', '0004.png']);

    @unlink($path);
});

test('catalog:extract-cbz melewati buku yang manifest-nya sudah ada', function () {
    Storage::fake('r2');
    config([
        'filesystems.books_disk' => 'r2',
        'filesystems.disks.r2.driver' => 's3',
    ]);

    $book = Book::factory()->create([
        'slug' => 'sudah',
        'file_path' => 'books/sudah.cbz',
        'is_published' => true,
        'pages' => 7,
    ]);

    Storage::disk('r2')->put('books/sudah/pages.json', json_encode(array_map(
        fn ($i) => sprintf('%04d.png', $i),
        range(1, 7)
    )));

    $this->artisan('catalog:extract-cbz')->assertSuccessful();

    expect($book->fresh()->pages)->toBe(7);
});

test('catalog:push-to-r2 gagal dengan jelas bila R2_BUCKET belum diisi', function () {
    config([
        'filesystems.disks.r2.bucket' => null,
    ]);

    $this->artisan('catalog:push-to-r2')
        ->expectsOutputToContain('R2_BUCKET belum diisi')
        ->assertFailed();
});

test('catalog:push-to-r2 menyalin berkas dengan nama path yang sama', function () {
    Storage::fake('r2');
    Storage::fake('public');

    config([
        'filesystems.disks.r2.bucket' => 'senjapustaka-test',
        'filesystems.disks.r2.driver' => 's3',
    ]);

    Storage::disk('public')->put('covers/contoh.jpg', 'JPEG');
    Storage::disk('public')->put('books/contoh.pdf', '%PDF-1.4');

    $this->artisan('catalog:push-to-r2')->assertSuccessful();

    expect(Storage::disk('r2')->get('covers/contoh.jpg'))->toBe('JPEG')
        ->and(Storage::disk('r2')->get('books/contoh.pdf'))->toBe('%PDF-1.4');
});

test('catalog:push-to-r2 --dry-run tidak mengirim apa pun', function () {
    Storage::fake('r2');
    Storage::fake('public');

    config([
        'filesystems.disks.r2.bucket' => 'senjapustaka-test',
    ]);

    Storage::disk('public')->put('covers/kering.jpg', 'JPEG');

    $this->artisan('catalog:push-to-r2 --dry-run')->assertSuccessful();

    expect(Storage::disk('r2')->exists('covers/kering.jpg'))->toBeFalse();
});

test('FileStorage::url memakai path relatif untuk disk lokal', function () {
    Storage::fake('public');
    config(['filesystems.uploads_disk' => 'public']);

    // Disk lokal dikembalikan sebagai path relatif supaya tidak mengikat
    // nama host (pola lama '/storage/...' tetap terjaga).
    expect(FileStorage::url('covers/a.jpg'))->toBe('/storage/covers/a.jpg')
        ->and(FileStorage::url(null))->toBeNull();
});

test('FileStorage::url memakai URL absolut untuk disk cloud', function () {
    config([
        'filesystems.uploads_disk' => 'r2',
        'filesystems.disks.r2.driver' => 's3',
        'filesystems.disks.r2.bucket' => 'senjapustaka-test',
        'filesystems.disks.r2.region' => 'auto',
        'filesystems.disks.r2.key' => 'test-key',
        'filesystems.disks.r2.secret' => 'test-secret',
        'filesystems.disks.r2.endpoint' => 'https://test.r2.cloudflarestorage.com',
        'filesystems.disks.r2.url' => 'https://pub-abc.r2.dev',
    ]);

    // Berkas ada di R2, bukan di server — path relatif akan 404.
    expect(FileStorage::url('covers/a.jpg'))->toBe('https://pub-abc.r2.dev/covers/a.jpg');
});
