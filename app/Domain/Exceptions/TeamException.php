<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use Exception;

class TeamException extends Exception
{
    public static function notFound(string $teamId): self
    {
        return new self("Team with ID {$teamId} not found.");
    }

    public static function insufficientTeams(int $required, int $available): self
    {
        return new self("Insufficient teams. Required: {$required}, Available: {$available}.");
    }

    public static function invalidStrength(float $strength): self
    {
        return new self("Invalid team strength: {$strength}. Must be between 0 and 5.");
    }
}