<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:backup-database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform a database backup (SQLite or MySQL) and clean up old backups';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting database backup...');

        $connection = config('database.default');
        $timestamp = date('Y-m-d_H-i-s');
        $backupDir = storage_path('app/backups');

        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        if ($connection === 'sqlite') {
            $dbPath = config('database.connections.sqlite.database');
            
            if (!File::exists($dbPath)) {
                $this->error("SQLite database file not found at: {$dbPath}");
                return 1;
            }

            $backupPath = "{$backupDir}/backup-{$timestamp}.sqlite";
            File::copy($dbPath, $backupPath);
            $this->info("Backup successfully saved to: {$backupPath}");
        } elseif ($connection === 'mysql') {
            $dbConfig = config('database.connections.mysql');
            $backupPath = "{$backupDir}/backup-{$timestamp}.sql";
            
            $command = sprintf(
                'mysqldump --user=%s --password=%s --host=%s %s > %s',
                escapeshellarg($dbConfig['username']),
                escapeshellarg($dbConfig['password']),
                escapeshellarg($dbConfig['host']),
                escapeshellarg($dbConfig['database']),
                escapeshellarg($backupPath)
            );

            $output = [];
            $resultCode = null;
            exec($command, $output, $resultCode);

            if ($resultCode === 0) {
                // Try to gzip it
                if (file_exists($backupPath)) {
                    $gzPath = $backupPath . '.gz';
                    $fp = gzopen($gzPath, 'w9');
                    gzwrite($fp, file_get_contents($backupPath));
                    gzclose($fp);
                    unlink($backupPath);
                    $this->info("Backup successfully saved to: {$gzPath}");
                } else {
                    $this->error("Backup file was not created.");
                    return 1;
                }
            } else {
                $this->error("mysqldump failed with exit code: {$resultCode}");
                return 1;
            }
        } else {
            $this->error("Backup for database connection type [{$connection}] is not supported.");
            return 1;
        }

        // Clean up backups older than retention days
        $this->cleanupOldBackups($backupDir);

        return 0;
    }

    /**
     * Clean up old backups based on retention setting.
     */
    protected function cleanupOldBackups(string $backupDir): void
    {
        $retentionDays = config('app.backup_retention_days', 7);
        $this->info("Cleaning up backups older than {$retentionDays} days...");

        $files = File::files($backupDir);
        $cutoffTime = time() - ($retentionDays * 24 * 60 * 60);

        $deletedCount = 0;
        foreach ($files as $file) {
            if ($file->getMTime() < $cutoffTime) {
                File::delete($file->getPathname());
                $deletedCount++;
            }
        }

        $this->info("Cleanup complete. Deleted {$deletedCount} old backup files.");
    }
}
