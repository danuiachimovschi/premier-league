<?php

declare(strict_types=1);

namespace App\Domain\Contracts\Repositories;

use App\Domain\Models\Season;

interface SeasonWriteRepositoryInterface
{
    public function create(array $data): Season;

    public function update(Season $season, array $data): Season;

    public function save(Season $season): bool;

    public function delete(Season $season): bool;

    public function resetStatistics(Season $season): void;
}