<?php

declare(strict_types=1);

namespace App\Domain\DTOs;

use App\Domain\ValueObjects\Team;
use App\Domain\ValueObjects\Goals;
use DateTimeImmutable;

final readonly class MatchResult
{
    public function __construct(
        public Team $homeTeam,
        public Team $awayTeam,
        public Goals $homeGoals,
        public Goals $awayGoals,
        public MatchStatistics $statistics,
        public DateTimeImmutable $playedAt = new DateTimeImmutable(),
    ) {}

    public function isHomeWin(): bool
    {
        return $this->homeGoals->isGreaterThan($this->awayGoals);
    }

    public function isAwayWin(): bool
    {
        return $this->awayGoals->isGreaterThan($this->homeGoals);
    }

    public function isDraw(): bool
    {
        return $this->homeGoals->equals($this->awayGoals);
    }
}