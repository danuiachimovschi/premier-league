<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Domain\Contracts\ChampionshipPredictionInterface;
use App\Domain\Contracts\StatisticsServiceInterface;
use App\Domain\DTOs\MatchResult;
use App\Domain\DTOs\TeamStatistics;
use App\Domain\ValueObjects\Team;

final class ChampionshipPredictorService implements ChampionshipPredictionInterface
{
    private array $predictionHistory = [];

    public function __construct(
        private readonly StatisticsServiceInterface $statisticsService,
        private readonly int $totalRounds = 6,
        private readonly float $formWeight = 0.4,
        private readonly float $strengthWeight = 0.6,
    ) {}

    public function addMatchResultAndUpdatePredictions(MatchResult $matchResult): array
    {
        $this->statisticsService->addMatchResult($matchResult);
        
        $predictions = $this->calculateChampionshipProbabilities();
        
        $this->predictionHistory[date('Y-m-d H:i:s')] = $predictions;
        
        return $predictions;
    }

    public function calculateChampionshipProbabilities(): array
    {
        $teamStats = $this->statisticsService->getAllTeamStatistics();
        $projections = [];

        foreach ($teamStats as $stats) {
            $projectedPoints = $this->calculateProjectedPoints($stats);
            $projections[$stats->team->getName()] = $projectedPoints;
        }

        return $this->normalizeToChampionshipProbabilities($projections);
    }

    private function calculateProjectedPoints(TeamStatistics $stats): float
    {
        $currentPoints = $stats->points->getValue();
        $matchesPlayed = $stats->gamesPlayed;
        $remainingMatches = $this->totalRounds - $matchesPlayed;

        if ($remainingMatches <= 0) {
            return (float) $currentPoints;
        }

        $overallStrength = ($stats->attackStrength + $stats->defenseStrength) / 2;
        
        $currentForm = $matchesPlayed > 0 ? $currentPoints / $matchesPlayed : 1.5;
        
        $expectedPointsPerGame =
            ($this->strengthWeight * $overallStrength * 1.5) + 
            ($this->formWeight * $currentForm);
        
        $expectedPointsPerGame = max(0.3, min(2.7, $expectedPointsPerGame));
        
        $confidenceFactor = 1.0 - (0.1 * $remainingMatches);
        $expectedRemainingPoints = $remainingMatches * $expectedPointsPerGame * $confidenceFactor;
        
        return $currentPoints + $expectedRemainingPoints;
    }

    private function normalizeToChampionshipProbabilities(array $projectedPoints): array
    {
        if (empty($projectedPoints)) {
            return [];
        }

        $exponentialScores = [];
        $maxPoints = max($projectedPoints);
        
        foreach ($projectedPoints as $team => $points) {
            $normalizedScore = $points / $maxPoints;
            $exponentialScores[$team] = exp($normalizedScore * 3);
        }

        $totalExp = array_sum($exponentialScores);
        $probabilities = [];
        
        foreach ($exponentialScores as $team => $score) {
            $probabilities[$team] = $totalExp > 0 ? $score / $totalExp : 0.0;
        }

        arsort($probabilities);
        
        return $probabilities;
    }

    public function getCurrentChampionshipOdds(): array
    {
        $probabilities = $this->calculateChampionshipProbabilities();
        $odds = [];

        foreach ($probabilities as $team => $probability) {
            $odds[$team] = $probability > 0 ? 1 / $probability : 999.0;
        }

        return $odds;
    }

    public function getPredictionHistory(): array
    {
        return $this->predictionHistory;
    }

    public function getDetailedChampionshipAnalysis(): array
    {
        $teamStats = $this->statisticsService->getAllTeamStatistics();
        $probabilities = $this->calculateChampionshipProbabilities();
        $analysis = [];

        foreach ($teamStats as $stats) {
            $teamName = $stats->team->getName();
            $remainingMatches = $this->totalRounds - $stats->gamesPlayed;
            
            $analysis[$teamName] = [
                'current_position' => $this->getCurrentPosition($stats->team),
                'current_points' => $stats->points->getValue(),
                'matches_played' => $stats->gamesPlayed,
                'remaining_matches' => $remainingMatches,
                'goal_difference' => $stats->getGoalDifference(),
                'attack_strength' => round($stats->attackStrength, 2),
                'defense_strength' => round($stats->defenseStrength, 2),
                'current_form' => round($stats->getPointsPerGame(), 2),
                'championship_probability' => round($probabilities[$teamName] * 100, 1),
                'championship_odds' => round(1 / $probabilities[$teamName], 1),
                'projected_final_points' => round($this->calculateProjectedPoints($stats), 1),
            ];
        }

        uasort($analysis, fn($a, $b) => $b['championship_probability'] <=> $a['championship_probability']);

        return $analysis;
    }

    private function getCurrentPosition(Team $team): int
    {
        $table = $this->statisticsService->getLeagueTable();
        
        foreach ($table as $entry) {
            if ($entry->team->equals($team)) {
                return $entry->position;
            }
        }
        
        return count($table) + 1;
    }

    public function getChampionshipProgressionSummary(): array
    {
        if (empty($this->predictionHistory)) {
            return [];
        }

        $teams = array_keys(current($this->predictionHistory));
        $progression = [];

        foreach ($teams as $team) {
            $progression[$team] = [
                'initial_probability' => round(reset($this->predictionHistory)[$team] * 100, 1),
                'current_probability' => round(end($this->predictionHistory)[$team] * 100, 1),
                'peak_probability' => 0.0,
                'trend' => 'stable'
            ];

            $probabilities = array_column($this->predictionHistory, $team);
            $progression[$team]['peak_probability'] = round(max($probabilities) * 100, 1);
            
            $first = reset($probabilities);
            $last = end($probabilities);
            
            if ($last > $first * 1.1) {
                $progression[$team]['trend'] = 'rising';
            } elseif ($last < $first * 0.9) {
                $progression[$team]['trend'] = 'falling';
            }
        }

        return $progression;
    }
}