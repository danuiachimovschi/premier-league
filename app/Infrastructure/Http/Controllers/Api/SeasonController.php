<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers\Api;

use App\Domain\Contracts\Services\SeasonServiceInterface;
use App\Domain\Models\Season;
use App\Infrastructure\Http\Requests\StoreSeasonRequest;
use App\Infrastructure\Http\Resources\SeasonResource;
use Exception;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;
use RuntimeException;

class SeasonController extends BaseController
{
    public function __construct(
        private readonly SeasonServiceInterface $seasonService
    ) {}
    public function index(): JsonResponse
    {
        $seasons = $this->seasonService->getActiveSeasons();

        return $this->successResponse(
            SeasonResource::collection($seasons)
        );
    }

    public function store(StoreSeasonRequest $request): JsonResponse
    {
        try {
            $season = $this->seasonService->createSeason([
                'name' => $request->name,
            ]);
            
            return $this->successResponse(
                new SeasonResource($season),
                'Season created successfully',
                201
            );
        } catch (RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 400);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to create season: ' . $e->getMessage(), 500);
        }
    }

    public function show(Season $season): JsonResponse
    {
        $seasonWithDetails = $this->seasonService->getSeasonWithDetails($season);
        
        return $this->successResponse(
            new SeasonResource($seasonWithDetails)
        );
    }

    public function reset(Season $season): JsonResponse
    {
        try {
            $resetSeason = $this->seasonService->resetSeason($season);
            
            return $this->successResponse(
                new SeasonResource($resetSeason),
                'Season reset successfully'
            );
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 400);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to reset season: ' . $e->getMessage(), 500);
        }
    }
}