<?php

namespace Ken\LaravelBackup\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Spatie\DbDumper\Databases\MySql;
use Spatie\DbDumper\Databases\PostgreSql;
use Spatie\DbDumper\Databases\Sqlite;

class DatabaseBackup
{
    /**
     * Create database backup
     */
    public function backup($backupPath)
    {
        $connection = Config::get('database.default');
        $database = Config::get("database.connections.{$connection}.database");

        $filename = $backupPath . '/database_' . date('Y-m-d_H-i-s') . '.sql';
        $fullPath = storage_path('app/' . $filename);

        switch ($connection) {
            case 'mysql':
                $this->backupMysql($database, $fullPath);
                break;
            case 'pgsql':
                $this->backupPostgresql($database, $fullPath);
                break;
            case 'sqlite':
                $this->backupSqlite($database, $fullPath);
                break;
        }

        return $filename;
    }

    /**
     * Restore database from backup
     */
    public function restore($backupFile)
    {
        $connection = Config::get('database.default');
        $fullPath = storage_path('app/' . $backupFile);

        switch ($connection) {
            case 'mysql':
                $this->restoreMysql($fullPath);
                break;
            case 'pgsql':
                $this->restorePostgresql($fullPath);
                break;
            case 'sqlite':
                $this->restoreSqlite($fullPath);
                break;
        }

        return true;
    }

    protected function backupMysql($database, $path)
    {
        MySql::create()
            ->setDbName($database)
            ->setUserName(Config::get('database.connections.mysql.username'))
            ->setPassword(Config::get('database.connections.mysql.password'))
            ->setHost(Config::get('database.connections.mysql.host'))
            ->setPort(Config::get('database.connections.mysql.port'))
            ->excludeTables(config('backup.database.exclude_tables', []))
            ->includeTables(config('backup.database.include_tables', []))
            ->dumpToFile($path);
    }

    protected function backupPostgresql($database, $path)
    {
        PostgreSql::create()
            ->setDbName($database)
            ->setUserName(Config::get('database.connections.pgsql.username'))
            ->setPassword(Config::get('database.connections.pgsql.password'))
            ->setHost(Config::get('database.connections.pgsql.host'))
            ->setPort(Config::get('database.connections.pgsql.port'))
            ->dumpToFile($path);
    }

    protected function backupSqlite($database, $path)
    {
        Sqlite::create()
            ->setDbName($database)
            ->dumpToFile($path);
    }

    protected function restoreMysql($path)
    {
        DB::unprepared(file_get_contents($path));
    }

    protected function restorePostgresql($path)
    {
        // PostgreSQL restore logic
        $command = sprintf(
            'psql -h %s -U %s -d %s < %s',
            Config::get('database.connections.pgsql.host'),
            Config::get('database.connections.pgsql.username'),
            Config::get('database.connections.pgsql.database'),
            $path
        );
        exec($command);
    }

    protected function restoreSqlite($path)
    {
        // SQLite restore logic
        DB::unprepared(file_get_contents($path));
    }
}