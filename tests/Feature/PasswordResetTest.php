<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

test('halaman lupa password dapat diakses', function () {
    $this->get(route('password.request'))
        ->assertOk()
        ->assertSee('Lupa Password');
});

test('permintaan reset membalas pesan generik untuk email yang tidak terdaftar (anti user enumeration)', function () {
    Notification::fake();

    $this->post(route('password.email'), ['email' => 'tidak-ada@example.com'])
        ->assertSessionHas('success')
        ->assertSessionHasNoErrors();

    Notification::assertNothingSent();
});

test('alur reset password lengkap: link, simpan, lalu login dengan password baru', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post(route('password.email'), ['email' => $user->email])
        ->assertSessionHas('success');

    $token = null;

    Notification::assertSentTo(
        $user,
        ResetPassword::class,
        function ($notification) use (&$token) {
            $token = $notification->token;

            return true;
        }
    );

    expect($token)->not->toBeNull();

    // Form reset terbuka, lalu password diganti.
    $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]))
        ->assertOk()
        ->assertSee('Reset Password');

    $this->post(route('password.update'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'BaruSangatKuat123!@',
        'password_confirmation' => 'BaruSangatKuat123!@',
    ])->assertRedirect(route('login'));

    // Password lama tidak berlaku lagi.
    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertSessionHasErrors('email');

    // Password baru bisa dipakai login.
    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'BaruSangatKuat123!@',
    ])->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

test('token reset yang salah atau kedaluwarsa ditolak', function () {
    $user = User::factory()->create();
    $passwordSebelum = $user->password;

    $this->post(route('password.update'), [
        'token' => 'token-ngaco-sekali',
        'email' => $user->email,
        'password' => 'BaruSangatKuat123!@',
        'password_confirmation' => 'BaruSangatKuat123!@',
    ])->assertSessionHas('error');

    expect($user->fresh()->password)->toBe($passwordSebelum);
});

test('password reset dibatasi agar tidak bisa dipakai membanjiri email', function () {
    Notification::fake();

    // Throttle route:3 per menit.
    foreach (range(1, 3) as $attempt) {
        $this->post(route('password.email'), ['email' => 'user@senjapustaka.test'])
            ->assertRedirect();
    }

    $this->post(route('password.email'), ['email' => 'user@senjapustaka.test'])
        ->assertStatus(429);
});
