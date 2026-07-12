<?php

namespace Modules\Superadmin\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Superadmin\Models\Backup;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

/**
 * BackupService — handles backup and restore operations.
 *
 * Creates backups containing database dumps and files.
 * Automatically creates pre-restore backups for safety.
 * Manages backup lifecycle (create, list, download, restore, delete).
 */
class BackupService
{
    /** Backup storage disk name */
    const BACKUP_DISK = 'backups';
    
    /** Default backup directory within storage */
    const BACKUP_DIR = 'backups';

    /** Files and directories to include in file backups */
    const BACKUP_PATHS = [
        'storage/app/public',
        'storage/app/uploads',
        'public/uploads',
        '.env',
    ];

    /**
     * Create a new backup.
     *
     * @param string $type Type of backup: manual, scheduled, pre-restore
     * @param bool $includeDatabase Whether to include database dump
     * @param bool $includeFiles Whether to include files
     * @return Backup
     * @throws \Exception if backup creation fails
     */
    public function create(
        string $type = 'manual',
        bool $includeDatabase = true,
        bool $includeFiles = true
    ): Backup {
        try {
            // Generate unique filename with timestamp
            $timestamp = now()->format('Y-m-d_His');
            $filename = "backup_{$timestamp}_" . Str::random(8) . ".zip";
            $tempPath = storage_path("app/temp/{$filename}");
            
            // Ensure temp directory exists
            $this->ensureDirectoryExists(dirname($tempPath));
            
            // Create ZIP archive
            $zip = new ZipArchive();
            
            if ($zip->open($tempPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \Exception("Failed to create ZIP archive at {$tempPath}");
            }

            // Add database dump if requested
            if ($includeDatabase) {
                $this->addDatabaseToZip($zip);
            }

            // Add files if requested
            if ($includeFiles) {
                $this->addFilesToZip($zip);
            }

            $zip->close();

            // Get file size
            $size = filesize($tempPath);

            // Move to permanent storage
            $backupDisk = Storage::disk(self::BACKUP_DISK);
            $relativePath = self::BACKUP_DIR . '/' . $filename;
            
            // Ensure backup directory exists
            if (!$backupDisk->exists(self::BACKUP_DIR)) {
                $backupDisk->makeDirectory(self::BACKUP_DIR);
            }
            
            // Move file to backup storage
            $backupDisk->put($relativePath, file_get_contents($tempPath));
            
            // Clean up temp file
            @unlink($tempPath);

            // Create database record
            $backup = Backup::create([
                'filename' => $filename,
                'path' => $relativePath,
                'size' => $size,
                'type' => $type,
                'includes_database' => $includeDatabase,
                'includes_files' => $includeFiles,
                'metadata' => [
                    'php_version' => PHP_VERSION,
                    'laravel_version' => app()->version(),
                    'app_env' => config('app.env'),
                    'created_from_ip' => request()->ip(),
                ],
                'created_by' => auth()->id(),
                'created_at' => now(),
            ]);

            Log::info("Backup created successfully", [
                'backup_id' => $backup->id,
                'filename' => $filename,
                'size' => $backup->formatted_size,
                'type' => $type,
            ]);

            return $backup;
            
        } catch (\Throwable $e) {
            // Clean up temp file if it exists
            if (isset($tempPath) && file_exists($tempPath)) {
                @unlink($tempPath);
            }
            
            Log::error("Backup creation failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'type' => $type,
            ]);
            
            throw new \Exception("Backup creation failed: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * List all available backups.
     *
     * @param array $filters Optional filters (type, days, etc.)
     * @return Collection
     */
    public function list(array $filters = []): Collection
    {
        $query = Backup::query()->with('creator')->ordered();

        // Apply filters
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['days'])) {
            $query->recent((int) $filters['days']);
        }

        if (!empty($filters['created_by'])) {
            $query->where('created_by', $filters['created_by']);
        }

        return $query->get();
    }

    /**
     * Download a backup file.
     *
     * @param string $filename The backup filename
     * @return StreamedResponse
     * @throws \Exception if backup not found or file doesn't exist
     */
    public function download(string $filename): StreamedResponse
    {
        $backup = Backup::where('filename', $filename)->firstOrFail();

        $backupDisk = Storage::disk(self::BACKUP_DISK);
        
        if (!$backupDisk->exists($backup->path)) {
            throw new \Exception("Backup file not found on disk: {$backup->path}");
        }

        Log::info("Backup download initiated", [
            'backup_id' => $backup->id,
            'filename' => $filename,
            'user_id' => auth()->id(),
        ]);

        return $backupDisk->download($backup->path, $filename);
    }

    /**
     * Restore a backup.
     *
     * IMPORTANT: This creates a pre-restore backup automatically before restoring.
     *
     * @param string $filename The backup filename to restore
     * @return bool True if restore was successful
     * @throws \Exception if restore fails
     */
    public function restore(string $filename): bool
    {
        $backup = Backup::where('filename', $filename)->firstOrFail();

        try {
            // CRITICAL: Create pre-restore backup first (Requirement 14.7)
            Log::info("Creating pre-restore backup before restoring {$filename}");
            $preRestoreBackup = $this->createPreRestoreBackup();

            $backupDisk = Storage::disk(self::BACKUP_DISK);
            
            if (!$backupDisk->exists($backup->path)) {
                throw new \Exception("Backup file not found: {$backup->path}");
            }

            // Extract ZIP to temp location
            $tempExtractPath = storage_path('app/temp/restore_' . time());
            $this->ensureDirectoryExists($tempExtractPath);

            $zipPath = $backupDisk->path($backup->path);
            $zip = new ZipArchive();
            
            if ($zip->open($zipPath) !== true) {
                throw new \Exception("Failed to open backup ZIP file");
            }

            $zip->extractTo($tempExtractPath);
            $zip->close();

            // Restore database if included
            if ($backup->includes_database) {
                $this->restoreDatabase($tempExtractPath);
            }

            // Restore files if included
            if ($backup->includes_files) {
                $this->restoreFiles($tempExtractPath);
            }

            // Clean up temp directory
            $this->deleteDirectory($tempExtractPath);

            Log::info("Backup restored successfully", [
                'backup_id' => $backup->id,
                'filename' => $filename,
                'pre_restore_backup_id' => $preRestoreBackup->id,
                'user_id' => auth()->id(),
            ]);

            return true;
            
        } catch (\Throwable $e) {
            // Clean up temp directory if it exists
            if (isset($tempExtractPath) && is_dir($tempExtractPath)) {
                $this->deleteDirectory($tempExtractPath);
            }
            
            Log::error("Backup restore failed", [
                'backup_id' => $backup->id,
                'filename' => $filename,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            throw new \Exception("Restore failed: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Delete a backup.
     *
     * @param string $filename The backup filename to delete
     * @return bool True if deletion was successful
     * @throws \Exception if deletion fails
     */
    public function delete(string $filename): bool
    {
        $backup = Backup::where('filename', $filename)->firstOrFail();

        try {
            $backupDisk = Storage::disk(self::BACKUP_DISK);
            
            // Delete file from storage if it exists
            if ($backupDisk->exists($backup->path)) {
                $backupDisk->delete($backup->path);
            }

            // Delete database record
            $backup->delete();

            Log::info("Backup deleted successfully", [
                'backup_id' => $backup->id,
                'filename' => $filename,
                'user_id' => auth()->id(),
            ]);

            return true;
            
        } catch (\Throwable $e) {
            Log::error("Backup deletion failed", [
                'backup_id' => $backup->id,
                'filename' => $filename,
                'error' => $e->getMessage(),
            ]);
            
            throw new \Exception("Failed to delete backup: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Get metadata for a backup.
     *
     * @param string $filename
     * @return array
     */
    public function getMetadata(string $filename): array
    {
        $backup = Backup::where('filename', $filename)->firstOrFail();

        return [
            'id' => $backup->id,
            'filename' => $backup->filename,
            'size' => $backup->size,
            'formatted_size' => $backup->formatted_size,
            'type' => $backup->type,
            'type_label' => $backup->type_label,
            'includes_database' => $backup->includes_database,
            'includes_files' => $backup->includes_files,
            'contents_description' => $backup->contents_description,
            'created_at' => $backup->created_at->toIso8601String(),
            'age' => $backup->age,
            'created_by' => $backup->created_by,
            'creator_name' => $backup->creator?->name,
            'metadata' => $backup->metadata,
            'exists_on_disk' => $backup->exists(),
        ];
    }

    /**
     * Delete old backups (older than specified days).
     *
     * @param int $days Delete backups older than this many days
     * @param string|null $type Optional: only delete backups of this type
     * @return int Number of backups deleted
     */
    public function deleteOldBackups(int $days = 30, ?string $type = null): int
    {
        $query = Backup::where('created_at', '<', now()->subDays($days));

        if ($type) {
            $query->where('type', $type);
        }

        $backups = $query->get();
        $count = 0;

        foreach ($backups as $backup) {
            try {
                $this->delete($backup->filename);
                $count++;
            } catch (\Exception $e) {
                Log::warning("Failed to delete old backup", [
                    'backup_id' => $backup->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info("Deleted {$count} old backups", [
            'days' => $days,
            'type' => $type,
        ]);

        return $count;
    }

    // =========================================================================
    // Private Helper Methods
    // =========================================================================

    /**
     * Create a pre-restore backup automatically.
     */
    private function createPreRestoreBackup(): Backup
    {
        return $this->create(
            type: 'pre-restore',
            includeDatabase: true,
            includeFiles: true
        );
    }

    /**
     * Add database dump to ZIP archive.
     */
    private function addDatabaseToZip(ZipArchive $zip): void
    {
        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");

        $sqlFile = storage_path('app/temp/database_' . time() . '.sql');

        try {
            if ($driver === 'mysql') {
                $this->dumpMysqlDatabase($sqlFile);
            } elseif ($driver === 'pgsql') {
                $this->dumpPostgresDatabase($sqlFile);
            } elseif ($driver === 'sqlite') {
                $this->dumpSqliteDatabase($sqlFile);
            } else {
                throw new \Exception("Unsupported database driver: {$driver}");
            }

            $zip->addFile($sqlFile, 'database.sql');
            
        } finally {
            // Cleanup will happen after ZIP is closed
        }
    }

    /**
     * Dump MySQL database to SQL file.
     */
    private function dumpMysqlDatabase(string $outputFile): void
    {
        $connection = config('database.default');
        $host = config("database.connections.{$connection}.host");
        $port = config("database.connections.{$connection}.port", 3306);
        $database = config("database.connections.{$connection}.database");
        $username = config("database.connections.{$connection}.username");
        $password = config("database.connections.{$connection}.password");

        $command = sprintf(
            'mysqldump --host=%s --port=%d --user=%s --password=%s %s > %s 2>&1',
            escapeshellarg($host),
            $port,
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg($outputFile)
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception("MySQL dump failed with code {$returnCode}: " . implode("\n", $output));
        }
    }

    /**
     * Dump PostgreSQL database to SQL file.
     */
    private function dumpPostgresDatabase(string $outputFile): void
    {
        $connection = config('database.default');
        $host = config("database.connections.{$connection}.host");
        $port = config("database.connections.{$connection}.port", 5432);
        $database = config("database.connections.{$connection}.database");
        $username = config("database.connections.{$connection}.username");
        $password = config("database.connections.{$connection}.password");

        putenv("PGPASSWORD={$password}");

        $command = sprintf(
            'pg_dump --host=%s --port=%d --username=%s --dbname=%s --file=%s 2>&1',
            escapeshellarg($host),
            $port,
            escapeshellarg($username),
            escapeshellarg($database),
            escapeshellarg($outputFile)
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception("PostgreSQL dump failed with code {$returnCode}: " . implode("\n", $output));
        }
    }

    /**
     * Dump SQLite database to SQL file.
     */
    private function dumpSqliteDatabase(string $outputFile): void
    {
        $connection = config('database.default');
        $databasePath = config("database.connections.{$connection}.database");

        if (!file_exists($databasePath)) {
            throw new \Exception("SQLite database file not found: {$databasePath}");
        }

        // For SQLite, we can just copy the database file
        copy($databasePath, $outputFile);
    }

    /**
     * Add files to ZIP archive.
     */
    private function addFilesToZip(ZipArchive $zip): void
    {
        $basePath = base_path();

        foreach (self::BACKUP_PATHS as $path) {
            $fullPath = $basePath . '/' . $path;

            if (is_file($fullPath)) {
                $zip->addFile($fullPath, 'files/' . $path);
            } elseif (is_dir($fullPath)) {
                $this->addDirectoryToZip($zip, $fullPath, 'files/' . $path);
            }
        }
    }

    /**
     * Recursively add directory to ZIP.
     */
    private function addDirectoryToZip(ZipArchive $zip, string $sourcePath, string $zipPath): void
    {
        if (!is_dir($sourcePath)) {
            return;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourcePath, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($sourcePath) + 1);
                $zip->addFile($filePath, $zipPath . '/' . $relativePath);
            }
        }
    }

    /**
     * Restore database from extracted backup.
     */
    private function restoreDatabase(string $extractPath): void
    {
        $sqlFile = $extractPath . '/database.sql';

        if (!file_exists($sqlFile)) {
            throw new \Exception("Database dump file not found in backup");
        }

        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");

        if ($driver === 'mysql') {
            $this->restoreMysqlDatabase($sqlFile);
        } elseif ($driver === 'pgsql') {
            $this->restorePostgresDatabase($sqlFile);
        } elseif ($driver === 'sqlite') {
            $this->restoreSqliteDatabase($sqlFile);
        } else {
            throw new \Exception("Unsupported database driver for restore: {$driver}");
        }
    }

    /**
     * Restore MySQL database from SQL file.
     */
    private function restoreMysqlDatabase(string $sqlFile): void
    {
        $connection = config('database.default');
        $host = config("database.connections.{$connection}.host");
        $port = config("database.connections.{$connection}.port", 3306);
        $database = config("database.connections.{$connection}.database");
        $username = config("database.connections.{$connection}.username");
        $password = config("database.connections.{$connection}.password");

        $command = sprintf(
            'mysql --host=%s --port=%d --user=%s --password=%s %s < %s 2>&1',
            escapeshellarg($host),
            $port,
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg($sqlFile)
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception("MySQL restore failed with code {$returnCode}: " . implode("\n", $output));
        }

        // Reconnect to database after restore
        DB::reconnect();
    }

    /**
     * Restore PostgreSQL database from SQL file.
     */
    private function restorePostgresDatabase(string $sqlFile): void
    {
        $connection = config('database.default');
        $host = config("database.connections.{$connection}.host");
        $port = config("database.connections.{$connection}.port", 5432);
        $database = config("database.connections.{$connection}.database");
        $username = config("database.connections.{$connection}.username");
        $password = config("database.connections.{$connection}.password");

        putenv("PGPASSWORD={$password}");

        $command = sprintf(
            'psql --host=%s --port=%d --username=%s --dbname=%s --file=%s 2>&1',
            escapeshellarg($host),
            $port,
            escapeshellarg($username),
            escapeshellarg($database),
            escapeshellarg($sqlFile)
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception("PostgreSQL restore failed with code {$returnCode}: " . implode("\n", $output));
        }

        DB::reconnect();
    }

    /**
     * Restore SQLite database from file.
     */
    private function restoreSqliteDatabase(string $sqlFile): void
    {
        $connection = config('database.default');
        $databasePath = config("database.connections.{$connection}.database");

        // Backup current database first
        if (file_exists($databasePath)) {
            copy($databasePath, $databasePath . '.bak');
        }

        // Replace with restored database
        copy($sqlFile, $databasePath);

        DB::reconnect();
    }

    /**
     * Restore files from extracted backup.
     */
    private function restoreFiles(string $extractPath): void
    {
        $filesPath = $extractPath . '/files';

        if (!is_dir($filesPath)) {
            Log::warning("Files directory not found in backup, skipping file restore");
            return;
        }

        $basePath = base_path();

        foreach (self::BACKUP_PATHS as $path) {
            $sourcePath = $filesPath . '/' . $path;
            $destPath = $basePath . '/' . $path;

            if (is_file($sourcePath)) {
                // Ensure destination directory exists
                $this->ensureDirectoryExists(dirname($destPath));
                copy($sourcePath, $destPath);
            } elseif (is_dir($sourcePath)) {
                $this->copyDirectory($sourcePath, $destPath);
            }
        }
    }

    /**
     * Recursively copy directory.
     */
    private function copyDirectory(string $source, string $destination): void
    {
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($files as $file) {
            $targetPath = $destination . '/' . substr($file->getRealPath(), strlen($source) + 1);

            if ($file->isDir()) {
                if (!is_dir($targetPath)) {
                    mkdir($targetPath, 0755, true);
                }
            } else {
                copy($file->getRealPath(), $targetPath);
            }
        }
    }

    /**
     * Recursively delete directory.
     */
    private function deleteDirectory(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }

        rmdir($path);
    }

    /**
     * Ensure directory exists, create if not.
     */
    private function ensureDirectoryExists(string $path): void
    {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }
}
