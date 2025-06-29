<?php

declare(strict_types=1);

namespace App\Domain\Contracts\Repositories;

use App\Domain\Models\Team;
use Illuminate\Support\Collection;

interface TeamReadRepositoryInterface
{
    public function find(string $id): ?Team;

    public function findByName(string $name): ?Team;

    public function all(): Collection;

    public function findMany(array $ids): Collection;

    public function getWithSeasonStats(string $seasonId): Collection;
}