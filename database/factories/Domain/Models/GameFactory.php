<?php

namespace Database\Factories\Domain\Models;

use App\Domain\Models\Game;
use App\Domain\Models\Season;
use App\Domain\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

class GameFactory extends Factory
{
    protected $model = Game::class;

    public function definition(): array
    {
        return [
            'home_team_id' => Team::factory(),
            'away_team_id' => Team::factory(),
            'season_id' => Season::factory(),
            'week' => $this->faker->numberBetween(1, 6),
            'home_goals' => null,
            'away_goals' => null,
            'is_played' => false,
            'played_at' => null,
            'game_statistics' => null,
        ];
    }

    public function completed(): self
    {
        return $this->state(function (array $attributes) {
            $homeGoals = $this->faker->numberBetween(0, 5);
            $awayGoals = $this->faker->numberBetween(0, 5);
            
            return [
                'home_goals' => $homeGoals,
                'away_goals' => $awayGoals,
                'is_played' => true,
                'played_at' => now(),
                'game_statistics' => [
                    'home_possession' => $this->faker->numberBetween(30, 70),
                    'away_possession' => $this->faker->numberBetween(30, 70),
                    'home_shots' => $this->faker->numberBetween(5, 20),
                    'away_shots' => $this->faker->numberBetween(5, 20),
                    'home_shots_on_target' => $this->faker->numberBetween(1, 10),
                    'away_shots_on_target' => $this->faker->numberBetween(1, 10),
                    'home_corners' => $this->faker->numberBetween(0, 12),
                    'away_corners' => $this->faker->numberBetween(0, 12),
                    'home_fouls' => $this->faker->numberBetween(5, 20),
                    'away_fouls' => $this->faker->numberBetween(5, 20),
                ],
            ];
        });
    }

    public function scheduled(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'is_played' => false,
                'home_goals' => null,
                'away_goals' => null,
                'played_at' => null,
                'game_statistics' => null,
            ];
        });
    }

    public function forWeek(int $week): self
    {
        return $this->state(function (array $attributes) use ($week) {
            return [
                'week' => $week,
            ];
        });
    }
}