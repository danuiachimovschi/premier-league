<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PredictionTransformer extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'team' => $this->resource['team'] ?? null,
            'current_points' => $this->resource['current_points'] ?? 0,
            'championship_probability' => $this->resource['championship_probability'] ?? 0,
            'betting_odds' => $this->resource['betting_odds'] ?? 0,
            'projected_points' => $this->resource['projected_points'] ?? 0,
            'remaining_matches' => $this->resource['remaining_matches'] ?? 0,
            'points_per_game' => $this->resource['points_per_game'] ?? 0,
            'attack_strength' => $this->resource['attack_strength'] ?? 0,
            'defense_strength' => $this->resource['defense_strength'] ?? 0,
            'recent_form' => $this->resource['recent_form'] ?? [],
        ];
    }
}