<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Contracts\Repositories\TeamReadRepositoryInterface;
use App\Infrastructure\Repositories\BaseReadRepository;
use App\Domain\Models\Team;
use Illuminate\Support\Collection;

class TeamReadRepository extends BaseReadRepository implements TeamReadRepositoryInterface
{
    public function find(string $id): ?Team
    {
        return Team::on($this->getConnection())->find($id);
    }

    public function findByName(string $name): ?Team
    {
        return Team::on($this->getConnection())->where('name', $name)->first();
    }

    public function all(): Collection
    {
        return Team::on($this->getConnection())->orderBy('name')->get();
    }

    public function findMany(array $ids): Collection
    {
        return Team::on($this->getConnection())->whereIn('id', $ids)->get();
    }

    public function getWithSeasonStats(string $seasonId): Collection
    {
        return Team::on($this->getConnection())->with(['teamSeasons' => function ($query) use ($seasonId) {
            $query->where('season_id', $seasonId);
        }])->orderBy('name')->get();
    }
}