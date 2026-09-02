<?php

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('halaman login dan daftar dapat diakses', function () {
    $this->get(route('login'))->assertOk()->assertSee('Selamat datang kembali');
    $this->get(route('register'))->assertOk()->assertSee('Mulai petualanganmu');
});

test('pengguna baru dapat mendaftar', function () {
    $this->post(route('register'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertRedirect(route('dashboard'));

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
});

test('pengguna dapat login dan logout', function () {
    $user = User::factory()->create(['password' => 'password123']);

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password123',
    ])->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);

    $this->post(route('logout'))->assertRedirect(route('home'));
    $this->assertGuest();
});

test('login dengan kata sandi salah ditolak', function () {
    $user = User::factory()->create(['password' => 'password123']);

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'salah',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('dashboard membutuhkan login', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('reader membutuhkan login', function () {
    $book = Book::factory()->create();

    $this->get(route('reader', $book))->assertRedirect(route('login'));
});
