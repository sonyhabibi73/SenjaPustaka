<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PDO;
use Throwable;

class SyncSqliteToMysql extends Command
{
    /**
     * Sinkronkan semua data dari database/database.sqlite ke koneksi MySQL yang aktif.
     *
     * Default: upsert aman — baris baru ditambahkan, baris yang berubah diperbarui,
     * tidak ada baris yang dihapus. Gunakan --mirror untuk sinkron penuh.
     */
    protected $signature = 'app:sync-sqlite-to-mysql
        {--mirror : Hapus baris di MySQL yang tidak ada di SQLite (sinkron penuh)}';

    protected $description = 'Sinkronkan semua data dari database/database.sqlite ke MySQL';

    public function handle(): int
    {
        $sqlitePath = database_path('database.sqlite');

        if (! file_exists($sqlitePath)) {
            $this->error("File SQLite tidak ditemukan: {$sqlitePath}");
            $this->line('Pastikan file database/database.sqlite masih ada.');

            return self::FAILURE;
        }

        try {
            $mysql = DB::connection('mysql')->getPdo();
        } catch (Throwable $e) {
            $this->error('Gagal terhubung ke MySQL: '.$e->getMessage());
            $this->line('Cek konfigurasi DB_HOST, DB_DATABASE, DB_USERNAME, dll di file .env.');

            return self::FAILURE;
        }

        $mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $mysql->exec('SET NAMES utf8mb4');

        $mysqlDb = (string) $mysql->query('SELECT DATABASE()')->fetchColumn();

        $this->info('Sumber: '.$sqlitePath);
        $this->info('Target: MySQL database `'.$mysqlDb.'` @ '.config('database.connections.mysql.host'));

        if (config('database.default') !== 'mysql') {
            $this->error('Koneksi default aplikasi bukan "mysql". Perintah ini wajib dijalankan saat .env memakai DB_CONNECTION=mysql, supaya tidak salah menulis ke database lain.');

            return self::FAILURE;
        }
        $this->newLine();

        $sqlite = new PDO('sqlite:'.$sqlitePath);
        $sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $tables = $sqlite
            ->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name")
            ->fetchAll(PDO::FETCH_COLUMN);

        $report = [];
        $warnings = 0;
        $totalDeleted = 0;

        try {
            $mysql->exec('SET FOREIGN_KEY_CHECKS=0');

            foreach ($tables as $table) {
                if (! $this->validIdentifier($table)) {
                    $this->warn("Lewati tabel `{$table}` — nama tidak valid.");
                    $warnings++;

                    continue;
                }

                $exists = $mysql
                    ->query('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='.$mysql->quote($mysqlDb).' AND table_name='.$mysql->quote($table))
                    ->fetchColumn();

                if (! $exists) {
                    $this->warn("Tabel `{$table}` belum ada di MySQL — jalankan `php artisan migrate` dulu.");
                    $warnings++;

                    continue;
                }

                $cols = $sqlite->query("PRAGMA table_info(`{$table}`)")->fetchAll(PDO::FETCH_ASSOC);
                $names = array_column($cols, 'name');

                $invalid = array_values(array_filter($names, fn ($c) => ! $this->validIdentifier($c)));
                if ($invalid !== []) {
                    $this->warn("Lewati tabel `{$table}` — ada nama kolom tidak valid: ".implode(', ', $invalid).'.');
                    $warnings++;

                    continue;
                }

                if ($names === []) {
                    $this->warn("Lewati tabel `{$table}` — tidak punya kolom.");
                    $warnings++;

                    continue;
                }

                // Kolom primary key dari SQLite (dipakai untuk ON DUPLICATE KEY UPDATE)
                $pk = [];
                foreach ($cols as $col) {
                    if ((int) $col['pk'] > 0) {
                        $pk[(int) $col['pk']] = $col['name'];
                    }
                }
                ksort($pk);
                $pk = array_values($pk);

                if ($pk === []) {
                    $this->warn("Tabel `{$table}` tidak punya primary key — ON DUPLICATE KEY UPDATE tidak aktif, baris bisa dobel bila perintah dijalankan berulang.");
                }

                try {
                    [$inserted, $updated, $unchanged] = $this->syncTable($sqlite, $mysql, $table, $names, $pk);
                } catch (Throwable $e) {
                    $this->warn("Gagal sinkron tabel `{$table}`: ".$e->getMessage());
                    $warnings++;

                    continue;
                }

                $sqliteCount = (int) $sqlite->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
                $mysqlCount = (int) $mysql->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();

                $report[] = [
                    'table' => $table,
                    'pk' => $pk,
                    'sqlite' => $sqliteCount,
                    'inserted' => $inserted,
                    'updated' => $updated,
                    'unchanged' => $unchanged,
                    'mysql_after' => $mysqlCount,
                    'deleted' => 0,
                ];
            }

            // Mode mirror: hapus baris di MySQL yang tidak ada di SQLite (hanya tabel ber-PK tunggal)
            if ($this->option('mirror')) {
                foreach ($report as $i => $row) {
                    $pk = $row['pk'];
                    if (count($pk) !== 1) {
                        continue;
                    }
                    $pkCol = $pk[0];

                    $sourceIds = array_map(
                        'strval',
                        $sqlite->query("SELECT `{$pkCol}` FROM `{$row['table']}`")->fetchAll(PDO::FETCH_COLUMN)
                    );
                    $mysqlIds = array_map(
                        'strval',
                        $mysql->query("SELECT `{$pkCol}` FROM `{$row['table']}`")->fetchAll(PDO::FETCH_COLUMN)
                    );

                    $missing = array_diff($mysqlIds, $sourceIds);
                    $deleted = 0;

                    foreach (array_chunk($missing, 500) as $chunk) {
                        $in = implode(',', array_fill(0, count($chunk), '?'));
                        $stmt = $mysql->prepare("DELETE FROM `{$row['table']}` WHERE `{$pkCol}` IN ({$in})");
                        $stmt->execute($chunk);
                        $deleted += $stmt->rowCount();
                    }

                    $report[$i]['deleted'] = $deleted;
                    $totalDeleted += $deleted;
                }
            }
        } finally {
            $mysql->exec('SET FOREIGN_KEY_CHECKS=1');
        }

        $this->table(
            ['Tabel', 'SQLite', 'Ditambah', 'Diupdate', 'Sama', 'MySQL setelah', 'Dihapus'],
            array_map(
                fn ($r) => [$r['table'], $r['sqlite'], $r['inserted'], $r['updated'], $r['unchanged'], $r['mysql_after'], $r['deleted']],
                $report
            )
        );

        $this->newLine();

        if ($this->option('mirror')) {
            $this->info('Sinkron penuh selesai. Baris dihapus: '.$totalDeleted.'.');
        } else {
            $this->info('Sinkron selesai. (Gunakan --mirror untuk menghapus baris MySQL yang tidak ada di SQLite.)');
        }

        if ($warnings > 0) {
            $this->warn("Ada {$warnings} peringatan. Untuk tabel yang belum ada di MySQL, jalankan `php artisan migrate` dulu, lalu ulangi perintah ini.");

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * Salin satu tabel dari SQLite ke MySQL memakai INSERT ... ON DUPLICATE KEY UPDATE.
     *
     * @return array{0: int, 1: int, 2: int} [inserted, updated, unchanged]
     */
    private function syncTable(PDO $sqlite, PDO $mysql, string $table, array $names, array $pk): array
    {
        $colList = implode(',', array_map(fn ($c) => "`{$c}`", $names));
        $placeholders = implode(',', array_fill(0, count($names), '?'));

        $sql = "INSERT INTO `{$table}` ({$colList}) VALUES ({$placeholders})";

        $updateCols = array_values(array_diff($names, $pk));
        if ($updateCols !== []) {
            $set = implode(', ', array_map(fn ($c) => "`{$c}`=VALUES(`{$c}`)", $updateCols));
            $sql .= " ON DUPLICATE KEY UPDATE {$set}";
        }

        $select = $sqlite->query("SELECT {$colList} FROM `{$table}`");
        $insert = $mysql->prepare($sql);

        $inserted = 0;
        $updated = 0;
        $unchanged = 0;

        while ($row = $select->fetch(PDO::FETCH_NUM)) {
            $insert->execute($row);
            $affected = $insert->rowCount();

            // MySQL: 1 = baris baru, 2 = baris lama diperbarui, 0 = nilainya sama
            if ($affected === 2) {
                $updated++;
            } elseif ($affected === 1) {
                $inserted++;
            } else {
                $unchanged++;
            }
        }

        return [$inserted, $updated, $unchanged];
    }

    private function validIdentifier(string $name): bool
    {
        return preg_match('/^[A-Za-z0-9_]+$/', $name) === 1;
    }
}
