<?php

declare(strict_types=1);

namespace App\Domain\Contracts\Repositories;

use App\Domain\Models\Season;
use Illuminate\Support\Collection;

interface SeasonRepositoryInterface
{
    public function find(string $id): ?Season;

    public function all(): Collection;

    public function getActive(): Collection;

    public function getCurrent(): ?Season;

    public function create(array $data): Season;

    public function update(Season $season, array $data): Season;

    public function delete(Season $season): bool;

    public function findWithRelations(string $id, array $relations): ?Season;

    public function save(Season $season): bool;

    public function resetStatistics(Season $season): void;
}