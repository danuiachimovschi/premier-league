<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Transformers\Collections;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class SeasonCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'seasons' => $this->collection,
            'meta' => [
                'total' => $this->collection->count(),
                'active' => $this->collection->where('is_active', true)->count(),
            ],
        ];
    }
}