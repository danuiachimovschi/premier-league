<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Domain\Contracts\Repositories\TeamSeasonRepositoryInterface;
use App\Domain\Contracts\Services\LeagueTableServiceInterface;
use App\Domain\Models\Season;
use Illuminate\Support\Collection;

class LeagueTableService implements LeagueTableServiceInterface
{
    public function __construct(
        private readonly TeamSeasonRepositoryInterface $teamSeasonRepository
    ) {}

    public function getStandingsWithPositions(Season $season): Collection
    {
        return $this->teamSeasonRepository
            ->getStandings($season)
            ->map(function ($teamSeason, $index) {
                return [
                    'position' => $index + 1,
                    'team_season' => $teamSeason,
                ];
            });
    }
}