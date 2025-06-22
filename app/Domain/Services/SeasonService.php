<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Domain\Contracts\Repositories\SeasonRepositoryInterface;
use App\Domain\Contracts\Repositories\TeamRepositoryInterface;
use App\Domain\Contracts\Repositories\TeamSeasonRepositoryInterface;
use App\Domain\Contracts\Services\MatchGeneratorServiceInterface;
use App\Domain\Contracts\Services\SeasonServiceInterface;
use App\Domain\Models\Season;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SeasonService implements SeasonServiceInterface
{
    public function __construct(
        private readonly SeasonRepositoryInterface $seasonRepository,
        private readonly TeamRepositoryInterface $teamRepository,
        private readonly TeamSeasonRepositoryInterface $teamSeasonRepository,
        private readonly MatchGeneratorServiceInterface $matchGenerator
    ) {}

    public function getActiveSeasons(): Collection
    {
        $seasons = $this->seasonRepository->getActive();
        $seasons->load(['teamSeasons.team']);
        return $seasons;
    }

    public function createSeason(array $data): Season
    {
        return DB::transaction(function () use ($data) {
            $season = $this->seasonRepository->create([
                'name' => $data['name'],
                'status' => 'active',
                'current_week' => 0,
                'total_weeks' => 6,
            ]);

            $teams = $this->teamRepository->all()->take(4);
            if ($teams->count() < 4) {
                throw new \RuntimeException('Not enough teams available. Please seed teams first.');
            }

            foreach ($teams as $team) {
                $this->teamSeasonRepository->create([
                    'team_id' => $team->id,
                    'season_id' => $season->id,
                    'championship_probability' => 25.0,
                ]);
            }

            $this->matchGenerator->generateSeasonMatches($season);

            return $this->seasonRepository->findWithRelations($season->id, ['teamSeasons.team', 'matches']);
        });
    }

    public function getSeasonWithDetails(Season $season): Season
    {
        return $this->seasonRepository->findWithRelations(
            $season->id,
            ['teamSeasons.team', 'matches.homeTeam', 'matches.awayTeam']
        );
    }

    public function resetSeason(Season $season): Season
    {
        if ($season->status === 'completed') {
            throw new \InvalidArgumentException('Cannot reset a completed season');
        }

        return DB::transaction(function () use ($season) {
            $this->seasonRepository->resetStatistics($season);
            return $this->seasonRepository->findWithRelations($season->id, ['teamSeasons.team']);
        });
    }
}