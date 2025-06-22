<?php

declare(strict_types=1);

namespace App\Domain\Contracts;

use App\Domain\DTOs\MatchResult;

interface ChampionshipPredictionInterface
{
    public function addMatchResultAndUpdatePredictions(MatchResult $matchResult): array;

    public function calculateChampionshipProbabilities(): array;

    public function getCurrentChampionshipOdds(): array;

    public function getDetailedChampionshipAnalysis(): array;

    public function getPredictionHistory(): array;
}