<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers\Api;

use App\Domain\Contracts\Services\TeamServiceInterface;
use App\Domain\Models\Team;
use App\Infrastructure\Http\Resources\TeamCollection;
use App\Infrastructure\Http\Resources\TeamResource;
use App\Infrastructure\Http\Resources\TeamSeasonResource;
use Illuminate\Http\JsonResponse;

class TeamController extends BaseController
{
    public function __construct(
        private readonly TeamServiceInterface $teamService
    ) {}

    public function index(): JsonResponse
    {
        $teams = $this->teamService->getAllTeams();

        return $this->successResponse(
            new TeamCollection($teams)
        );
    }

    public function show(Team $team): JsonResponse
    {
        $teamData = $this->teamService->getTeamWithStats($team);

        return $this->successResponse([
            'team' => new TeamResource($teamData['team']),
            'current_season_stats' => $teamData['current_season_stats'] ? new TeamSeasonResource($teamData['current_season_stats']) : null,
            'recent_matches' => $teamData['recent_matches'],
        ]);
    }

}