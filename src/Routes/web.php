<?php

use Illuminate\Support\Facades\Route;
use Ken\LaravelBackup\Controllers\BackupController;

Route::group(['prefix' => 'backups', 'middleware' => ['web']], function () {
    Route::get('/', [BackupController::class, 'index'])->name('backups.index');
    Route::post('/', [BackupController::class, 'store'])->name('backups.store');
    Route::put('{backupId}/restore', [BackupController::class, 'update'])->name('backups.restore');
    Route::delete('{backupId}', [BackupController::class, 'destroy'])->name('backups.destroy');
    Route::get('{backupId}/download', [BackupController::class, 'download'])->name('backups.download');
});