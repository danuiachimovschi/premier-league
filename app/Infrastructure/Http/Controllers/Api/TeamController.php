<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers\Api;

use App\Domain\Contracts\Repositories\TeamReadRepositoryInterface;
use App\Domain\Contracts\Services\TeamServiceInterface;
use App\Domain\Models\Team;
use App\Infrastructure\Http\Transformers\Collections\TeamCollection;
use App\Infrastructure\Http\Transformers\TeamSeasonTransformer;
use App\Infrastructure\Http\Transformers\TeamTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class TeamController extends Controller
{
    public function __construct(
        private readonly TeamReadRepositoryInterface $teamReadRepository,
        private readonly TeamServiceInterface $teamService
    ) {}

    public function index(): JsonResponse
    {
        $teams = $this->teamReadRepository->all();

        return response()->json([
            'status' => 'success',
            'message' => 'Success',
            'data' => new TeamCollection($teams),
        ], Response::HTTP_OK);
    }

    public function show(Team $team): JsonResponse
    {
        $teamData = $this->teamService->getTeamWithStats($team);

        return response()->json([
            'status' => 'success',
            'message' => 'Success',
            'data' => [
                'team' => new TeamTransformer($teamData['team']),
                'current_season_stats' => $teamData['current_season_stats'] ? new TeamSeasonTransformer($teamData['current_season_stats']) : null,
                'recent_matches' => $teamData['recent_matches'],
            ],
        ], Response::HTTP_OK);
    }

}