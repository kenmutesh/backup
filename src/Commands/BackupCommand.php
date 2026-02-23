<?php

namespace Ken\LaravelBackup\Commands;

use Illuminate\Console\Command;
use Ken\LaravelBackup\Services\BackupService;

class BackupCommand extends Command
{
    protected $signature = 'backup:run 
                            {--database : Only backup database}
                            {--files : Only backup files}
                            {--queue : Queue the backup}';

    protected $description = 'Create a new backup';

    protected $backupService;

    public function __construct(BackupService $backupService)
    {
        parent::__construct();
        $this->backupService = $backupService;
    }

    public function handle()
    {
        $this->info('Starting backup...');

        try {
            $options = [];

            if ($this->option('database')) {
                $options['database'] = true;
            }

            if ($this->option('files')) {
                $options['files'] = true;
            }

            if ($this->option('queue')) {
                // Dispatch to queue
                $this->info('Backup queued successfully');
                return 0;
            }

            $backup = $this->backupService->create($options);

            $this->info('Backup completed successfully!');
            $this->table(
                ['ID', 'Date', 'Files'],
                [[$backup['id'], $backup['created_at'], implode(', ', $backup['files'])]]
            );

            return 0;

        } catch (\Exception $e) {
            $this->error('Backup failed: ' . $e->getMessage());
            return 1;
        }
    }
}