<?php

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    $this->user = User::where('email', 'user@senjapustaka.test')->first();
    $this->actingAs($this->user);
});

afterEach(function () {
    @unlink(storage_path('app/public/books/range-test.pdf'));
});

test('reader.file mendukung HTTP Range request (206 Partial Content)', function () {
    // buat file PDF tiruan yang valid
    $path = storage_path('app/public/books/range-test.pdf');
    file_put_contents($path, str_repeat('PDF-test-content-', 1000));

    $book = Book::factory()->create([
        'file_path' => 'books/range-test.pdf',
        'is_published' => true,
    ]);

    // BinaryFileResponse dikembalikan mentah (tanpa TestResponse),
    // jadi pakai API Symfony langsung.
    $response = $this->get(route('reader.file', $book), [
        'Range' => 'bytes=0-1023',
    ]);

    expect($response->getStatusCode())->toBe(206)
        ->and($response->headers->get('Content-Range'))->toMatch('/^bytes 0-1023\//');

    // non-range request tetap 200
    $full = $this->get(route('reader.file', $book));
    expect($full->getStatusCode())->toBe(200);
});
