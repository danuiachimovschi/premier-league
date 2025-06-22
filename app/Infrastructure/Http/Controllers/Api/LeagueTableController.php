<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers\Api;

use App\Domain\Contracts\Services\LeagueTableServiceInterface;
use App\Domain\Models\Season;
use App\Infrastructure\Http\Resources\SeasonResource;
use App\Infrastructure\Http\Resources\TeamSeasonResource;
use Illuminate\Http\JsonResponse;

class LeagueTableController extends BaseController
{
    public function __construct(
        private readonly LeagueTableServiceInterface $leagueTableService
    ) {}

    public function index(Season $season): JsonResponse
    {
        $standingsData = $this->leagueTableService->getStandingsWithPositions($season);
        
        $standings = $standingsData->map(function ($item) {
            $resource = new TeamSeasonResource($item['team_season']);
            $resourceArray = $resource->toArray(request());
            $resourceArray['position'] = $item['position'];
            return $resourceArray;
        });

        return $this->successResponse([
            'season' => new SeasonResource($season),
            'standings' => $standings,
        ]);
    }
}