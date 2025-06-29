<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers\Api;

use App\Domain\Contracts\Services\SeasonServiceInterface;
use App\Domain\Models\Season;
use App\Infrastructure\Http\Requests\StoreSeasonRequest;
use App\Infrastructure\Http\Transformers\SeasonTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class SeasonController extends Controller
{
    public function __construct(
        private readonly SeasonServiceInterface $seasonService
    ) {}
    public function index(): JsonResponse
    {
        $seasons = $this->seasonService->getActiveSeasons();

        return response()->json([
            'status' => 'success',
            'message' => 'Success',
            'data' => SeasonTransformer::collection($seasons),
        ], Response::HTTP_OK);
    }

    public function store(StoreSeasonRequest $request): JsonResponse
    {
        $season = $this->seasonService->createSeason([
            'name' => $request->name,
        ]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Season created successfully',
            'data' => new SeasonTransformer($season),
        ], Response::HTTP_CREATED);
    }

    public function show(Season $season): JsonResponse
    {
        $seasonWithDetails = $this->seasonService->getSeasonWithDetails($season);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Success',
            'data' => new SeasonTransformer($seasonWithDetails),
        ], Response::HTTP_OK);
    }

    public function reset(Season $season): JsonResponse
    {
        $resetSeason = $this->seasonService->resetSeason($season);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Season reset successfully',
            'data' => new SeasonTransformer($resetSeason),
        ], Response::HTTP_OK);
    }
}