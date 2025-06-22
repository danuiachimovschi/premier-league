<?php

declare(strict_types=1);

namespace App\Domain\Contracts\Services;

use App\Domain\Models\Game;
use App\Domain\Models\Season;
use Illuminate\Support\Collection;

interface MatchServiceInterface
{
    public function getMatchesByWeek(Season $season): Collection;
    public function generateWeek(Season $season): array;
    public function updateMatch(Game $match, array $data): Game;
    public function simulateAllMatches(Season $season): array;
}