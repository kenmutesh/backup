<?php

namespace Ken\LaravelBackup\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Ken\LaravelBackup\Events\BackupCompleted;
use Ken\LaravelBackup\Events\BackupFailed;
use Ken\LaravelBackup\Exceptions\BackupException;

class BackupService
{
    protected $config;
    protected $backupId;
    protected $backupPath;
    protected $databaseBackup;
    protected $fileBackup;
    protected $compressor;

    public function __construct()
    {
        $this->config = config('backup');
        $this->databaseBackup = new DatabaseBackup();
        $this->fileBackup = new FileBackup();
        $this->compressor = new Compressor();
    }

    /**
     * Create a new backup
     */
    public function create(array $options = [])
    {
        try {
            $this->backupId = Carbon::now()->format('Y-m-d_H-i-s');
            $this->backupPath = $this->config['destination']['path'] . '/' . $this->backupId;

            // Create backup directory
            Storage::disk($this->config['destination']['disk'])->makeDirectory($this->backupPath);

            $backupData = [
                'id' => $this->backupId,
                'created_at' => Carbon::now(),
                'files' => [],
            ];

            // Database backup
            if ($this->config['database']['enabled'] ?? true) {
                $databaseFile = $this->databaseBackup->backup($this->backupPath);
                $backupData['files']['database'] = $databaseFile;
            }

            // Files backup
            if ($this->config['files']['enabled'] ?? true) {
                $filesArchive = $this->fileBackup->backup($this->backupPath);
                $backupData['files']['archive'] = $filesArchive;
            }

            // Compress backup
            if ($this->config['files']['compress'] ?? true) {
                $compressedFile = $this->compressor->compress($this->backupPath);
                $backupData['files']['compressed'] = $compressedFile;
            }

            // Save backup record
            $this->saveBackupRecord($backupData);

            // Cleanup old backups
            if ($this->config['cleanup']['enabled'] ?? true) {
                $this->cleanup();
            }

            event(new BackupCompleted($backupData));

            return $backupData;

        } catch (\Exception $e) {
            event(new BackupFailed($e->getMessage()));
            throw new BackupException('Backup failed: ' . $e->getMessage());
        }
    }

    /**
     * Restore from backup
     */
    public function restore($backupId)
    {
        try {
            $backupPath = $this->config['destination']['path'] . '/' . $backupId;

            if (!Storage::disk($this->config['destination']['disk'])->exists($backupPath)) {
                throw new BackupException('Backup not found: ' . $backupId);
            }

            // Decompress if needed
            $files = Storage::disk($this->config['destination']['disk'])->files($backupPath);

            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'zip') {
                    $this->compressor->decompress($file);
                } elseif (strpos($file, '.sql') !== false) {
                    $this->databaseBackup->restore($file);
                }
            }

            return true;

        } catch (\Exception $e) {
            throw new BackupException('Restore failed: ' . $e->getMessage());
        }
    }

    /**
     * Get all backups
     */
    public function getAll()
    {
        $backups = [];
        $disk = Storage::disk($this->config['destination']['disk']);
        $directories = $disk->directories($this->config['destination']['path']);

        foreach ($directories as $directory) {
            $backupId = basename($directory);
            $files = $disk->files($directory);
            $size = 0;

            foreach ($files as $file) {
                $size += $disk->size($file);
            }

            $backups[] = [
                'id' => $backupId,
                'date' => Carbon::createFromFormat('Y-m-d_H-i-s', $backupId),
                'size' => $this->formatBytes($size),
                'files' => $files,
            ];
        }

        return $backups;
    }

    /**
     * Delete a backup
     */
    public function delete($backupId)
    {
        $disk = Storage::disk($this->config['destination']['disk']);
        $backupPath = $this->config['destination']['path'] . '/' . $backupId;

        if ($disk->exists($backupPath)) {
            return $disk->deleteDirectory($backupPath);
        }

        return false;
    }

    /**
     * Clean up old backups
     */
    protected function cleanup()
    {
        $backups = $this->getAll();
        $maxAge = $this->config['cleanup']['max_age_in_days'];
        $maxBackups = $this->config['cleanup']['max_backups'];

        // Remove by age
        foreach ($backups as $backup) {
            $ageInDays = $backup['date']->diffInDays(Carbon::now());
            if ($ageInDays > $maxAge) {
                $this->delete($backup['id']);
            }
        }

        // Keep only max number of backups
        if (count($backups) > $maxBackups) {
            $backupsToDelete = array_slice($backups, $maxBackups);
            foreach ($backupsToDelete as $backup) {
                $this->delete($backup['id']);
            }
        }
    }

    /**
     * Save backup record to database
     */
    protected function saveBackupRecord($data)
    {
        if (config('backup.monitoring.enabled')) {
            DB::table('backups')->insert([
                'backup_id' => $data['id'],
                'created_at' => $data['created_at'],
                'data' => json_encode($data),
            ]);
        }
    }

    /**
     * Format bytes to human readable format
     */
    protected function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}