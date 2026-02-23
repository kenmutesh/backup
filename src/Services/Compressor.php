<?php

namespace Ken\LaravelBackup\Services;

use Illuminate\Support\Facades\Storage;
use ZipArchive;

class Compressor
{
    /**
     * Compress backup directory
     */
    public function compress($backupPath)
    {
        $disk = Storage::disk(config('backup.destination.disk'));
        $sourcePath = storage_path('app/' . $backupPath);
        $zipFilename = $backupPath . '.zip';
        $zipPath = storage_path('app/' . $zipFilename);

        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $this->addDirectoryToZip($zip, $sourcePath, basename($backupPath));
            $zip->close();

            // Delete original directory
            $disk->deleteDirectory($backupPath);
        }

        return $zipFilename;
    }

    /**
     * Decompress backup
     */
    public function decompress($zipFile)
    {
        $disk = Storage::disk(config('backup.destination.disk'));
        $zipPath = storage_path('app/' . $zipFile);
        $extractPath = storage_path('app/' . dirname($zipFile));

        $zip = new ZipArchive();

        if ($zip->open($zipPath) === true) {
            $zip->extractTo($extractPath);
            $zip->close();

            // Delete zip file
            $disk->delete($zipFile);
        }

        return true;
    }

    /**
     * Add directory recursively to zip
     */
    protected function addDirectoryToZip($zip, $path, $relativePath)
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $zipPath = $relativePath . '/' . substr($filePath, strlen($path) + 1);
                $zip->addFile($filePath, $zipPath);
            }
        }
    }
}