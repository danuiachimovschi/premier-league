<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use Illuminate\Database\Eloquent\Model;

abstract class BaseWriteRepository
{
    protected function getConnection(): string
    {
        if (app()->environment('testing')) {
            return config('database.default');
        }
        
        $driver = config('database.default');
        return match($driver) {
            'mysql' => 'mysql_write',
            'sqlite' => 'sqlite_write',
            default => $driver . '_write'
        };
    }

    protected function setModelConnection(Model $model): Model
    {
        $model->setConnection($this->getConnection());
        return $model;
    }
}