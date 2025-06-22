<?php

declare(strict_types=1);

namespace App\Domain\DTOs;

use App\Domain\ValueObjects\Team;

final readonly class LeagueTableEntry
{
    public function __construct(
        public int $position,
        public Team $team,
        public int $points,
        public int $played,
        public int $goalsFor,
        public int $goalsAgainst,
        public int $goalDifference,
        public float $attackStrength,
        public float $defenseStrength,
    ) {}

    public function getForm(): float
    {
        return ($this->attackStrength + $this->defenseStrength) / 2;
    }
}