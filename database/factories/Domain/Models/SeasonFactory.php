<?php

namespace Database\Factories\Domain\Models;

use App\Domain\Models\Season;
use Illuminate\Database\Eloquent\Factories\Factory;

class SeasonFactory extends Factory
{
    protected $model = Season::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement([
                '2023/2024 Season',
                '2024/2025 Season',
                '2025/2026 Season',
                'Premier League ' . $this->faker->year,
            ]),
            'status' => 'active',
            'current_week' => 0,
            'total_weeks' => 6,
        ];
    }

    public function completed(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'completed',
                'current_week' => 6,
            ];
        });
    }

    public function inProgress(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'current_week' => $this->faker->numberBetween(1, 5),
            ];
        });
    }
}