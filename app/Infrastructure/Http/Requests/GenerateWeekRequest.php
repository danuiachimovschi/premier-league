<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateWeekRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'simulate' => 'sometimes|boolean',
            'week' => 'sometimes|integer|min:1|max:6',
        ];
    }
}