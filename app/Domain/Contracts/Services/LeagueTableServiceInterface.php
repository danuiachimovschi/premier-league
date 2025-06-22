<?php

declare(strict_types=1);

namespace App\Domain\Contracts\Services;

use App\Domain\Models\Season;
use Illuminate\Support\Collection;

interface LeagueTableServiceInterface
{
    public function getStandingsWithPositions(Season $season): Collection;
}