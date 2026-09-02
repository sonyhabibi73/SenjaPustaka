<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Nonaktifkan CSRF di environment test (token tidak dikirim oleh test client).
     */
    protected function setUp(): void
    {
        // phpunit.xml memakai `<env force="true">`, tapi di PHPUnit versi ini nilai-nilai
        // tersebut tidak sampai ke helper env() Laravel (Laravel hanya membaca $_ENV/$_SERVER).
        // Set manual sebelum aplikasi di-boot supaya test memakai konfigurasi yang benar
        // dan TIDAK PERNAH menyentuh database MySQL yang berisi data asli.
        //
        // Daftar ini HARUS tetap sinkron dengan blok <php> di phpunit.xml.
        $testEnv = [
            'APP_ENV' => 'testing',
            'APP_MAINTENANCE_DRIVER' => 'file',
            'BCRYPT_ROUNDS' => '4',
            'BROADCAST_CONNECTION' => 'null',
            'CACHE_STORE' => 'array',
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => ':memory:',
            'MAIL_MAILER' => 'array',
            'QUEUE_CONNECTION' => 'sync',
            'SESSION_DRIVER' => 'array',
        ];

        foreach ($testEnv as $key => $value) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }

        parent::setUp();

        $this->withoutMiddleware(
            PreventRequestForgery::class,
        );
    }

    /**
     * Jaring pengaman: paksa semua test memakai SQLite in-memory.
     *
     * RefreshDatabase menjalankan `migrate:fresh` di koneksi default. Kalau pengaturan
     * di atas gagal karena alasan apa pun, override ini tetap menjamin MySQL tidak tersentuh.
     */
    public function createApplication(): Application
    {
        $app = parent::createApplication();

        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite.database', ':memory:');

        return $app;
    }
}
