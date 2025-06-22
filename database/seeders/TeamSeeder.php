<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Models\Team;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        $teams = [
            [
                'name' => 'Arsenal',
                'attack_strength' => 2.2,
                'defense_strength' => 1.8,
                'logo_url' => null,
            ],
            [
                'name' => 'Chelsea',
                'attack_strength' => 1.9,
                'defense_strength' => 2.1,
                'logo_url' => null,
            ],
            [
                'name' => 'Liverpool',
                'attack_strength' => 2.3,
                'defense_strength' => 1.7,
                'logo_url' => null,
            ],
            [
                'name' => 'Manchester City',
                'attack_strength' => 2.5,
                'defense_strength' => 2.0,
                'logo_url' => null,
            ],
        ];

        foreach ($teams as $teamData) {
            Team::firstOrCreate(
                ['name' => $teamData['name']],
                $teamData
            );
        }
    }
}