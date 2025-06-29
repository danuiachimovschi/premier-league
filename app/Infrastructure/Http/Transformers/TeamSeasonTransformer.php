<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamSeasonTransformer extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'team' => new TeamTransformer($this->whenLoaded('team')),
            'played' => $this->played,
            'won' => $this->won,
            'drawn' => $this->drawn,
            'lost' => $this->lost,
            'goals_for' => $this->goals_for,
            'goals_against' => $this->goals_against,
            'goal_difference' => $this->goal_difference,
            'points' => $this->points,
            'form' => $this->form ?? [],
            'points_per_game' => $this->getPointsPerGame(),
            'championship_probability' => $this->championship_probability,
        ];
    }
}