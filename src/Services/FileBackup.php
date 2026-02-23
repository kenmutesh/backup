<?php

namespace Ken\LaravelBackup\Services;

use Illuminate\Support\Facades\Storage;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class FileBackup
{
    /**
     * Create files backup
     */
    public function backup($backupPath)
    {
        $filename = $backupPath . '/files_' . date('Y-m-d_H-i-s') . '.zip';
        $fullPath = storage_path('app/' . $filename);

        $zip = new ZipArchive();

        if ($zip->open($fullPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $this->addFilesToZip($zip, config('backup.files.include', []));
            $zip->close();
        }

        return $filename;
    }

    /**
     * Add files to zip archive
     */
    protected function addFilesToZip($zip, $directories)
    {
        $excludePatterns = config('backup.files.exclude', []);

        foreach ($directories as $directory) {
            if (!is_dir($directory)) {
                continue;
            }

            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($directory),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();

                    // Check if file should be excluded
                    if ($this->shouldExclude($filePath, $excludePatterns)) {
                        continue;
                    }

                    $relativePath = substr($filePath, strlen(base_path()) + 1);
                    $zip->addFile($filePath, $relativePath);
                }
            }
        }
    }

    /**
     * Check if file should be excluded
     */
    protected function shouldExclude($filePath, $patterns)
    {
        foreach ($patterns as $pattern) {
            if (strpos($filePath, $pattern) !== false) {
                return true;
            }
        }
        return false;
    }
}