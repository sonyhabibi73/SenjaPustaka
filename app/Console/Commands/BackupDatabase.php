<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database
                            {--compress : Compress the backup file}
                            {--keep=7 : Number of backup files to keep}';

    protected $description = 'Backup the database to storage';

    public function handle(): int
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        $filename = 'backups/database-'.now()->format('Y-m-d-His').'.sql';
        $tempPath = storage_path('app/temp/'.basename($filename));

        $this->info('Starting database backup...');

        try {
            match ($connection) {
                'sqlite' => $this->backupSqlite($config, $tempPath),
                'mysql' => $this->backupMysql($config, $tempPath),
                default => throw new \Exception("Unsupported database connection: {$connection}"),
            };

            if ($this->option('compress')) {
                $this->compress($tempPath);
                $filename .= '.gz';
            }

            // Upload to storage
            $content = file_get_contents($tempPath);

            if ($content === false) {
                throw new \Exception("Gagal membaca file backup sementara: {$tempPath}");
            }

            Storage::disk('local')->put($filename, $content);

            // Cleanup temp file
            @unlink($tempPath);

            // Clean old backups
            $this->cleanOldBackups();

            $this->info("Database backup completed: {$filename}");

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Backup failed: '.$e->getMessage());
            @unlink($tempPath);

            return self::FAILURE;
        }
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function backupSqlite(array $config, string $path): void
    {
        $dbPath = (string) ($config['database'] ?? '');
        $dir = dirname($path);

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $process = new Process(['sqlite3', $dbPath, '.dump']);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new \Exception('SQLite backup failed: '.$process->getErrorOutput());
        }

        file_put_contents($path, (string) $process->getOutput());
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function backupMysql(array $config, string $path): void
    {
        $dir = dirname($path);

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $command = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s %s > %s',
            escapeshellarg((string) ($config['host'] ?? 'localhost')),
            escapeshellarg((string) ($config['port'] ?? 3306)),
            escapeshellarg((string) ($config['username'] ?? 'root')),
            escapeshellarg((string) ($config['password'] ?? '')),
            escapeshellarg((string) ($config['database'] ?? '')),
            escapeshellarg($path)
        );

        $process = Process::fromShellCommandline($command);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new \Exception('MySQL backup failed: '.$process->getErrorOutput());
        }
    }

    private function compress(string $path): void
    {
        $process = new Process(['gzip', '-f', $path]);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new \Exception('Compression failed: '.$process->getErrorOutput());
        }
    }

    private function cleanOldBackups(): void
    {
        $keep = (int) $this->option('keep');
        $files = Storage::disk('local')->files('backups');

        if (count($files) <= $keep) {
            return;
        }

        // Sort by name (which includes timestamp)
        sort($files);

        // Delete oldest backups
        $toDelete = array_slice($files, 0, count($files) - $keep);
        Storage::disk('local')->delete($toDelete);
    }
}
