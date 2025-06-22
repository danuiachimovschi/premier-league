<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Domain\Contracts\Repositories\TeamSeasonRepositoryInterface;
use App\Domain\Contracts\Services\ChampionshipServiceInterface;
use App\Domain\Contracts\Services\PredictionServiceInterface;
use App\Domain\Models\Season;
use Illuminate\Support\Collection;

class PredictionService implements PredictionServiceInterface
{
    public function __construct(
        private readonly TeamSeasonRepositoryInterface $teamSeasonRepository,
        private readonly ChampionshipServiceInterface $championshipService
    ) {}

    public function getPredictions(Season $season): array
    {
        if ($season->current_week === 0) {
            throw new \InvalidArgumentException('No predictions available. Play at least one week first.');
        }

        $predictions = $this->teamSeasonRepository
            ->getStandings($season)
            ->sortByDesc('championship_probability')
            ->map(function ($teamSeason) use ($season) {
                $remainingMatches = $teamSeason->getRemainingMatches();
                $projectedPoints = $this->calculateProjectedPoints($teamSeason, $remainingMatches);
                
                return [
                    'team' => $teamSeason->team->name,
                    'current_points' => $teamSeason->points,
                    'championship_probability' => round($teamSeason->championship_probability, 2),
                    'betting_odds' => $this->calculateBettingOdds($teamSeason->championship_probability),
                    'projected_points' => round($projectedPoints, 1),
                    'remaining_matches' => $remainingMatches,
                    'points_per_game' => round($teamSeason->getPointsPerGame(), 2),
                    'attack_strength' => $teamSeason->team->attack_strength,
                    'defense_strength' => $teamSeason->team->defense_strength,
                    'recent_form' => $teamSeason->form ?? [],
                ];
            });

        $history = $this->championshipService->getPredictionHistory($season);
        $analysis = $this->generateAnalysis($predictions, $season);

        return [
            'predictions' => $predictions,
            'history' => $history,
            'analysis' => $analysis,
        ];
    }

    private function calculateProjectedPoints(object $teamSeason, int $remainingMatches): float
    {
        if ($remainingMatches === 0) {
            return $teamSeason->points;
        }

        $ppg = $teamSeason->getPointsPerGame();
        $teamStrength = ($teamSeason->team->attack_strength + $teamSeason->team->defense_strength) / 2;
        
        $expectedPpg = (0.6 * $teamStrength * 1.5) + (0.4 * $ppg);
        $confidenceFactor = 1.0 - (0.1 * ($remainingMatches / 2));
        
        return $teamSeason->points + ($remainingMatches * $expectedPpg * $confidenceFactor);
    }

    private function calculateBettingOdds(float $probability): float
    {
        if ($probability <= 0) {
            return 999.99;
        }
        
        return round(100 / $probability, 2);
    }

    private function generateAnalysis(Collection $predictions, Season $season): array
    {
        $leader = $predictions->first();
        $analysis = [];

        if ($season->current_week < 4) {
            $analysis['phase'] = 'early';
            $analysis['message'] = 'Early season - predictions are highly volatile';
        } elseif ($season->current_week < $season->total_weeks) {
            $analysis['phase'] = 'mid';
            $analysis['message'] = 'Mid-season - patterns are emerging';
        } else {
            $analysis['phase'] = 'final';
            $analysis['message'] = 'Season complete - final standings';
        }

        if ($leader && $leader['championship_probability'] > 60) {
            $analysis['favorite'] = $leader['team'] . ' is the clear favorite';
        } elseif ($predictions->count() >= 2 && 
                  abs($predictions->get(0)['championship_probability'] - $predictions->get(1)['championship_probability']) < 10) {
            $analysis['favorite'] = 'Title race is very close';
        }

        return $analysis;
    }
}