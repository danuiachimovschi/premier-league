<?php

declare(strict_types=1);

namespace App\Domain\Contracts;

use App\Domain\DTOs\LeagueTableEntry;
use App\Domain\DTOs\MatchResult;
use App\Domain\DTOs\TeamStatistics;
use App\Domain\ValueObjects\Team;

interface StatisticsServiceInterface
{
    public function addMatchResult(MatchResult $matchResult): void;

    public function getTeamStatistics(Team $team): TeamStatistics;

    public function getAllTeamStatistics(): array;

    public function getLeagueTable(): array;
}