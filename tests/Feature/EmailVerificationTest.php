<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\Mime\Email;

uses(RefreshDatabase::class);

/**
 * Ambil URL verifikasi dari email yang benar-benar terkirim
 * (MAIL_MAILER=array di lingkungan test menyimpan pesan di memori).
 */
function ambilUrlVerifikasi(): string
{
    $messages = app('mailer')->getSymfonyTransport()->messages();

    expect($messages)->not->toBeEmpty();

    $original = $messages->last()->getOriginalMessage();

    $body = $original instanceof Email
        ? (string) ($original->getTextBody() ?? '').' '.(string) ($original->getHtmlBody() ?? '')
        : (string) $original->getBody();

    preg_match('#/verifikasi-email/([A-Za-z0-9]{64})#', $body, $matches);

    expect($matches)->toHaveKey(1);

    return $matches[1];
}

test('halaman verifikasi email dapat dibuka pengguna belum terverifikasi', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->get(route('verification.notice'))
        ->assertOk()
        ->assertSee('Verifikasi Email');
});

test('pengguna yang sudah terverifikasi diarahkan ke dashboard', function () {
    $user = User::factory()->create(); // default: sudah terverifikasi

    $this->actingAs($user)
        ->get(route('verification.notice'))
        ->assertRedirect(route('dashboard'));
});

test('permintaan verifikasi mengirim email dan linknya berhasil dipakai', function () {
    $user = User::factory()->unverified()->create();
    $this->actingAs($user);

    $this->post(route('verification.send'))
        ->assertSessionHas('success')
        ->assertSessionHasNoErrors();

    $this->assertDatabaseCount('email_verifications', 1);

    $token = ambilUrlVerifikasi();

    // Token mentah di URL berbeda dari yang disimpan di database (hanya hash-nya).
    $stored = DB::table('email_verifications')->value('token');
    expect($stored)->not->toBe($token);

    $this->get(route('verification.verify', $token))
        ->assertRedirect(route('dashboard'));

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});

test('token verifikasi yang salah ditolak dan tidak memverifikasi akun', function () {
    $user = User::factory()->unverified()->create();
    $this->actingAs($user);

    $this->post(route('verification.send'))->assertSessionHas('success');

    $this->get(route('verification.verify', str_repeat('a', 64)))
        ->assertRedirect(route('verification.notice'))
        ->assertSessionHas('error');

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('link verifikasi tidak bisa dipakai dua kali', function () {
    $user = User::factory()->unverified()->create();
    $this->actingAs($user);

    $this->post(route('verification.send'))->assertSessionHas('success');
    $token = ambilUrlVerifikasi();

    $this->get(route('verification.verify', $token))->assertRedirect(route('dashboard'));

    $this->get(route('verification.verify', $token))
        ->assertRedirect(route('verification.notice'))
        ->assertSessionHas('error');
});

test('permintaan email verifikasi dibatasi (rate limit)', function () {
    $user = User::factory()->unverified()->create();
    $this->actingAs($user);

    foreach (range(1, 3) as $attempt) {
        $this->post(route('verification.send'))->assertRedirect();
    }

    $this->post(route('verification.send'))->assertStatus(429);
});
