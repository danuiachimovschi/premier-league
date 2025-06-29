<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers\Api;

use App\Domain\Contracts\Services\LeagueTableServiceInterface;
use App\Domain\Models\Season;
use App\Infrastructure\Http\Transformers\LeagueStandingTransformer;
use App\Infrastructure\Http\Transformers\SeasonTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class LeagueTableController extends Controller
{
    public function __construct(
        private readonly LeagueTableServiceInterface $leagueTableService
    ) {}

    public function index(Season $season): JsonResponse
    {
        $standingsData = $this->leagueTableService->getStandingsWithPositions($season);
        
        $standings = LeagueStandingTransformer::collection($standingsData);

        return response()->json([
            'status' => 'success',
            'message' => 'Success',
            'data' => [
                'season' => new SeasonTransformer($season),
                'standings' => $standings,
            ],
        ], Response::HTTP_OK);
    }
}