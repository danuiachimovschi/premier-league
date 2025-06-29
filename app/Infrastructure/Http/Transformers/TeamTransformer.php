<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamTransformer extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'attack_strength' => $this->attack_strength,
            'defense_strength' => $this->defense_strength,
            'logo_url' => $this->logo_url,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}