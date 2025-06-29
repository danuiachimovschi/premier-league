<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Config;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
        
        // In testing, use the same in-memory database for both read and write operations
        // This ensures data consistency during tests while still testing the CQRS structure
        $this->configureDatabaseForTesting();
    }

    protected function configureDatabaseForTesting(): void
    {
        // For testing, use the main database connection for both read and write
        // This ensures data consistency while preserving the CQRS structure
        
        $mainConnection = config('database.connections.sqlite');
        
        Config::set('database.connections.sqlite_read', $mainConnection);
        Config::set('database.connections.sqlite_write', $mainConnection);
        
        // For MySQL testing with Docker
        if (config('database.default') === 'mysql') {
            Config::set('database.connections.mysql_read', [
                'driver' => 'mysql',
                'host' => env('DB_READ_HOST', env('DB_HOST', '127.0.0.1')),
                'port' => env('DB_READ_PORT', env('DB_PORT', '3306')),
                'database' => env('DB_READ_DATABASE', env('DB_DATABASE', 'testing')),
                'username' => env('DB_READ_USERNAME', env('DB_USERNAME', 'root')),
                'password' => env('DB_READ_PASSWORD', env('DB_PASSWORD', '')),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => true,
                'engine' => null,
            ]);

            Config::set('database.connections.mysql_write', [
                'driver' => 'mysql',
                'host' => env('DB_WRITE_HOST', env('DB_HOST', '127.0.0.1')),
                'port' => env('DB_WRITE_PORT', env('DB_PORT', '3306')),
                'database' => env('DB_WRITE_DATABASE', env('DB_DATABASE', 'testing')),
                'username' => env('DB_WRITE_USERNAME', env('DB_USERNAME', 'root')),
                'password' => env('DB_WRITE_PASSWORD', env('DB_PASSWORD', '')),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => true,
                'engine' => null,
            ]);
        }
    }
}