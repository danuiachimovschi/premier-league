<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Contracts\Repositories\TeamSeasonReadRepositoryInterface;
use App\Infrastructure\Repositories\BaseReadRepository;
use App\Domain\Models\Season;
use App\Domain\Models\Team;
use App\Domain\Models\TeamSeason;
use Illuminate\Support\Collection;

class TeamSeasonReadRepository extends BaseReadRepository implements TeamSeasonReadRepositoryInterface
{
    public function find(string $id): ?TeamSeason
    {
        return TeamSeason::on($this->getConnection())->find($id);
    }

    public function findByTeamAndSeason(Team $team, Season $season): ?TeamSeason
    {
        return TeamSeason::on($this->getConnection())->where('team_id', $team->id)
            ->where('season_id', $season->id)
            ->first();
    }

    public function getBySeason(Season $season): Collection
    {
        return TeamSeason::on($this->getConnection())->where('season_id', $season->id)
            ->with('team')
            ->get();
    }

    public function getStandings(Season $season): Collection
    {
        return TeamSeason::on($this->getConnection())->where('season_id', $season->id)
            ->with('team')
            ->orderByDesc('points')
            ->orderByDesc('goal_difference')
            ->orderByDesc('goals_for')
            ->get();
    }

    public function getWithRelations(Season $season, array $relations): Collection
    {
        return TeamSeason::on($this->getConnection())->where('season_id', $season->id)
            ->with($relations)
            ->orderByDesc('points')
            ->orderByDesc('goal_difference')
            ->orderByDesc('goals_for')
            ->get();
    }

    public function getRemainingMatchesCount(TeamSeason $teamSeason): int
    {
        $totalMatches = ($teamSeason->season->teams()->count() - 1) * 2;
        return $totalMatches - $teamSeason->played;
    }
}