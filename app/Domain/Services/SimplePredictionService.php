<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Domain\Models\Season;
use Illuminate\Support\Facades\Cache;

class SimplePredictionService
{
    public function getPredictions(Season $season): array
    {
        if ($season->current_week === 0) {
            throw new \InvalidArgumentException('No predictions available. Play at least one week first.');
        }

        $teamSeasons = $season->teamSeasons()->with('team')->get();
        $predictions = [];

        foreach ($teamSeasons as $teamSeason) {
            $probability = $this->calculateChampionshipProbability($teamSeason, $season);
            
            $predictions[] = [
                'team' => $teamSeason->team->name,
                'current_points' => $teamSeason->points,
                'championship_probability' => round($probability, 2),
                'betting_odds' => $probability > 0 ? round(100 / $probability, 2) : 999.99,
                'projected_points' => round($this->calculateProjectedPoints($teamSeason), 1),
                'remaining_matches' => $teamSeason->getRemainingMatches(),
                'points_per_game' => round($teamSeason->getPointsPerGame(), 2),
            ];
        }

        usort($predictions, fn($a, $b) => $b['championship_probability'] <=> $a['championship_probability']);

        return [
            'predictions' => $predictions,
            'analysis' => $this->generateAnalysis($predictions, $season),
        ];
    }

    public function updateProbabilities(Season $season): void
    {
        $teamSeasons = $season->teamSeasons()->with('team')->get();

        foreach ($teamSeasons as $teamSeason) {
            $probability = $this->calculateChampionshipProbability($teamSeason, $season);
            $teamSeason->update(['championship_probability' => $probability]);
        }
    }

    private function calculateChampionshipProbability($teamSeason, Season $season): float
    {
        $allTeamSeasons = $season->teamSeasons()->get();
        $projectedPoints = [];

        foreach ($allTeamSeasons as $ts) {
            $projectedPoints[$ts->id] = $this->calculateProjectedPoints($ts);
        }

        $maxPoints = max($projectedPoints);
        $teamProjectedPoints = $projectedPoints[$teamSeason->id];

        if ($maxPoints <= 0) {
            return 25.0; // Equal probability if no one has points
        }

        $normalizedScore = $teamProjectedPoints / $maxPoints;
        $exponentialScore = exp($normalizedScore * 2);

        $totalExponential = 0;
        foreach ($projectedPoints as $points) {
            $totalExponential += exp(($points / $maxPoints) * 2);
        }

        return ($exponentialScore / $totalExponential) * 100;
    }

    private function calculateProjectedPoints($teamSeason): float
    {
        $remainingMatches = $teamSeason->getRemainingMatches();
        
        if ($remainingMatches === 0) {
            return $teamSeason->points;
        }

        $pointsPerGame = $teamSeason->getPointsPerGame();
        $teamStrength = ($teamSeason->team->attack_strength + $teamSeason->team->defense_strength) / 2;
        
        $expectedPpg = (0.6 * $teamStrength * 1.5) + (0.4 * $pointsPerGame);
        $confidenceFactor = max(0.7, 1.0 - (0.1 * ($remainingMatches / 2)));
        
        return $teamSeason->points + ($remainingMatches * $expectedPpg * $confidenceFactor);
    }

    private function generateAnalysis(array $predictions, Season $season): array
    {
        $leader = $predictions[0] ?? null;
        $analysis = [];

        if ($season->current_week < 3) {
            $analysis['phase'] = 'Early season - predictions are highly volatile';
        } elseif ($season->current_week < $season->total_weeks) {
            $analysis['phase'] = 'Mid-season - patterns are emerging';
        } else {
            $analysis['phase'] = 'Final standings';
        }

        if ($leader && $leader['championship_probability'] > 60) {
            $analysis['favorite'] = $leader['team'] . ' is the clear favorite';
        } elseif (count($predictions) >= 2 && 
                  abs($predictions[0]['championship_probability'] - $predictions[1]['championship_probability']) < 10) {
            $analysis['favorite'] = 'Title race is very close';
        }

        return $analysis;
    }

    public function clearCache(Season $season): void
    {
        Cache::forget("predictions_season_{$season->id}");
    }
}