<?php

declare(strict_types=1);

namespace App\Domain\DTOs;

use App\Domain\ValueObjects\Team;
use App\Domain\ValueObjects\Points;
use App\Domain\ValueObjects\Goals;

final class TeamStatistics
{
    public function __construct(
        public readonly Team $team,
        public Points $points = new Points(0),
        public Goals $goalsScored = new Goals(0),
        public Goals $goalsConceded = new Goals(0),
        public int $gamesPlayed = 0,
        public float $shotsPerGame = 0.0,
        public float $shotsOnTargetPerGame = 0.0,
        public float $averagePossession = 0.0,
        public float $attackStrength = 1.0,
        public float $defenseStrength = 1.0,
    ) {}

    public function getGoalDifference(): int
    {
        return $this->goalsScored->getValue() - $this->goalsConceded->getValue();
    }

    public function getPointsPerGame(): float
    {
        return $this->gamesPlayed > 0 
            ? $this->points->getValue() / $this->gamesPlayed 
            : 0.0;
    }

    public function withUpdatedStrengths(float $attackStrength, float $defenseStrength): self
    {
        return new self(
            team: $this->team,
            points: $this->points,
            goalsScored: $this->goalsScored,
            goalsConceded: $this->goalsConceded,
            gamesPlayed: $this->gamesPlayed,
            shotsPerGame: $this->shotsPerGame,
            shotsOnTargetPerGame: $this->shotsOnTargetPerGame,
            averagePossession: $this->averagePossession,
            attackStrength: $attackStrength,
            defenseStrength: $defenseStrength,
        );
    }
}