<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Domain\Contracts\Repositories\GameRepositoryInterface;
use App\Domain\Contracts\Repositories\SeasonRepositoryInterface;
use App\Domain\Contracts\Repositories\TeamSeasonRepositoryInterface;
use App\Domain\Contracts\Services\ChampionshipServiceInterface;
use App\Domain\Contracts\Services\MatchServiceInterface;
use App\Domain\Contracts\Services\MatchSimulatorServiceInterface;
use App\Domain\Models\Game;
use App\Domain\Models\Season;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MatchService implements MatchServiceInterface
{
    public function __construct(
        private readonly GameRepositoryInterface $gameRepository,
        private readonly SeasonRepositoryInterface $seasonRepository,
        private readonly TeamSeasonRepositoryInterface $teamSeasonRepository,
        private readonly MatchSimulatorServiceInterface $matchSimulator,
        private readonly ChampionshipServiceInterface $championshipService
    ) {}

    public function getMatchesByWeek(Season $season): Collection
    {
        return $this->gameRepository
            ->getWithRelations($season, ['homeTeam', 'awayTeam'])
            ->groupBy('week')
            ->map(function ($weekMatches, $week) {
                return [
                    'week' => $week,
                    'matches' => $weekMatches,
                ];
            })->values();
    }

    public function generateWeek(Season $season): array
    {
        if ($season->status !== 'active') {
            throw new \InvalidArgumentException('Season is not active');
        }

        if ($season->isCompleted()) {
            throw new \InvalidArgumentException('Season is already completed');
        }

        return DB::transaction(function () use ($season) {
            $nextWeek = $season->current_week + 1;
            $matches = $this->gameRepository
                ->getBySeasonAndWeek($season, $nextWeek)
                ->filter(fn($match) => !$match->is_played);

            if ($matches->isEmpty()) {
                throw new \RuntimeException('No matches found for week ' . $nextWeek);
            }

            foreach ($matches as $match) {
                $this->matchSimulator->simulateMatch($match);
            }

            $season->current_week = $nextWeek;
            if ($season->current_week >= $season->total_weeks) {
                $season->status = 'completed';
            }
            $this->seasonRepository->save($season);

            $this->championshipService->updateProbabilities($season);

            $updatedSeason = $this->seasonRepository->findWithRelations($season->id, ['teamSeasons.team']);
            
            return [
                'week' => $nextWeek,
                'matches' => $matches->load(['homeTeam', 'awayTeam']),
                'season' => $updatedSeason,
            ];
        });
    }

    public function updateMatch(Game $match, array $data): Game
    {
        if ($match->season->status === 'completed') {
            throw new \InvalidArgumentException('Cannot update matches in a completed season');
        }

        return DB::transaction(function () use ($match, $data) {
            $wasPlayed = $match->is_played;
            $oldHomeGoals = $match->home_goals;
            $oldAwayGoals = $match->away_goals;

            $match = $this->gameRepository->update($match, [
                'home_goals' => $data['home_goals'],
                'away_goals' => $data['away_goals'],
                'is_played' => true,
                'played_at' => now(),
                'game_statistics' => $data['match_statistics'] ?? $this->generateDefaultStatistics(),
            ]);

            $homeTeamSeason = $this->teamSeasonRepository->findByTeamAndSeason($match->homeTeam, $match->season);
            $awayTeamSeason = $this->teamSeasonRepository->findByTeamAndSeason($match->awayTeam, $match->season);

            if ($wasPlayed) {
                $this->revertMatchStats($homeTeamSeason, $oldHomeGoals, $oldAwayGoals, 'home');
                $this->revertMatchStats($awayTeamSeason, $oldAwayGoals, $oldHomeGoals, 'away');
            }

            $homeResult = $this->getMatchResult($data['home_goals'], $data['away_goals']);
            $awayResult = $this->getMatchResult($data['away_goals'], $data['home_goals']);

            $homeTeamSeason->updateStats($data['home_goals'], $data['away_goals'], $homeResult);
            $awayTeamSeason->updateStats($data['away_goals'], $data['home_goals'], $awayResult);

            $this->championshipService->updateProbabilities($match->season);

            return $match->fresh(['homeTeam', 'awayTeam']);
        });
    }

    public function simulateAllMatches(Season $season): array
    {
        if ($season->status !== 'active') {
            throw new \InvalidArgumentException('Season is not active');
        }

        return DB::transaction(function () use ($season) {
            $unplayedMatches = $season->matches()
                ->where('is_played', false)
                ->with(['homeTeam', 'awayTeam'])
                ->orderBy('week')
                ->get();

            if ($unplayedMatches->isEmpty()) {
                throw new \RuntimeException('No unplayed matches found');
            }

            foreach ($unplayedMatches as $match) {
                $this->matchSimulator->simulateMatch($match);
            }

            $season->update([
                'current_week' => $season->total_weeks,
                'status' => 'completed',
            ]);

            $this->championshipService->updateProbabilities($season);

            $updatedSeason = $season->fresh(['teamSeasons.team']);
            
            return [
                'matches_simulated' => $unplayedMatches->count(),
                'season' => $updatedSeason,
            ];
        });
    }

    private function getMatchResult(int $goalsFor, int $goalsAgainst): string
    {
        if ($goalsFor > $goalsAgainst) return 'W';
        if ($goalsFor < $goalsAgainst) return 'L';
        return 'D';
    }

    private function revertMatchStats($teamSeason, int $goalsFor, int $goalsAgainst, string $side): void
    {
        $teamSeason->played--;
        $teamSeason->goals_for -= $goalsFor;
        $teamSeason->goals_against -= $goalsAgainst;
        $teamSeason->goal_difference = $teamSeason->goals_for - $teamSeason->goals_against;

        $result = $this->getMatchResult($goalsFor, $goalsAgainst);
        switch ($result) {
            case 'W':
                $teamSeason->won--;
                $teamSeason->points -= 3;
                break;
            case 'D':
                $teamSeason->drawn--;
                $teamSeason->points -= 1;
                break;
            case 'L':
                $teamSeason->lost--;
                break;
        }

        $teamSeason->save();
    }

    private function generateDefaultStatistics(): array
    {
        $homePossession = rand(30, 70);
        return [
            'home_possession' => $homePossession,
            'away_possession' => 100 - $homePossession,
            'home_shots' => rand(5, 20),
            'away_shots' => rand(5, 20),
            'home_shots_on_target' => rand(2, 10),
            'away_shots_on_target' => rand(2, 10),
        ];
    }
}