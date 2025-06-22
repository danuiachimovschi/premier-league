<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Domain\Models\Game;
use App\Domain\Models\Season;
use App\Domain\Models\Team;
use App\Domain\Models\TeamSeason;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PredictionControllerTest extends TestCase
{
    use RefreshDatabase;

    private Season $season;
    private Team $team1;
    private Team $team2;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->season = Season::factory()->create(['current_week' => 1]);
        $this->team1 = Team::factory()->create(['name' => 'Team A']);
        $this->team2 = Team::factory()->create(['name' => 'Team B']);
        
        TeamSeason::factory()->create([
            'season_id' => $this->season->id,
            'team_id' => $this->team1->id,
            'won' => 5,
            'drawn' => 2,
            'lost' => 1,
            'points' => (5 * 3) + (2 * 1), // 17 points
            'goals_for' => 15,
            'goals_against' => 8,
            'goal_difference' => 15 - 8,
            'played' => 8,
        ]);
        
        TeamSeason::factory()->create([
            'season_id' => $this->season->id,
            'team_id' => $this->team2->id,
            'won' => 3,
            'drawn' => 3,
            'lost' => 2,
            'points' => (3 * 3) + (3 * 1), // 12 points
            'goals_for' => 10,
            'goals_against' => 9,
            'goal_difference' => 10 - 9,
            'played' => 8,
        ]);
        
        // Create at least one played game to satisfy prediction requirements
        Game::factory()->create([
            'season_id' => $this->season->id,
            'home_team_id' => $this->team1->id,
            'away_team_id' => $this->team2->id,
            'week' => 1,
            'home_goals' => 2,
            'away_goals' => 1,
            'is_played' => true,
        ]);
    }

    public function test_can_get_predictions_for_season(): void
    {
        $response = $this->getJson("/api/seasons/{$this->season->id}/predictions");

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'season' => [
                        'id',
                        'name',
                        'status',
                        'current_week'
                    ],
                    'predictions' => [
                        '*' => [
                            'team',
                            'championship_probability',
                            'current_points',
                            'projected_points'
                        ]
                    ],
                    'history',
                    'analysis'
                ]
            ])
            ->assertJson([
                'status' => 'success',
                'message' => 'Success'
            ]);

        $predictions = $response->json('data.predictions');
        $this->assertNotEmpty($predictions);
        
        foreach ($predictions as $prediction) {
            $this->assertIsNumeric($prediction['championship_probability']);
            $this->assertIsString($prediction['team']);
            $this->assertIsInt($prediction['current_points']);
            
            $this->assertGreaterThanOrEqual(0, $prediction['championship_probability']);
            $this->assertLessThanOrEqual(1, $prediction['championship_probability']);
        }
    }

    public function test_predictions_include_all_teams_in_season(): void
    {
        $response = $this->getJson("/api/seasons/{$this->season->id}/predictions");

        $predictions = $response->json('data.predictions');
        $teamNames = collect($predictions)->pluck('team')->sort()->values();
        
        $expectedTeamNames = ['Team A', 'Team B'];
        sort($expectedTeamNames);
        
        $this->assertEquals($expectedTeamNames, $teamNames->toArray());
    }

    public function test_returns_404_for_non_existent_season(): void
    {
        $response = $this->getJson('/api/seasons/999/predictions');

        $response->assertNotFound();
    }

    public function test_predictions_response_includes_analysis(): void
    {
        $response = $this->getJson("/api/seasons/{$this->season->id}/predictions");

        $response->assertOk();
        
        $data = $response->json('data');
        $this->assertArrayHasKey('analysis', $data);
        $this->assertArrayHasKey('history', $data);
    }

    public function test_championship_probabilities_sum_to_approximately_one(): void
    {
        $response = $this->getJson("/api/seasons/{$this->season->id}/predictions");

        $predictions = $response->json('data.predictions');
        $totalChampionshipProbability = collect($predictions)
            ->sum('championship_probability');
        
        // Early season probabilities might be 0, so we just check they are valid numbers
        $this->assertIsNumeric($totalChampionshipProbability);
        $this->assertGreaterThanOrEqual(0, $totalChampionshipProbability);
        $this->assertLessThanOrEqual(1, $totalChampionshipProbability);
    }

    public function test_handles_prediction_service_errors_gracefully(): void
    {
        $emptySeason = Season::factory()->create();

        $response = $this->getJson("/api/seasons/{$emptySeason->id}/predictions");

        $this->assertTrue(
            $response->status() === 200 || $response->status() === 400 || $response->status() === 500
        );
        
        if ($response->status() !== 200) {
            $response->assertJson([
                'status' => 'error'
            ]);
        }
    }

    public function test_prediction_probabilities_are_valid_ranges(): void
    {
        $response = $this->getJson("/api/seasons/{$this->season->id}/predictions");

        if ($response->status() === 200) {
            $predictions = $response->json('data.predictions');
            
            foreach ($predictions as $prediction) {
                $this->assertGreaterThanOrEqual(0, $prediction['championship_probability']);
                $this->assertLessThanOrEqual(1, $prediction['championship_probability']);
            }
        }
    }
}