<?php

declare(strict_types=1);

namespace App\Domain\Contracts\Services;

use App\Domain\Models\Team;
use Illuminate\Support\Collection;

interface TeamServiceInterface
{
    public function getAllTeams(): Collection;
    public function getTeamWithStats(Team $team): array;
    public function getRecentMatches(Team $team, int $limit = 5): Collection;
}