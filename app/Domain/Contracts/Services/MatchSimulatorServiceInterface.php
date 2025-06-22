<?php

declare(strict_types=1);

namespace App\Domain\Contracts\Services;

use App\Domain\Models\Game;
use App\Domain\Models\Season;

interface MatchSimulatorServiceInterface
{
    public function simulateMatch(Game $match): Game;

    public function simulateWeek(Season $season, int $week): array;

    public function simulateAllRemainingMatches(Season $season): array;

    public function getMatchStatistics(Game $match): array;
}