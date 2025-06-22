<?php

declare(strict_types=1);

namespace App\Domain\Contracts;

use App\Domain\ValueObjects\Probability;

interface ProbabilityCalculatorInterface
{
    public function poissonProbability(int $k, float $lambda): float;

    public function calculateWinProbability(float $homeExpectedGoals, float $awayExpectedGoals): Probability;

    public function calculateDrawProbability(float $homeExpectedGoals, float $awayExpectedGoals): Probability;
}