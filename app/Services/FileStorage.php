<?php

namespace App\Services;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

/**
 * Titik tunggal untuk menentukan disk mana yang dipakai:
 *
 * - uploads (sampul, avatar) -> UPLOADS_DISK, default 'public'
 * - books (PDF/CBZ)         -> BOOKS_DISK,   default 'public'
 *
 Secara lokal keduanya disk 'public' seperti sebelumnya (storage/app/public).
saat deploy, kedua env diarahkan ke disk 'r2' karena disk Render bersifat
ephemeral. Kode cukup memanggil FileStorage::uploads() / ::books() supaya
tidak perlu tahu disk mana yang aktif.
 */
final class FileStorage
{
    public static function uploadsDisk(): string
    {
        return (string) config('filesystems.uploads_disk', 'public');
    }

    public static function booksDisk(): string
    {
        return (string) config('filesystems.books_disk', 'public');
    }

    public static function uploads(): Filesystem
    {
        return Storage::disk(self::uploadsDisk());
    }

    public static function books(): Filesystem
    {
        return Storage::disk(self::booksDisk());
    }

    /**
     * Disk non-lokal tidak punya path filesystem, jadi ->path(), ZipArchive,
     * dan filemtime() tidak boleh dipakai padanya. Fungsi ini jadi penjaga:
     * pemanggil memeriksa dulu sebelum jatuh ke jalur file lokal.
     */
    public static function isRemote(?string $disk = null): bool
    {
        $name = $disk ?? self::booksDisk();

        return config('filesystems.disks.'.$name.'.driver') !== 'local';
    }

    /**
     * URL publik sebuah berkas.
     *
     * Disk lokal dikembalikan sebagai path relatif ('/storage/...') supaya
     * tidak ikut mengikat nama host; disk cloud memakai URL absolut yang
     * dikembalikan driver (mis. domain r2.dev).
     */
    public static function url(?string $path, ?string $disk = null): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        $name = $disk ?? self::uploadsDisk();
        $url = Storage::disk($name)->url($path);

        if (self::isRemote($name)) {
            return $url;
        }

        return parse_url($url, PHP_URL_PATH) ?: $url;
    }

    /**
     * Path absolut di disk lokal, atau null bila disknya berbasis cloud.
     */
    public static function localPath(string $path, ?string $disk = null): ?string
    {
        $name = $disk ?? self::booksDisk();

        if (self::isRemote($name)) {
            return null;
        }

        $full = Storage::disk($name)->path($path);

        return is_file($full) ? $full : null;
    }
}
