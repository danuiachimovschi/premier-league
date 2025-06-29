<?php

declare(strict_types=1);

namespace App\Domain\Contracts\Repositories;

use App\Domain\Models\Season;
use App\Domain\Models\Team;
use App\Domain\Models\TeamSeason;
use Illuminate\Support\Collection;

interface TeamSeasonReadRepositoryInterface
{
    public function find(string $id): ?TeamSeason;

    public function findByTeamAndSeason(Team $team, Season $season): ?TeamSeason;

    public function getBySeason(Season $season): Collection;

    public function getStandings(Season $season): Collection;

    public function getWithRelations(Season $season, array $relations): Collection;

    public function getRemainingMatchesCount(TeamSeason $teamSeason): int;
}