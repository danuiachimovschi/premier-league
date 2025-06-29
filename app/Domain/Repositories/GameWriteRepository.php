<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Contracts\Repositories\GameWriteRepositoryInterface;
use App\Infrastructure\Repositories\BaseWriteRepository;
use App\Domain\Models\Game;

class GameWriteRepository extends BaseWriteRepository implements GameWriteRepositoryInterface
{
    public function create(array $data): Game
    {
        return Game::on($this->getConnection())->create($data);
    }

    public function update(Game $game, array $data): Game
    {
        $game->update($data);
        return $game->fresh();
    }

    public function save(Game $game): bool
    {
        return $game->save();
    }

    public function deleteBySeasonId(string $seasonId): void
    {
        Game::on($this->getConnection())->where('season_id', $seasonId)->delete();
    }
}