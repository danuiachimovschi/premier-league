<?php

declare(strict_types=1);

namespace App\Domain\Contracts\Repositories;

use App\Domain\Models\Team;
use Illuminate\Support\Collection;

interface TeamRepositoryInterface
{
    public function find(string $id): ?Team;

    public function findByName(string $name): ?Team;

    public function all(): Collection;

    public function findMany(array $ids): Collection;

    public function create(array $data): Team;

    public function update(Team $team, array $data): Team;

    public function delete(Team $team): bool;

    public function updateStrengths(Team $team, float $attackStrength, float $defenseStrength): Team;

    public function getWithSeasonStats(string $seasonId): Collection;

    public function save(Team $team): bool;
}