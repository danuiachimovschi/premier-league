<?php

declare(strict_types=1);

namespace App\Domain\Contracts\Services;

use App\Domain\Models\Season;
use Illuminate\Support\Collection;

interface MatchGeneratorServiceInterface
{
    public function generateSeasonMatches(Season $season): void;

    public function generateWeekMatches(Season $season, int $week): Collection;

    public function getWeekMatches(Season $season, int $week): Collection;

    public function hasWeekMatches(Season $season, int $week): bool;
}