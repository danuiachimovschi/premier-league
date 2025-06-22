<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Domain\Models\Season;
use App\Domain\Models\Team;
use App\Domain\Models\TeamSeason;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeasonControllerTest extends TestCase
{
    use RefreshDatabase;

    private Season $activeSeason;
    private Season $inactiveSeason;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->activeSeason = Season::factory()->create([
            'name' => 'Active Season',
            'status' => 'active'
        ]);
        
        $this->inactiveSeason = Season::factory()->create([
            'name' => 'Inactive Season',
            'status' => 'completed'
        ]);
    }

    public function test_can_get_active_seasons(): void
    {
        $response = $this->getJson('/api/seasons');

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'status',
                        'current_week'
                    ]
                ]
            ])
            ->assertJson([
                'status' => 'success',
                'message' => 'Success'
            ]);

        $seasons = $response->json('data');
        $this->assertNotEmpty($seasons);
        
        foreach ($seasons as $season) {
            $this->assertEquals('active', $season['status']);
        }
    }

    public function test_can_create_new_season(): void
    {
        // Create teams for the season with unique names
        for ($i = 1; $i <= 20; $i++) {
            Team::factory()->create(['name' => "Test Team {$i}"]);
        }
        
        $seasonData = [
            'name' => 'New Test Season'
        ];

        $response = $this->postJson('/api/seasons', $seasonData);

        $response->assertCreated()
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'name',
                    'status',
                    'current_week'
                ]
            ])
            ->assertJson([
                'status' => 'success',
                'message' => 'Season created successfully',
                'data' => [
                    'name' => 'New Test Season',
                    'status' => 'active'
                ]
            ]);

        $this->assertDatabaseHas('seasons', [
            'name' => 'New Test Season',
            'status' => 'active'
        ]);
    }

    public function test_create_season_validates_required_fields(): void
    {
        $response = $this->postJson('/api/seasons', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_create_season_validates_unique_name(): void
    {
        $response = $this->postJson('/api/seasons', [
            'name' => $this->activeSeason->name
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_can_get_specific_season(): void
    {
        $response = $this->getJson("/api/seasons/{$this->activeSeason->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'name',
                    'status',
                    'current_week'
                ]
            ])
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $this->activeSeason->id,
                    'name' => $this->activeSeason->name,
                    'status' => 'active'
                ]
            ]);
    }

    public function test_returns_404_for_non_existent_season(): void
    {
        $response = $this->getJson('/api/seasons/999');

        $response->assertNotFound();
    }

    public function test_can_reset_season(): void
    {
        $team = Team::factory()->create();
        TeamSeason::factory()->create([
            'season_id' => $this->activeSeason->id,
            'team_id' => $team->id,
            'won' => 5,
            'points' => 15
        ]);

        $response = $this->deleteJson("/api/seasons/{$this->activeSeason->id}/reset");

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'name',
                    'status',
                    'current_week'
                ]
            ])
            ->assertJson([
                'status' => 'success',
                'message' => 'Season reset successfully'
            ]);
    }

    public function test_reset_returns_404_for_non_existent_season(): void
    {
        $response = $this->deleteJson('/api/seasons/999/reset');

        $response->assertNotFound();
    }

    public function test_create_season_handles_service_errors(): void
    {
        $response = $this->postJson('/api/seasons', [
            'name' => ''
        ]);

        $response->assertUnprocessable();
    }

    public function test_reset_season_handles_errors_gracefully(): void
    {
        $response = $this->deleteJson("/api/seasons/{$this->inactiveSeason->id}/reset");

        $this->assertTrue(
            $response->status() === 200 || $response->status() === 400
        );
        
        if ($response->status() === 400) {
            $response->assertJson([
                'status' => 'error'
            ]);
        }
    }

    public function test_season_name_must_be_string(): void
    {
        $response = $this->postJson('/api/seasons', [
            'name' => 123
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_season_name_has_maximum_length(): void
    {
        $longName = str_repeat('a', 256);
        
        $response = $this->postJson('/api/seasons', [
            'name' => $longName
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_active_seasons_only_returns_active(): void
    {
        $response = $this->getJson('/api/seasons');

        $seasons = $response->json('data');
        $activeSeasonIds = collect($seasons)->pluck('id')->toArray();
        
        $this->assertContains($this->activeSeason->id, $activeSeasonIds);
        $this->assertNotContains($this->inactiveSeason->id, $activeSeasonIds);
    }
}