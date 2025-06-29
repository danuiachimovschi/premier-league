<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameTransformer extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'week' => $this->week,
            'home_team' => new TeamTransformer($this->whenLoaded('homeTeam')),
            'away_team' => new TeamTransformer($this->whenLoaded('awayTeam')),
            'home_goals' => $this->home_goals,
            'away_goals' => $this->away_goals,
            'is_played' => $this->is_played,
            'played_at' => $this->played_at?->toISOString(),
            'game_statistics' => $this->game_statistics,
            'result' => $this->when($this->is_played, function () {
                if ($this->home_goals > $this->away_goals) {
                    return 'home_win';
                }
                if ($this->home_goals < $this->away_goals) {
                    return 'away_win';
                }
                return 'draw';
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}