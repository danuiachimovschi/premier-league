<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Contracts\Repositories\TeamRepositoryInterface;
use App\Domain\Models\Team;
use Illuminate\Support\Collection;

class TeamRepository implements TeamRepositoryInterface
{
    public function find(string $id): ?Team
    {
        return Team::find($id);
    }

    public function findByName(string $name): ?Team
    {
        return Team::where('name', $name)->first();
    }

    public function all(): Collection
    {
        return Team::orderBy('name')->get();
    }

    public function findMany(array $ids): Collection
    {
        return Team::whereIn('id', $ids)->get();
    }

    public function create(array $data): Team
    {
        return Team::create($data);
    }

    public function update(Team $team, array $data): Team
    {
        $team->update($data);
        return $team->fresh();
    }

    public function delete(Team $team): bool
    {
        return $team->delete();
    }

    public function updateStrengths(Team $team, float $attackStrength, float $defenseStrength): Team
    {
        $team->update([
            'attack_strength' => $attackStrength,
            'defense_strength' => $defenseStrength,
        ]);
        
        return $team->fresh();
    }

    public function getWithSeasonStats(string $seasonId): Collection
    {
        return Team::with(['teamSeasons' => function ($query) use ($seasonId) {
            $query->where('season_id', $seasonId);
        }])->orderBy('name')->get();
    }

    public function save(Team $team): bool
    {
        return $team->save();
    }
}