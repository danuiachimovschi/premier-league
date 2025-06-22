<?php

declare(strict_types=1);

namespace App\Domain\Contracts\Repositories;

use App\Domain\Models\Season;
use App\Domain\Models\Team;
use App\Domain\Models\TeamSeason;
use Illuminate\Support\Collection;

interface TeamSeasonRepositoryInterface
{
    public function find(string $id): ?TeamSeason;

    public function findByTeamAndSeason(Team $team, Season $season): ?TeamSeason;

    public function getBySeason(Season $season): Collection;

    public function getStandings(Season $season): Collection;

    public function create(array $data): TeamSeason;

    public function update(TeamSeason $teamSeason, array $data): TeamSeason;

    public function updateMatchStats(
        TeamSeason $teamSeason,
        int $goalsFor,
        int $goalsAgainst,
        int $points,
        string $result
    ): TeamSeason;

    public function updateChampionshipProbability(TeamSeason $teamSeason, float $probability): TeamSeason;

    public function resetSeasonStatistics(Season $season): void;

    public function getWithRelations(Season $season, array $relations): Collection;

    public function save(TeamSeason $teamSeason): bool;

    public function getRemainingMatchesCount(TeamSeason $teamSeason): int;
}