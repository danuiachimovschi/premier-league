<?php

declare(strict_types=1);

namespace App\Domain\Contracts\Repositories;

use App\Domain\Models\Season;
use App\Domain\Models\TeamSeason;

interface TeamSeasonWriteRepositoryInterface
{
    public function create(array $data): TeamSeason;

    public function update(TeamSeason $teamSeason, array $data): TeamSeason;

    public function save(TeamSeason $teamSeason): bool;

    public function updateMatchStats(
        TeamSeason $teamSeason,
        int $goalsFor,
        int $goalsAgainst,
        int $points,
        string $result
    ): TeamSeason;

    public function updateChampionshipProbability(TeamSeason $teamSeason, float $probability): TeamSeason;

    public function resetSeasonStatistics(Season $season): void;
}