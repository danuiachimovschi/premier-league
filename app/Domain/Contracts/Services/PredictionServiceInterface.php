<?php

declare(strict_types=1);

namespace App\Domain\Contracts\Services;

use App\Domain\Models\Season;

interface PredictionServiceInterface
{
    public function getPredictions(Season $season): array;
}