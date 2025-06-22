<?php

declare(strict_types=1);

namespace App\Domain\Contracts\Services;

use App\Domain\Models\Season;
use Illuminate\Support\Collection;

interface SeasonServiceInterface
{
    public function getActiveSeasons(): Collection;
    public function createSeason(array $data): Season;
    public function getSeasonWithDetails(Season $season): Season;
    public function resetSeason(Season $season): Season;
}