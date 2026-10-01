<?php

use App\Models\ActivityLog;
use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
});

test('response headers keamanan terpasang di semua halaman', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertHeader('X-Frame-Options', 'DENY')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('Permissions-Policy')
        ->assertHeader('Content-Security-Policy');
});

test('upaya SQL injection pada pencarian tidak merusak query dan tidak membocorkan data', function () {
    $payload = "' OR '1'='1' --";

    $this->get(route('search', ['q' => $payload]))
        ->assertOk()
        ->assertDontSee('3726 MDPL');

    $this->getJson(route('search.suggest', ['q' => $payload]))
        ->assertOk()
        ->assertJson([]);

    $this->get(route('library', ['q' => $payload]))
        ->assertOk()
        ->assertDontSee('3726 MDPL');
});

test('judul buku berisi skrip HTML di-escape saat dirender (anti XSS)', function () {
    $book = Book::factory()->create([
        'title' => '<script>alert("xss")</script>',
        'is_published' => true,
    ]);

    $this->get(route('books.show', $book))
        ->assertOk()
        ->assertDontSee('<script>alert("xss")</script>', false)
        ->assertSee('&lt;script&gt;', false);
});

test('halaman log aktivitas ditolak untuk tamu dan non-admin', function () {
    $this->get(route('admin.activity-logs.index'))->assertRedirect(route('login'));

    $this->actingAs(User::where('email', 'user@senjapustaka.test')->first());
    $this->get(route('admin.activity-logs.index'))->assertForbidden();
    $this->get(route('admin.activity-logs.show', ActivityLog::create([
        'action' => 'test.action',
        'description' => 'uji',
    ])))->assertForbidden();
});

test('admin dapat membuka daftar dan detail log aktivitas', function () {
    $admin = User::where('email', 'admin@senjapustaka.test')->first();

    $log = ActivityLog::create([
        'user_id' => $admin->id,
        'action' => 'admin.book.updated',
        'description' => 'Buku diperbarui',
        'ip_address' => '127.0.0.1',
        'user_agent' => 'PHPUnit',
        'metadata' => ['book_id' => 1],
    ]);

    $this->actingAs($admin);

    $this->get(route('admin.activity-logs.index'))
        ->assertOk()
        ->assertSee('Log Aktivitas')
        ->assertSee('admin.book.updated');

    $this->get(route('admin.activity-logs.show', $log))
        ->assertOk()
        ->assertSee('Detail Log Aktivitas')
        ->assertSee('Buku diperbarui')
        ->assertSee('127.0.0.1');
});

test('registrasi tidak bisa dipakai untuk menaikkan peran admin (mass assignment)', function () {
    $this->post(route('register'), [
        'name' => 'Penyusup',
        'email' => 'penyusup@example.com',
        'password' => 'SecurePass123!@#',
        'password_confirmation' => 'SecurePass123!@#',
        'is_admin' => 1,
        'points' => 999999,
    ])->assertRedirect(route('dashboard'));

    $user = User::where('email', 'penyusup@example.com')->firstOrFail();

    expect($user->is_admin)->toBeFalse()
        ->and($user->points)->toBe(0);
});

test('update profil tidak bisa memanipulasi peran atau poin', function () {
    $user = User::where('email', 'user@senjapustaka.test')->first();
    $this->actingAs($user);

    $this->patch(route('profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'bio' => 'Bio baru',
        'is_admin' => 1,
        'points' => 999999,
    ])->assertSessionHasNoErrors();

    $user->refresh();

    expect($user->is_admin)->toBeFalse()
        ->and($user->points)->toBe(620);
});

test('form kontak dibatasi agar tidak bisa di-spam', function () {
    $payload = [
        'name' => 'Tester',
        'email' => 'tester@example.com',
        'message' => 'Halo, ini pesan uji.',
    ];

    foreach (range(1, 5) as $attempt) {
        $this->post(route('contact.send'), $payload)->assertRedirect();
    }

    $this->post(route('contact.send'), $payload)->assertStatus(429);
});

test('login dibatasi dari brute force (throttle percobaan)', function () {
    foreach (range(1, 5) as $attempt) {
        $this->post(route('login'), [
            'email' => 'user@senjapustaka.test',
            'password' => 'salah-semua',
        ])->assertSessionHasErrors('email');
    }

    $this->post(route('login'), [
        'email' => 'user@senjapustaka.test',
        'password' => 'salah-semua',
    ])->assertStatus(429);
});
