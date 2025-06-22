<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Domain\Models\Season;
use App\Domain\Models\Team;
use App\Domain\Models\TeamSeason;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeagueTableControllerTest extends TestCase
{
    use RefreshDatabase;

    private Season $season;
    private Team $team1;
    private Team $team2;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->season = Season::factory()->create();
        $this->team1 = Team::factory()->create(['name' => 'Team A']);
        $this->team2 = Team::factory()->create(['name' => 'Team B']);
        
        TeamSeason::factory()->create([
            'season_id' => $this->season->id,
            'team_id' => $this->team1->id,
            'won' => 5,
            'drawn' => 2,
            'lost' => 1,
            'goals_for' => 15,
            'goals_against' => 8,
            'points' => (5 * 3) + (2 * 1), // 17 points
            'goal_difference' => 15 - 8, // 7
        ]);
        
        TeamSeason::factory()->create([
            'season_id' => $this->season->id,
            'team_id' => $this->team2->id,
            'won' => 3,
            'drawn' => 3,
            'lost' => 2,
            'goals_for' => 10,
            'goals_against' => 9,
            'points' => (3 * 3) + (3 * 1), // 12 points
            'goal_difference' => 10 - 9, // 1
        ]);
    }

    public function test_can_get_league_table_for_season(): void
    {
        $response = $this->getJson("/api/seasons/{$this->season->id}/table");

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
                    'standings' => [
                        '*' => [
                            'team' => [
                                'id',
                                'name'
                            ],
                            'won',
                            'drawn',
                            'lost',
                            'goals_for',
                            'goals_against',
                            'goal_difference',
                            'points',
                            'position'
                        ]
                    ]
                ]
            ])
            ->assertJson([
                'status' => 'success',
                'message' => 'Success'
            ]);

        $standings = $response->json('data.standings');
        $this->assertCount(2, $standings);
        
        $firstPlace = collect($standings)->firstWhere('position', 1);
        $this->assertEquals($this->team1->name, $firstPlace['team']['name']);
        $this->assertEquals(17, $firstPlace['points']);
    }

    public function test_returns_404_for_non_existent_season(): void
    {
        $response = $this->getJson('/api/seasons/999/table');

        $response->assertNotFound();
    }

    public function test_league_table_shows_correct_standings_order(): void
    {
        $response = $this->getJson("/api/seasons/{$this->season->id}/table");

        $standings = $response->json('data.standings');
        
        $positions = collect($standings)->pluck('position')->sort()->values()->toArray();
        $this->assertEquals([1, 2], $positions);
        
        $teamAPosition = collect($standings)->firstWhere('team.name', 'Team A')['position'];
        $teamBPosition = collect($standings)->firstWhere('team.name', 'Team B')['position'];
        
        $this->assertEquals(1, $teamAPosition);
        $this->assertEquals(2, $teamBPosition);
    }

    public function test_standings_include_all_required_statistics(): void
    {
        $response = $this->getJson("/api/seasons/{$this->season->id}/table");

        $standings = $response->json('data.standings');
        $firstTeam = $standings[0];

        $this->assertArrayHasKey('won', $firstTeam);
        $this->assertArrayHasKey('drawn', $firstTeam);
        $this->assertArrayHasKey('lost', $firstTeam);
        $this->assertArrayHasKey('goals_for', $firstTeam);
        $this->assertArrayHasKey('goals_against', $firstTeam);
        $this->assertArrayHasKey('goal_difference', $firstTeam);
        $this->assertArrayHasKey('points', $firstTeam);
        $this->assertArrayHasKey('position', $firstTeam);
    }
}