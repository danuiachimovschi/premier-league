<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use Illuminate\Database\Eloquent\Model;

abstract class BaseReadRepository
{
    protected function getConnection(): string
    {
        if (app()->environment('testing')) {
            return config('database.default');
        }
        
        $driver = config('database.default');
        return match($driver) {
            'mysql' => 'mysql_read',
            'sqlite' => 'sqlite_read',
            default => $driver . '_read'
        };
    }

    protected function setModelConnection(Model $model): Model
    {
        $model->setConnection($this->getConnection());
        return $model;
    }
}