<?php

declare(strict_types=1);

namespace App\Domain\Contracts\Repositories;

use App\Domain\Models\Game;
use App\Domain\Models\Season;
use App\Domain\Models\Team;
use Illuminate\Support\Collection;

interface GameReadRepositoryInterface
{
    public function find(string $id): ?Game;

    public function getBySeasonId(string $seasonId): Collection;

    public function getBySeasonAndWeek(Season $season, int $week): Collection;

    public function getPlayedGames(Season $season): Collection;

    public function getUnplayedGames(Season $season): Collection;

    public function getGamesBetweenTeams(Season $season, Team $team1, Team $team2): Collection;

    public function getWithRelations(Season $season, array $relations): Collection;
}