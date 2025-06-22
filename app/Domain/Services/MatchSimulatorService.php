<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Domain\Contracts\Repositories\GameRepositoryInterface;
use App\Domain\Contracts\Repositories\TeamSeasonRepositoryInterface;
use App\Domain\Contracts\Services\MatchSimulatorServiceInterface;
use App\Domain\Models\Game;
use App\Domain\Models\Season;

class MatchSimulatorService implements MatchSimulatorServiceInterface
{
    public function __construct(
        private readonly GameRepositoryInterface $gameRepository,
        private readonly TeamSeasonRepositoryInterface $teamSeasonRepository
    ) {}
    public function simulateMatch(Game $match): Game
    {
        if ($match->is_played) {
            return $match;
        }

        $homeTeam = $match->homeTeam;
        $awayTeam = $match->awayTeam;

        $homeAttack = $homeTeam->attack_strength;
        $homeDefense = $homeTeam->defense_strength;
        $awayAttack = $awayTeam->attack_strength;
        $awayDefense = $awayTeam->defense_strength;

        $homeAdvantage = config('league.simulation.home_advantage', 1.2);

        $homeExpectedGoals = ($homeAttack * $homeAdvantage) / $awayDefense;
        $awayExpectedGoals = $awayAttack / ($homeDefense * $homeAdvantage);

        $homeGoals = $this->generateGoals($homeExpectedGoals);
        $awayGoals = $this->generateGoals($awayExpectedGoals);

        $statistics = $this->generateMatchStatistics($homeGoals, $awayGoals, $homeAttack, $awayAttack);

        $match = $this->gameRepository->update($match, [
            'home_goals' => $homeGoals,
            'away_goals' => $awayGoals,
            'game_statistics' => $statistics,
            'is_played' => true,
            'played_at' => now(),
        ]);

        $this->updateTeamSeasonStats($match);
        
        return $match;
    }

    private function generateGoals(float $expectedGoals): int
    {
        $lambda = max(0.1, min(5.0, $expectedGoals));
        
        $goals = 0;
        $probability = exp(-$lambda);
        $cumulativeProbability = $probability;
        $random = mt_rand() / mt_getrandmax();

        while ($random > $cumulativeProbability && $goals < 10) {
            $goals++;
            $probability *= $lambda / $goals;
            $cumulativeProbability += $probability;
        }

        return $goals;
    }

    private function generateMatchStatistics(int $homeGoals, int $awayGoals, float $homeAttack, float $awayAttack): array
    {
        $strengthRatio = $homeAttack / ($homeAttack + $awayAttack);
        $basePossession = 40 + ($strengthRatio * 20);
        
        $minPossession = config('league.simulation.min_possession', 25);
        $maxPossession = config('league.simulation.max_possession', 75);
        $homePossession = max($minPossession, min($maxPossession, $basePossession + rand(-10, 10)));
        $awayPossession = 100 - $homePossession;

        $homeShots = max(5, $homeGoals * 3 + rand(3, 8) + intval($homePossession / 10));
        $awayShots = max(5, $awayGoals * 3 + rand(3, 8) + intval($awayPossession / 10));

        $homeShotsOnTarget = max(1, intval($homeShots * (0.3 + rand(0, 20) / 100)));
        $awayShotsOnTarget = max(1, intval($awayShots * (0.3 + rand(0, 20) / 100)));

        $homeShotsOnTarget = max($homeGoals, $homeShotsOnTarget);
        $awayShotsOnTarget = max($awayGoals, $awayShotsOnTarget);

        return [
            'home_possession' => round($homePossession, 1),
            'away_possession' => round($awayPossession, 1),
            'home_shots' => $homeShots,
            'away_shots' => $awayShots,
            'home_shots_on_target' => $homeShotsOnTarget,
            'away_shots_on_target' => $awayShotsOnTarget,
            'home_corners' => rand(2, 12),
            'away_corners' => rand(2, 12),
            'home_fouls' => rand(8, 20),
            'away_fouls' => rand(8, 20),
        ];
    }

    private function updateTeamSeasonStats(Game $match): void
    {
        $homeTeamSeason = $this->teamSeasonRepository->findByTeamAndSeason(
            $match->homeTeam,
            $match->season
        );
        
        $awayTeamSeason = $this->teamSeasonRepository->findByTeamAndSeason(
            $match->awayTeam,
            $match->season
        );

        $homeResult = $this->getMatchResult($match->home_goals, $match->away_goals);
        $awayResult = $this->getMatchResult($match->away_goals, $match->home_goals);

        $homePoints = $homeResult === 'W' ? 3 : ($homeResult === 'D' ? 1 : 0);
        $awayPoints = $awayResult === 'W' ? 3 : ($awayResult === 'D' ? 1 : 0);

        $this->teamSeasonRepository->updateMatchStats(
            $homeTeamSeason,
            $match->home_goals,
            $match->away_goals,
            $homePoints,
            $homeResult
        );

        $this->teamSeasonRepository->updateMatchStats(
            $awayTeamSeason,
            $match->away_goals,
            $match->home_goals,
            $awayPoints,
            $awayResult
        );
    }

    private function getMatchResult(int $goalsFor, int $goalsAgainst): string
    {
        if ($goalsFor > $goalsAgainst) return 'W';
        if ($goalsFor < $goalsAgainst) return 'L';
        return 'D';
    }

    public function simulateWeek(Season $season, int $week): array
    {
        $matches = $season->matches()
            ->where('week', $week)
            ->where('is_played', false)
            ->with(['homeTeam', 'awayTeam', 'season'])
            ->get();

        $simulatedMatches = [];
        foreach ($matches as $match) {
            $simulatedMatches[] = $this->simulateMatch($match);
        }

        return $simulatedMatches;
    }

    public function simulateAllRemainingMatches(Season $season): array
    {
        $matches = $season->matches()
            ->where('is_played', false)
            ->with(['homeTeam', 'awayTeam', 'season'])
            ->orderBy('week')
            ->get();

        $simulatedMatches = [];
        foreach ($matches as $match) {
            $simulatedMatches[] = $this->simulateMatch($match);
        }

        return $simulatedMatches;
    }

    public function getMatchStatistics(Game $match): array
    {
        return $match->game_statistics ?? [];
    }
}