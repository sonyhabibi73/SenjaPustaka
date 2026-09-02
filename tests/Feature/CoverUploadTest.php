<?php

use App\Models\Author;
use App\Models\Book;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('book cover menampilkan gambar jika ada, gradien jika tidak', function () {
    $author = Author::factory()->create();

    $withImage = Book::factory()->create([
        'author_id' => $author->id,
        'cover_image' => 'covers/test.jpg',
    ]);
    $withoutImage = Book::factory()->create([
        'author_id' => $author->id,
        'cover_image' => null,
    ]);

    $htmlImage = view('components.book-cover', ['book' => $withImage])->render();
    expect($htmlImage)->toContain('book-cover-img')
        ->and($htmlImage)->toContain('/storage/covers/test.jpg');

    $htmlGradient = view('components.book-cover', ['book' => $withoutImage])->render();
    expect($htmlGradient)->toContain('book-card__gradient')
        ->and($htmlGradient)->not->toContain('book-cover-img');
});

test('admin dapat upload gambar cover buku', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['is_admin' => true]);
    $author = Author::factory()->create();

    $response = $this->actingAs($admin)->post(route('admin.buku.store'), [
        'title' => 'Novel dengan Cover',
        'author_id' => $author->id,
        'pages' => 120,
        'cover_image' => UploadedFile::fake()->image('cover.png', 300, 400),
        'is_published' => 1,
    ]);

    $response->assertRedirect(route('admin.buku.index'));

    $book = Book::where('title', 'Novel dengan Cover')->first();
    expect($book)->not->toBeNull()
        ->and($book->cover_image)->not->toBeNull();

    Storage::disk('public')->assertExists($book->cover_image);
});

test('admin dapat menghapus cover dan kembali ke gradien', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['is_admin' => true]);
    $book = Book::factory()->create(['cover_image' => 'covers/old.jpg']);

    $this->actingAs($admin)->put(route('admin.buku.update', $book), [
        'title' => $book->title,
        'author_id' => $book->author_id,
        'pages' => $book->pages,
        'remove_cover' => 1,
    ])->assertRedirect(route('admin.buku.index'));

    $book->refresh();
    expect($book->cover_image)->toBeNull();

    Storage::disk('public')->assertMissing('covers/old.jpg');
});

test('admin dapat upload buku CBZ dan tersimpan dengan ekstensi .cbz', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['is_admin' => true]);
    $author = Author::factory()->create();

    // Bangun file CBZ asli (zip berisi gambar)
    $tmp = tempnam(sys_get_temp_dir(), 'cbz');
    $zip = new ZipArchive;
    $zip->open($tmp, ZipArchive::CREATE);
    $zip->addFromString('page1.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='));
    $zip->close();

    // UploadedFile asli (bukan fake): MIME terdeteksi dari isi file = application/zip
    $file = new UploadedFile($tmp, 'buku.cbz', null, null, true);

    $response = $this->actingAs($admin)->post(route('admin.buku.store'), [
        'title' => 'Buku CBZ',
        'author_id' => $author->id,
        'pages' => 10,
        'file' => $file,
        'is_published' => 1,
    ]);

    $response->assertRedirect(route('admin.buku.index'));

    $book = Book::where('title', 'Buku CBZ')->first();
    expect($book)->not->toBeNull()
        ->and($book->file_path)->not->toBeNull()
        ->and($book->file_path)->toEndWith('.cbz');

    Storage::disk('public')->assertExists($book->file_path);

    @unlink($tmp);
});

test('cover tersimpan dengan ekstensi hasil deteksi server, bukan nama file klien', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['is_admin' => true]);
    $author = Author::factory()->create();

    // PNG asli tetapi dikirim dengan nama .svg (nama klien tidak dipercaya)
    $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
    $tmp = tempnam(sys_get_temp_dir(), 'cover');
    file_put_contents($tmp, $png);

    $file = new UploadedFile($tmp, 'cover.svg', null, null, true);

    $this->actingAs($admin)->post(route('admin.buku.store'), [
        'title' => 'Buku Uji Ekstensi',
        'author_id' => $author->id,
        'pages' => 10,
        'cover_image' => $file,
        'is_published' => 1,
    ])->assertRedirect(route('admin.buku.index'));

    $book = Book::where('title', 'Buku Uji Ekstensi')->first();
    expect($book->cover_image)->not->toBeNull();

    // Harus tersimpan dengan ekstensi hasil deteksi isi (png/webp hasil optimasi), bukan nama klien (svg)
    $ext = strtolower(pathinfo($book->cover_image, PATHINFO_EXTENSION));
    expect($ext)->toBeIn(['png', 'jpg', 'jpeg', 'webp'])
        ->and($ext)->not->toBe('svg');

    Storage::disk('public')->assertExists($book->cover_image);

    @unlink($tmp);
});
