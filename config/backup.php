<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Backup Configuration
    |--------------------------------------------------------------------------
    */

    'name' => env('BACKUP_NAME', 'laravel-backup'),

    /*
    |--------------------------------------------------------------------------
    | Backup Destination
    |--------------------------------------------------------------------------
    */

    'destination' => [
        'disk' => env('BACKUP_DISK', 'local'),
        'path' => env('BACKUP_PATH', 'backups'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Backup Configuration
    |--------------------------------------------------------------------------
    */

    'database' => [
        'enabled' => true,
        'connections' => [
            'mysql' => [
                'dump_command' => 'mysqldump',
                'binary_path' => env('MYSQL_BIN_PATH', ''),
                'timeout' => 60,
            ],
            'pgsql' => [
                'dump_command' => 'pg_dump',
                'binary_path' => env('PGSQL_BIN_PATH', ''),
                'timeout' => 60,
            ],
        ],
        'exclude_tables' => [],
        'include_tables' => [],
        'compress' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Files Backup Configuration
    |--------------------------------------------------------------------------
    */

    'files' => [
        'enabled' => true,
        'include' => [
            base_path('public'),
            base_path('storage'),
        ],
        'exclude' => [
            'node_modules',
            'vendor',
            '.git',
            'storage/logs/*',
            'storage/framework/cache/*',
        ],
        'compress' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cleanup Configuration
    |--------------------------------------------------------------------------
    */

    'cleanup' => [
        'enabled' => true,
        'max_age_in_days' => env('BACKUP_MAX_AGE', 30),
        'max_backups' => env('BACKUP_MAX_COUNT', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring Configuration
    |--------------------------------------------------------------------------
    */

    'monitoring' => [
        'enabled' => env('BACKUP_MONITORING', false),
        'email' => [
            'notifications' => true,
            'to' => env('BACKUP_NOTIFY_EMAIL', 'admin@example.com'),
        ],
        'slack' => [
            'notifications' => false,
            'webhook_url' => env('BACKUP_SLACK_WEBHOOK'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    */

    'queue' => [
        'enabled' => env('BACKUP_QUEUE', false),
        'connection' => env('BACKUP_QUEUE_CONNECTION', 'sync'),
        'queue' => env('BACKUP_QUEUE_NAME', 'default'),
    ],
];