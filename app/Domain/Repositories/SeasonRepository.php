<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Contracts\Repositories\SeasonRepositoryInterface;
use App\Domain\Models\Season;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SeasonRepository implements SeasonRepositoryInterface
{
    public function find(string $id): ?Season
    {
        return Season::find($id);
    }

    public function all(): Collection
    {
        return Season::orderBy('created_at', 'desc')->get();
    }

    public function getActive(): Collection
    {
        return Season::where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getCurrent(): ?Season
    {
        return Season::where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function create(array $data): Season
    {
        return Season::create($data);
    }

    public function update(Season $season, array $data): Season
    {
        $season->update($data);
        return $season->fresh();
    }

    public function delete(Season $season): bool
    {
        return $season->delete();
    }

    public function findWithRelations(string $id, array $relations): ?Season
    {
        return Season::with($relations)->find($id);
    }

    public function save(Season $season): bool
    {
        return $season->save();
    }

    public function resetStatistics(Season $season): void
    {
        DB::transaction(function () use ($season) {
            $season->matches()->update([
                'is_played' => false,
                'home_goals' => null,
                'away_goals' => null,
                'game_statistics' => null,
                'played_at' => null,
            ]);

            $season->teamSeasons()->update([
                'played' => 0,
                'won' => 0,
                'drawn' => 0,
                'lost' => 0,
                'goals_for' => 0,
                'goals_against' => 0,
                'goal_difference' => 0,
                'points' => 0,
                'form' => null,
                'championship_probability' => 0,
            ]);

            $season->update([
                'current_week' => 0,
                'status' => 'pending',
            ]);
        });
    }
}