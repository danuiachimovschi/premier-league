<?php

namespace Database\Factories\Domain\Models;

use App\Domain\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TeamFactory extends Factory
{
    protected $model = Team::class;

    protected array $teams = [
        ['name' => 'Arsenal', 'founded' => 1886, 'stadium' => 'Emirates Stadium'],
        ['name' => 'Chelsea', 'founded' => 1905, 'stadium' => 'Stamford Bridge'],
        ['name' => 'Liverpool', 'founded' => 1892, 'stadium' => 'Anfield'],
        ['name' => 'Manchester United', 'founded' => 1878, 'stadium' => 'Old Trafford'],
        ['name' => 'Manchester City', 'founded' => 1880, 'stadium' => 'Etihad Stadium'],
        ['name' => 'Tottenham Hotspur', 'founded' => 1882, 'stadium' => 'Tottenham Hotspur Stadium'],
        ['name' => 'Newcastle United', 'founded' => 1892, 'stadium' => 'St James\' Park'],
        ['name' => 'Brighton & Hove Albion', 'founded' => 1901, 'stadium' => 'American Express Stadium'],
    ];

    protected static int $teamIndex = 0;

    public function definition(): array
    {
        $team = $this->teams[self::$teamIndex % count($this->teams)];
        self::$teamIndex++;

        return [
            'name' => $team['name'],
            'attack_strength' => $this->faker->randomFloat(2, 60, 90),
            'defense_strength' => $this->faker->randomFloat(2, 60, 90),
            'logo_url' => 'https://via.placeholder.com/200x200.png?text=' . urlencode($team['name']),
        ];
    }

    public function withCustomName(string $name): self
    {
        return $this->state(function (array $attributes) use ($name) {
            return [
                'name' => $name,
                'logo_url' => 'https://via.placeholder.com/200x200.png?text=' . urlencode($name),
            ];
        });
    }
}