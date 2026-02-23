<?php

use Illuminate\Support\Facades\Route;
use Ken\LaravelBackup\Controllers\BackupController;

Route::group(['prefix' => 'api/backups', 'middleware' => ['api']], function () {
    Route::get('/', [BackupController::class, 'index']);
    Route::post('/', [BackupController::class, 'store']);
    Route::put('{backupId}/restore', [BackupController::class, 'update']);
    Route::delete('{backupId}', [BackupController::class, 'destroy']);
    Route::get('{backupId}/download', [BackupController::class, 'download']);
});