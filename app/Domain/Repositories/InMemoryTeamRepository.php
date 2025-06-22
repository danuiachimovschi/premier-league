<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\DTOs\TeamStatistics;
use App\Domain\ValueObjects\Team;

final class InMemoryTeamRepository
{
    private array $teams = [];

    public function __construct(array $teams, private readonly float $initialStrength = 1.0)
    {
        foreach ($teams as $team) {
            $this->teams[$team->getName()] = new TeamStatistics(
                team: $team,
                attackStrength: $this->initialStrength,
                defenseStrength: $this->initialStrength,
            );
        }
    }

    public function get(Team $team): TeamStatistics
    {
        if (!isset($this->teams[$team->getName()])) {
            throw new \InvalidArgumentException("Team {$team->getName()} not found");
        }

        return $this->teams[$team->getName()];
    }

    public function getAll(): array
    {
        return array_values($this->teams);
    }

    public function update(TeamStatistics $statistics): void
    {
        $this->teams[$statistics->team->getName()] = $statistics;
    }

    public function getTeams(): array
    {
        return array_map(
            fn(TeamStatistics $stats) => $stats->team,
            $this->teams
        );
    }

    public function getTeamIndex(Team $team): int
    {
        $index = array_search($team->getName(), array_keys($this->teams), true);

        if ($index === false) {
            throw new \InvalidArgumentException("Team {$team->getName()} not found");
        }

        return $index;
    }
}