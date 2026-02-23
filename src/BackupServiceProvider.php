<?php

namespace Ken\LaravelBackup;

use Illuminate\Support\ServiceProvider;
use Ken\LaravelBackup\Commands\BackupCommand;
use Ken\LaravelBackup\Commands\RestoreCommand;
use Ken\LaravelBackup\Commands\CleanCommand;
use Ken\LaravelBackup\Services\BackupService;

class BackupServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/backup.php', 'backup'
        );

        $this->app->singleton('laravel-backup', function ($app) {
            return new BackupService();
        });

        $this->app->alias('laravel-backup', BackupService::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish configuration
        $this->publishes([
            __DIR__.'/../config/backup.php' => config_path('backup.php'),
        ], 'backup-config');

        // Publish migrations
        $this->publishes([
            __DIR__.'/Database/migrations/' => database_path('migrations'),
        ], 'backup-migrations');

        // Publish views
        $this->publishes([
            __DIR__.'/Resources/views' => resource_path('views/vendor/backup'),
        ], 'backup-views');

        // Load routes
        $this->loadRoutesFrom(__DIR__.'/Routes/api.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/Database/migrations');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                BackupCommand::class,
                RestoreCommand::class,
                CleanCommand::class,
            ]);
        }
    }
}