<?php

namespace Ken\LaravelBackup\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BackupCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $backupData;

    public function __construct($backupData)
    {
        $this->backupData = $backupData;
    }
}