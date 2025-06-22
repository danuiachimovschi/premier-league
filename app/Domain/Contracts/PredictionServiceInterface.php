<?php

declare(strict_types=1);

namespace App\Domain\Contracts;

use App\Domain\DTOs\MatchPrediction;
use App\Domain\ValueObjects\Team;

interface PredictionServiceInterface
{
    public function predictMatch(Team $homeTeam, Team $awayTeam): MatchPrediction;

    public function calculateChampionshipProbabilities(): array;
}