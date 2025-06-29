<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Transformers\Collections;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class GameCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'matches' => $this->collection,
            'meta' => [
                'total' => $this->collection->count(),
                'played' => $this->collection->where('is_played', true)->count(),
                'remaining' => $this->collection->where('is_played', false)->count(),
            ],
        ];
    }
}