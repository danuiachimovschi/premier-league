<?php

declare(strict_types=1);

namespace App\Domain\Contracts\Repositories;

use App\Domain\Models\Game;

interface GameWriteRepositoryInterface
{
    public function create(array $data): Game;

    public function update(Game $game, array $data): Game;

    public function save(Game $game): bool;

    public function deleteBySeasonId(string $seasonId): void;
}