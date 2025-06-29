<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers\Api;

use App\Domain\Contracts\Services\MatchServiceInterface;
use App\Domain\Models\Game;
use App\Domain\Models\Season;
use App\Infrastructure\Http\Requests\GenerateWeekRequest;
use App\Infrastructure\Http\Requests\UpdateMatchRequest;
use App\Infrastructure\Http\Transformers\Collections\GameCollection;
use App\Infrastructure\Http\Transformers\GameTransformer;
use App\Infrastructure\Http\Transformers\SeasonTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class MatchController extends Controller
{
    public function __construct(
        private readonly MatchServiceInterface $matchService
    ) {}

    public function index(Season $season): JsonResponse
    {
        $formattedMatches = $this->matchService->getMatchesByWeek($season)
            ->map(function ($weekData) {
                return [
                    'week' => $weekData['week'],
                    'matches' => new GameCollection($weekData['matches']),
                ];
            });

        return response()->json([
            'status' => 'success',
            'message' => 'Success',
            'data' => $formattedMatches,
        ], Response::HTTP_OK);
    }

    public function generateWeek(GenerateWeekRequest $request, Season $season): JsonResponse
    {
        $result = $this->matchService->generateWeek($season);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Week ' . $result['week'] . ' matches generated successfully',
            'data' => [
                'week' => $result['week'],
                'matches' => new GameCollection($result['matches']),
                'season' => new SeasonTransformer($result['season']),
            ],
        ], Response::HTTP_OK);
    }

    public function update(UpdateMatchRequest $request, Game $match): JsonResponse
    {
        $updatedMatch = $this->matchService->updateMatch($match, [
            'home_goals' => $request->home_goals,
            'away_goals' => $request->away_goals,
            'match_statistics' => $request->match_statistics,
        ]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Match updated successfully',
            'data' => new GameTransformer($updatedMatch),
        ], Response::HTTP_OK);
    }

    public function simulateAll(Season $season): JsonResponse
    {
        $result = $this->matchService->simulateAllMatches($season);
        
        return response()->json([
            'status' => 'success',
            'message' => 'All matches simulated successfully',
            'data' => [
                'matches_simulated' => $result['matches_simulated'],
                'season' => new SeasonTransformer($result['season']),
            ],
        ], Response::HTTP_OK);
    }
}