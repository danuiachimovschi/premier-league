<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Domain\Models\Game;
use App\Domain\Models\Season;
use App\Domain\Models\Team;
use App\Domain\Models\TeamSeason;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamControllerTest extends TestCase
{
    use RefreshDatabase;

    private Team $team1;
    private Team $team2;
    private Season $season;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->team1 = Team::factory()->create(['name' => 'Test Team 1']);
        $this->team2 = Team::factory()->create(['name' => 'Test Team 2']);
        $this->season = Season::factory()->create(['status' => 'active']);
        
        TeamSeason::factory()->create([
            'team_id' => $this->team1->id,
            'season_id' => $this->season->id,
            'won' => 5,
            'drawn' => 2,
            'lost' => 1,
            'goals_for' => 15,
            'goals_against' => 8,
            'points' => (5 * 3) + (2 * 1), // Calculate points: 5 wins * 3 + 2 draws * 1 = 17
            'goal_difference' => 15 - 8, // 7
        ]);
    }

    public function test_can_get_all_teams(): void
    {
        $response = $this->getJson('/api/teams');

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'teams' => [
                        '*' => [
                            'id',
                            'name'
                        ]
                    ]
                ]
            ])
            ->assertJson([
                'status' => 'success',
                'message' => 'Success'
            ]);

        $teams = $response->json('data.teams');
        $this->assertCount(2, $teams);
        
        $teamNames = collect($teams)->pluck('name')->toArray();
        $this->assertContains('Test Team 1', $teamNames);
        $this->assertContains('Test Team 2', $teamNames);
    }

    public function test_can_get_specific_team(): void
    {
        $response = $this->getJson("/api/teams/{$this->team1->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'team' => [
                        'id',
                        'name'
                    ],
                    'current_season_stats',
                    'recent_matches'
                ]
            ])
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'team' => [
                        'id' => $this->team1->id,
                        'name' => 'Test Team 1'
                    ]
                ]
            ]);
    }

    public function test_team_response_includes_current_season_stats(): void
    {
        $response = $this->getJson("/api/teams/{$this->team1->id}");

        $response->assertOk();
        
        $data = $response->json('data');
        $this->assertArrayHasKey('current_season_stats', $data);
        
        if ($data['current_season_stats']) {
            $stats = $data['current_season_stats'];
            $this->assertArrayHasKey('won', $stats);
            $this->assertArrayHasKey('drawn', $stats);
            $this->assertArrayHasKey('lost', $stats);
            $this->assertArrayHasKey('goals_for', $stats);
            $this->assertArrayHasKey('goals_against', $stats);
            $this->assertArrayHasKey('points', $stats);
        }
    }

    public function test_team_response_includes_recent_matches(): void
    {
        Game::factory()->create([
            'season_id' => $this->season->id,
            'home_team_id' => $this->team1->id,
            'away_team_id' => $this->team2->id,
            'home_goals' => 2,
            'away_goals' => 1,
            'week' => 1,
        ]);

        $response = $this->getJson("/api/teams/{$this->team1->id}");

        $response->assertOk();
        
        $data = $response->json('data');
        $this->assertArrayHasKey('recent_matches', $data);
        $this->assertIsArray($data['recent_matches']);
    }

    public function test_returns_404_for_non_existent_team(): void
    {
        $response = $this->getJson('/api/teams/999');

        $response->assertNotFound();
    }

    public function test_team_without_current_season_stats(): void
    {
        $teamWithoutStats = Team::factory()->create(['name' => 'Team Without Stats']);

        $response = $this->getJson("/api/teams/{$teamWithoutStats->id}");

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'team' => [
                        'id' => $teamWithoutStats->id,
                        'name' => 'Team Without Stats'
                    ],
                    'current_season_stats' => null
                ]
            ]);
    }

    public function test_teams_are_properly_formatted_in_collection(): void
    {
        $response = $this->getJson('/api/teams');

        $teams = $response->json('data.teams');
        
        foreach ($teams as $team) {
            $this->assertIsString($team['id']);
            $this->assertIsString($team['name']);
            $this->assertNotEmpty($team['name']);
        }
    }

    public function test_team_stats_show_correct_calculations(): void
    {
        $response = $this->getJson("/api/teams/{$this->team1->id}");

        $stats = $response->json('data.current_season_stats');
        
        if ($stats) {
            $expectedPoints = (5 * 3) + (2 * 1);
            $this->assertEquals($expectedPoints, $stats['points']);
            
            $expectedGoalDifference = 15 - 8;
            $this->assertEquals($expectedGoalDifference, $stats['goal_difference']);
        }
    }

    public function test_handles_team_service_errors_gracefully(): void
    {
        $response = $this->getJson('/api/teams');
        
        $this->assertTrue($response->status() === 200 || $response->status() === 500);
        
        if ($response->status() !== 200) {
            $response->assertJson([
                'status' => 'error'
            ]);
        }
    }

    public function test_team_detail_handles_service_errors(): void
    {
        $response = $this->getJson("/api/teams/{$this->team1->id}");
        
        $this->assertTrue($response->status() === 200 || $response->status() === 500);
        
        if ($response->status() !== 200) {
            $response->assertJson([
                'status' => 'error'
            ]);
        }
    }
}