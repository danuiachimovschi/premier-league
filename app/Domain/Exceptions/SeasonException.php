<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use Exception;

class SeasonException extends Exception
{
    public static function notActive(): self
    {
        return new self('Season is not active.');
    }

    public static function alreadyCompleted(): self
    {
        return new self('Season is already completed.');
    }

    public static function insufficientTeams(): self
    {
        return new self('Season must have exactly 4 teams to generate matches.');
    }

    public static function invalidWeek(int $week, int $maxWeek): self
    {
        return new self("Invalid week {$week}. Week must be between 1 and {$maxWeek}.");
    }

    public static function noMatchesFound(int $week): self
    {
        return new self("No matches found for week {$week}.");
    }
}