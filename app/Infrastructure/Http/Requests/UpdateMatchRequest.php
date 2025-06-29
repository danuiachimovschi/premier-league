<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMatchRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'home_goals' => 'required|integer|min:0',
            'away_goals' => 'required|integer|min:0',
            'match_statistics' => 'nullable|array',
        ];
    }
}