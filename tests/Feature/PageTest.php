<?php

use App\Models\Book;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
});

test('homepage menampilkan hero dan buku', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('halaman terakhirmu')
        ->assertSee('Sedang Trending');
});

test('halaman koleksi dapat diakses', function () {
    $this->get(route('library'))
        ->assertOk()
        ->assertSee('Koleksi Buku');
});

test('koleksi mendukung filter kategori dan pencarian', function () {
    $category = Category::first();
    $book = Book::where('is_published', true)->first();

    $this->get(route('library', ['kategori' => $category->slug]))
        ->assertOk();

    // Pencarian judul lengkap agar deterministik (fragmen pendek bisa lolos ke halaman berikutnya)
    $this->get(route('library', ['q' => $book->title]))
        ->assertOk()
        ->assertSee($book->title);
});

test('halaman detail buku dapat diakses', function () {
    $book = Book::where('is_published', true)->first();

    $this->get(route('books.show', $book))
        ->assertOk()
        ->assertSee($book->title);
});

test('halaman peringkat, leaderboard, kategori, penulis dapat diakses', function () {
    $this->get(route('ranking'))->assertOk();
    $this->get(route('leaderboard'))->assertOk();
    $this->get(route('categories.index'))->assertOk();
    $this->get(route('authors.index'))->assertOk();
    $this->get(route('about'))->assertOk();
    $this->get(route('contact'))->assertOk();
});

test('halaman legal dapat diakses', function () {
    $this->get(route('legal.privacy'))->assertOk()->assertSee('Kebijakan Privasi');
    $this->get(route('legal.terms'))->assertOk()->assertSee('Syarat');
});

test('halaman pencarian dapat diakses', function () {
    $this->get(route('search', ['q' => 'senja']))->assertOk();
});

test('auto-suggest mengembalikan JSON', function () {
    $this->getJson(route('search.suggest', ['q' => 'senja']))
        ->assertOk()
        ->assertJsonIsArray();
});

test('buku tidak terpublikasi menghasilkan 404', function () {
    $book = Book::factory()->create(['is_published' => false]);

    $this->get(route('books.show', $book))->assertNotFound();
});
