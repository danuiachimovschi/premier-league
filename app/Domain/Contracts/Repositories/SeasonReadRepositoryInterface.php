<?php

declare(strict_types=1);

namespace App\Domain\Contracts\Repositories;

use App\Domain\Models\Season;
use Illuminate\Support\Collection;

interface SeasonReadRepositoryInterface
{
    public function find(string $id): ?Season;

    public function all(): Collection;

    public function getActive(): Collection;

    public function getCurrent(): ?Season;

    public function findWithRelations(string $id, array $relations): ?Season;
}