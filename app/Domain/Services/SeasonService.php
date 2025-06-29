<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Domain\Contracts\Repositories\SeasonReadRepositoryInterface;
use App\Domain\Contracts\Repositories\SeasonWriteRepositoryInterface;
use App\Domain\Contracts\Repositories\TeamReadRepositoryInterface;
use App\Domain\Contracts\Repositories\TeamSeasonWriteRepositoryInterface;
use App\Domain\Contracts\Services\MatchGeneratorServiceInterface;
use App\Domain\Contracts\Services\SeasonServiceInterface;
use App\Domain\Models\Season;
use App\Domain\Exceptions\SeasonException;
use App\Domain\Exceptions\TeamException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SeasonService implements SeasonServiceInterface
{
    public function __construct(
        private readonly SeasonReadRepositoryInterface $seasonReadRepository,
        private readonly SeasonWriteRepositoryInterface $seasonWriteRepository,
        private readonly TeamReadRepositoryInterface $teamReadRepository,
        private readonly TeamSeasonWriteRepositoryInterface $teamSeasonWriteRepository,
        private readonly MatchGeneratorServiceInterface $matchGenerator
    ) {}

    public function getActiveSeasons(): Collection
    {
        $seasons = $this->seasonReadRepository->getActive();
        $seasons->load(['teamSeasons.team']);
        return $seasons;
    }

    public function createSeason(array $data): Season
    {
        return DB::transaction(function () use ($data) {
            $season = $this->seasonWriteRepository->create([
                'name' => $data['name'],
                'status' => 'active',
                'current_week' => 0,
                'total_weeks' => 6,
            ]);

            $teams = $this->teamReadRepository->all()->take(4);
            if ($teams->count() < 4) {
                throw TeamException::insufficientTeams(4, $teams->count());
            }

            foreach ($teams as $team) {
                $this->teamSeasonWriteRepository->create([
                    'team_id' => $team->id,
                    'season_id' => $season->id,
                    'championship_probability' => 25.0,
                ]);
            }

            $this->matchGenerator->generateSeasonMatches($season);

            return $this->seasonReadRepository->findWithRelations($season->id, ['teamSeasons.team', 'matches']);
        });
    }

    public function getSeasonWithDetails(Season $season): Season
    {
        return $this->seasonReadRepository->findWithRelations(
            $season->id,
            ['teamSeasons.team', 'matches.homeTeam', 'matches.awayTeam']
        );
    }

    public function resetSeason(Season $season): Season
    {
        if ($season->status === 'completed') {
            throw SeasonException::alreadyCompleted();
        }

        return DB::transaction(function () use ($season) {
            $this->seasonWriteRepository->resetStatistics($season);
            return $this->seasonReadRepository->findWithRelations($season->id, ['teamSeasons.team']);
        });
    }
}