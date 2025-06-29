<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Domain\DTOs\TeamStatistics;
use App\Domain\Repositories\TeamReadRepositoryInterface;
use App\Domain\ValueObjects\Goals;
use App\Domain\ValueObjects\Points;
use App\Domain\ValueObjects\Team;

final class TeamStatisticsRepositoryAdapter
{
    private array $sessionStats = [];

    public function __construct(
        private readonly ?TeamReadRepositoryInterface $teamRepository = null,
        private readonly float $initialStrength = 1.0
    ) {}

    public function initializeTeams(array $teamNames): void
    {
        foreach ($teamNames as $teamName) {
            $team = new Team($teamName);
            $this->sessionStats[$teamName] = new TeamStatistics(
                team: $team,
                attackStrength: $this->initialStrength,
                defenseStrength: $this->initialStrength,
            );
        }
    }

    public function get(Team $team): TeamStatistics
    {
        $teamName = $team->getName();
        
        if (!isset($this->sessionStats[$teamName])) {
            throw new \InvalidArgumentException("Team {$teamName} not found");
        }

        return $this->sessionStats[$teamName];
    }

    public function getAll(): array
    {
        return array_values($this->sessionStats);
    }

    public function update(TeamStatistics $statistics): void
    {
        $this->sessionStats[$statistics->team->getName()] = $statistics;
    }

    public function getTeams(): array
    {
        return array_map(
            fn(TeamStatistics $stats) => $stats->team,
            $this->sessionStats
        );
    }

    public function getTeamIndex(Team $team): int
    {
        $index = array_search($team->getName(), array_keys($this->sessionStats), true);

        if ($index === false) {
            throw new \InvalidArgumentException("Team {$team->getName()} not found");
        }

        return $index;
    }
}