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

test('reader PDF menyajikan data-url relatif untuk PDF.js', function () {
    $book = Book::factory()->create([
        'file_path' => 'books/sample.pdf',
        'is_published' => true,
    ]);

    $this->get(route('reader', $book))
        ->assertOk()
        ->assertSee('id="pdf-viewer"', false)
        ->assertSee('data-url="/baca/'.$book->slug.'/file"', false)
        ->assertDontSee('data-url="http', false);
});

test('favorit bisa di-toggle via AJAX', function () {
    $book = Book::where('is_published', true)->first();

    $this->postJson(route('favorites.toggle'), ['book_id' => $book->id])
        ->assertOk()
        ->assertJson(['favorited' => true]);

    $this->assertDatabaseHas('favorites', ['user_id' => $this->user->id, 'book_id' => $book->id]);

    $this->postJson(route('favorites.toggle'), ['book_id' => $book->id])
        ->assertOk()
        ->assertJson(['favorited' => false]);
});

test('rating dan review bisa disimpan', function () {
    $book = Book::where('is_published', true)->first();

    $this->postJson(route('reviews.store'), [
        'book_id' => $book->id,
        'rating' => 5,
        'comment' => 'Buku yang luar biasa!',
    ])->assertOk()->assertJson(['ok' => true]);

    $book->refresh();
    expect($book->rating_count)->toBeGreaterThan(0);

    $this->assertDatabaseHas('reviews', ['user_id' => $this->user->id, 'book_id' => $book->id]);
});

test('progres membaca tersimpan dan memberi poin', function () {
    $book = Book::where('is_published', true)
        ->whereDoesntHave('progress', fn ($q) => $q->where('user_id', $this->user->id))
        ->first();
    $pointsBefore = $this->user->points;

    $this->postJson(route('progress.save'), [
        'book_id' => $book->id,
        'page' => 5,
    ])->assertOk()->assertJson(['ok' => true]);

    $this->assertDatabaseHas('reading_progress', [
        'user_id' => $this->user->id,
        'book_id' => $book->id,
        'current_page' => 5,
    ]);

    expect($this->user->fresh()->points)->toBeGreaterThan($pointsBefore);
});

test('target membaca bisa disimpan', function () {
    $this->post(route('goals.store'), [
        'year' => now()->year,
        'target_books' => 12,
        'target_pages' => 3650,
    ])->assertSessionHasNoErrors();

    $this->assertDatabaseHas('reading_goals', [
        'user_id' => $this->user->id,
        'target_books' => 12,
    ]);
});

test('notifikasi bisa ditandai sudah dibaca', function () {
    $this->post(route('notifications.read-all'))->assertRedirect();

    expect($this->user->unreadNotifications()->count())->toBe(0);
});

test('dashboard menampilkan level dan rekomendasi', function () {
    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Rekomendasi Untukmu')
        ->assertSee('Level');
});

test('panel admin menolak pengguna biasa', function () {
    $this->get(route('admin.dashboard'))->assertForbidden();
});

test('panel admin dapat diakses admin', function () {
    $admin = User::where('email', 'admin@senjapustaka.test')->first();
    $this->actingAs($admin);

    $this->get(route('admin.dashboard'))->assertOk();
    $this->get(route('admin.buku.index'))->assertOk();
    $this->get(route('admin.user.index'))->assertOk();
    $this->get(route('admin.newsletter.index'))->assertOk();
});
