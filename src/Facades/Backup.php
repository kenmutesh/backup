<?php

namespace Ken\LaravelBackup\Facades;

use Illuminate\Support\Facades\Facade;

class Backup extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel-backup';
    }
}