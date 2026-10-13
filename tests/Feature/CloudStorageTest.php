<?php

use App\Models\Book;
use App\Models\User;
use App\Services\FileStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Perilaku reader saat disk buku berada di cloud (R2) alih-alih disk lokal.
 *
 * Disk lokal dan cloud punya jalur kode berbeda di ReaderController:
 * - lokal : PDF dilayani BinaryFileResponse (mendukung Range 206),
 *           halaman CBZ diambil dari ZipArchive
 * - cloud : keduanya di-redirect ke URL bertanda tangan, karena file tidak
 *           ada di filesystem server dan ZipArchive tak bisa membuka ZIP remote
 *
 * Test memakai Storage::fake sehingga tidak butuh kredensial R2 sungguhan.
 */
uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    $this->user = User::where('email', 'user@senjapustaka.test')->first();
    $this->actingAs($this->user);

    // Disk 'r2' palsu + konfigurasi driver s3 supaya dianggap cloud.
    config([
        'filesystems.books_disk' => 'r2',
        'filesystems.disks.r2.driver' => 's3',
        'filesystems.disks.r2.bucket' => 'senjapustaka-test',
    ]);

    Cache::flush();
});

afterEach(function () {
    Cache::flush();
});

test('books_disk cloud membuat reader menandai disk sebagai remote', function () {
    expect(FileStorage::booksDisk())->toBe('r2')
        ->and(FileStorage::isRemote())->toBeTrue();
});

test('PDF di disk cloud dialihkan ke URL bertanda tangan, bukan dilayani lokal', function () {
    // temporaryUrl pada disk s3 butuh endpoint & kunci; untuk test ini cukup
    // membuktikan file() tidak mencoba membuka path filesystem.
    Storage::fake('r2');
    Storage::disk('r2')->put('books/cloud.pdf', 'PDF');

    $book = Book::factory()->create([
        'file_path' => 'books/cloud.pdf',
        'is_published' => true,
    ]);

    // Endpoint & credential disetel supaya temporaryUrl bisa membangun URL.
    config([
        'filesystems.disks.r2.key' => 'test-key',
        'filesystems.disks.r2.secret' => 'test-secret',
        'filesystems.disks.r2.region' => 'auto',
        'filesystems.disks.r2.bucket' => 'senjapustaka-test',
        'filesystems.disks.r2.endpoint' => 'https://test.r2.cloudflarestorage.com',
    ]);

    $response = $this->get(route('reader.file', $book));

    // Di cloud tidak ada 206 dari server aplikasi; hasilnya pengalihan.
    expect($response->getStatusCode())->toBe(302)
        ->and($response->headers->get('Location'))->toContain('cloud.pdf');
});

test('halaman CBZ di disk cloud diambil dari manifest, bukan dari ZipArchive', function () {
    Storage::fake('r2');

    $book = Book::factory()->create([
        'slug' => 'komik-dua-halaman',
        'file_path' => 'books/komik.cbz',
        'is_published' => true,
        'pages' => 2,
    ]);

    Storage::disk('r2')->put('books/komik-dua-halaman/pages.json', json_encode(['0001.jpg', '0002.jpg']));

    config([
        'filesystems.disks.r2.key' => 'test-key',
        'filesystems.disks.r2.secret' => 'test-secret',
        'filesystems.disks.r2.region' => 'auto',
        'filesystems.disks.r2.bucket' => 'senjapustaka-test',
        'filesystems.disks.r2.endpoint' => 'https://test.r2.cloudflarestorage.com',
    ]);

    $response = $this->get(route('reader.cbz', [$book, 2]));

    expect($response->getStatusCode())->toBe(302)
        ->and($response->headers->get('Location'))->toContain('0002.jpg');
});

test('halaman CBZ di luar jangkauan manifest akan 404', function () {
    Storage::fake('r2');

    $book = Book::factory()->create([
        'slug' => 'komik-satu-halaman',
        'file_path' => 'books/komik.cbz',
        'is_published' => true,
    ]);

    Storage::disk('r2')->put('books/komik-satu-halaman/pages.json', json_encode(['0001.jpg']));

    config([
        'filesystems.disks.r2.key' => 'test-key',
        'filesystems.disks.r2.secret' => 'test-secret',
        'filesystems.disks.r2.region' => 'auto',
        'filesystems.disks.r2.bucket' => 'senjapustaka-test',
        'filesystems.disks.r2.endpoint' => 'https://test.r2.cloudflarestorage.com',
    ]);

    $this->get(route('reader.cbz', [$book, 9]))->assertNotFound();
});

test('jumlah halaman CBZ di cloud diambil dari manifest, bukan dari books.pages', function () {
    Storage::fake('r2');

    $book = Book::factory()->create([
        'slug' => 'komik-tiga-halaman',
        'file_path' => 'books/komik.cbz',
        'is_published' => true,
        'pages' => 1, // nilai DB sengaja dikecilkan
    ]);

    Storage::disk('r2')->put('books/komik-tiga-halaman/pages.json', json_encode(['0001.jpg', '0002.jpg', '0003.jpg']));

    $this->get(route('reader', $book))
        ->assertOk()
        // Pembaca memakai jumlah halaman dari manifest (3), bukan kolom DB (1).
        ->assertSee('data-pages="3"', false);

    // Kolom DB sengaja tidak ditulis ulang saat render — manifest yang jadi
    // sumber kebenaran, supaya tidak ada penulisan database di tiap request.
    expect($book->fresh()->pages)->toBe(1);
});
