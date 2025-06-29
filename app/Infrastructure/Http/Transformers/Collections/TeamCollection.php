<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Transformers\Collections;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TeamCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'teams' => $this->collection,
            'meta' => [
                'total' => $this->collection->count(),
            ],
        ];
    }
}