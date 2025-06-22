<?php

declare(strict_types=1);

namespace App\Domain\DTOs;

use App\Domain\ValueObjects\Probability;

final readonly class MatchPrediction
{
    public function __construct(
        public Probability $homeWinProbability,
        public Probability $drawProbability,
        public Probability $awayWinProbability,
        public float $expectedHomeGoals,
        public float $expectedAwayGoals,
    ) {
        $total = $homeWinProbability->getValue() + 
                 $drawProbability->getValue() + 
                 $awayWinProbability->getValue();
        
        if (abs($total - 1.0) > 0.001) {
            throw new \InvalidArgumentException('Probabilities must sum to 1');
        }
    }

    public function getMostLikelyOutcome(): string
    {
        $outcomes = [
            'home_win' => $this->homeWinProbability->getValue(),
            'draw' => $this->drawProbability->getValue(),
            'away_win' => $this->awayWinProbability->getValue(),
        ];

        return array_search(max($outcomes), $outcomes, true);
    }
}