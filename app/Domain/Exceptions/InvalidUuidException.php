<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use Exception;

class InvalidUuidException extends Exception
{
    public static function forParameter(string $parameter, string $value): self
    {
        return new self("Invalid UUID format for parameter '{$parameter}': {$value}");
    }
}