<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers\Api;

use App\Domain\Contracts\Services\MatchServiceInterface;
use App\Domain\Models\Game;
use App\Domain\Models\Season;
use App\Infrastructure\Http\Requests\GenerateWeekRequest;
use App\Infrastructure\Http\Requests\UpdateMatchRequest;
use App\Infrastructure\Http\Resources\GameCollection;
use App\Infrastructure\Http\Resources\GameResource;
use App\Infrastructure\Http\Resources\SeasonResource;
use Exception;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;
use RuntimeException;

class MatchController extends BaseController
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

        return $this->successResponse($formattedMatches);
    }

    public function generateWeek(GenerateWeekRequest $request, Season $season): JsonResponse
    {
        try {
            $result = $this->matchService->generateWeek($season);
            
            return $this->successResponse([
                'week' => $result['week'],
                'matches' => new GameCollection($result['matches']),
                'season' => new SeasonResource($result['season']),
            ], 'Week ' . $result['week'] . ' matches generated successfully');
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 400);
        } catch (RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to generate matches: ' . $e->getMessage(), 500);
        }
    }

    public function update(UpdateMatchRequest $request, Game $match): JsonResponse
    {
        try {
            $updatedMatch = $this->matchService->updateMatch($match, [
                'home_goals' => $request->home_goals,
                'away_goals' => $request->away_goals,
                'match_statistics' => $request->match_statistics,
            ]);
            
            return $this->successResponse(
                new GameResource($updatedMatch),
                'Match updated successfully'
            );
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 400);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to update match: ' . $e->getMessage(), 500);
        }
    }

    public function simulateAll(Season $season): JsonResponse
    {
        try {
            $result = $this->matchService->simulateAllMatches($season);
            
            return $this->successResponse([
                'matches_simulated' => $result['matches_simulated'],
                'season' => new SeasonResource($result['season']),
            ], 'All matches simulated successfully');
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 400);
        } catch (RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to simulate matches: ' . $e->getMessage(), 500);
        }
    }
}