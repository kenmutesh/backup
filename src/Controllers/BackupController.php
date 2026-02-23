<?php

namespace Ken\LaravelBackup\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Ken\LaravelBackup\Services\BackupService;
use Ken\LaravelBackup\Exceptions\BackupException;

class BackupController extends Controller
{
    protected $backupService;

    public function __construct(BackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    /**
     * Display all backups
     */
    public function index()
    {
        try {
            $backups = $this->backupService->getAll();

            if (request()->wantsJson()) {
                return response()->json(['backups' => $backups]);
            }

            return view('backup::backup-status', ['backups' => $backups]);

        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Create a new backup
     */
    public function store(Request $request)
    {
        try {
            $options = $request->only(['database', 'files']);
            $backup = $this->backupService->create($options);

            return response()->json([
                'message' => 'Backup created successfully',
                'backup' => $backup,
            ], 201);

        } catch (BackupException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Restore from backup
     */
    public function update(Request $request, $backupId)
    {
        try {
            $this->backupService->restore($backupId);

            return response()->json([
                'message' => 'Backup restored successfully',
            ]);

        } catch (BackupException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete a backup
     */
    public function destroy($backupId)
    {
        try {
            $deleted = $this->backupService->delete($backupId);

            if ($deleted) {
                return response()->json([
                    'message' => 'Backup deleted successfully',
                ]);
            }

            return response()->json(['error' => 'Backup not found'], 404);

        } catch (BackupException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Download a backup
     */
    public function download($backupId)
    {
        try {
            $disk = config('backup.destination.disk');
            $path = config('backup.destination.path') . '/' . $backupId . '.zip';

            if (!Storage::disk($disk)->exists($path)) {
                return response()->json(['error' => 'Backup file not found'], 404);
            }

            return Storage::disk($disk)->download($path);

        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Handle errors
     */
    protected function handleError(\Exception $e)
    {
        if (request()->wantsJson()) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return back()->withError($e->getMessage());
    }
}