<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use Exception;

class MatchException extends Exception
{
    public static function alreadyPlayed(): self
    {
        return new self('Match has already been played.');
    }

    public static function cannotUpdateCompletedSeason(): self
    {
        return new self('Cannot update matches in a completed season.');
    }

    public static function invalidStatistics(string $message): self
    {
        return new self("Invalid match statistics: {$message}");
    }

    public static function simulationFailed(string $reason): self
    {
        return new self("Match simulation failed: {$reason}");
    }
}