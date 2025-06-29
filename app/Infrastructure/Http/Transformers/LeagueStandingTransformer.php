<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeagueStandingTransformer extends JsonResource
{
    public function toArray(Request $request): array
    {
        $teamSeasonData = (new TeamSeasonTransformer($this->resource['team_season']))->toArray($request);
        $teamSeasonData['position'] = $this->resource['position'];
        
        return $teamSeasonData;
    }
}