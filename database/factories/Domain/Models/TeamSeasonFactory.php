<?php

namespace Database\Factories\Domain\Models;

use App\Domain\Models\Season;
use App\Domain\Models\Team;
use App\Domain\Models\TeamSeason;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeamSeasonFactory extends Factory
{
    protected $model = TeamSeason::class;

    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'season_id' => Season::factory(),
            'points' => 0,
            'played' => 0,
            'won' => 0,
            'drawn' => 0,
            'lost' => 0,
            'goals_for' => 0,
            'goals_against' => 0,
            'goal_difference' => 0,
            'form' => null,
        ];
    }

    public function withStats(array $stats): self
    {
        return $this->state(function (array $attributes) use ($stats) {
            $goalsFor = $stats['goals_for'] ?? 0;
            $goalsAgainst = $stats['goals_against'] ?? 0;
            
            return array_merge($stats, [
                'goal_difference' => $goalsFor - $goalsAgainst,
            ]);
        });
    }

    public function inProgress(): self
    {
        return $this->state(function (array $attributes) {
            $played = $this->faker->numberBetween(1, 10);
            $won = $this->faker->numberBetween(0, $played);
            $remaining = $played - $won;
            $drawn = $this->faker->numberBetween(0, $remaining);
            $lost = $remaining - $drawn;
            $goalsFor = $this->faker->numberBetween(0, $played * 3);
            $goalsAgainst = $this->faker->numberBetween(0, $played * 3);

            return [
                'played' => $played,
                'won' => $won,
                'drawn' => $drawn,
                'lost' => $lost,
                'points' => ($won * 3) + $drawn,
                'goals_for' => $goalsFor,
                'goals_against' => $goalsAgainst,
                'goal_difference' => $goalsFor - $goalsAgainst,
                'form' => $this->faker->randomElements(['W', 'D', 'L'], 5),
            ];
        });
    }
}