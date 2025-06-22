<?php

declare(strict_types=1);

namespace App\Domain\Contracts\Services;

use App\Domain\Models\Season;

interface ChampionshipServiceInterface
{
    public function updateProbabilities(Season $season): void;

    public function getChampionshipPredictions(Season $season): array;

    public function clearPredictionsCache(Season $season): void;
}