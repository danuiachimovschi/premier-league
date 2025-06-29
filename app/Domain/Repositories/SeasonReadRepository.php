<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Contracts\Repositories\SeasonReadRepositoryInterface;
use App\Infrastructure\Repositories\BaseReadRepository;
use App\Domain\Models\Season;
use Illuminate\Support\Collection;

class SeasonReadRepository extends BaseReadRepository implements SeasonReadRepositoryInterface
{
    public function find(string $id): ?Season
    {
        return Season::on($this->getConnection())->find($id);
    }

    public function all(): Collection
    {
        return Season::on($this->getConnection())->orderBy('created_at', 'desc')->get();
    }

    public function getActive(): Collection
    {
        return Season::on($this->getConnection())->where('status', 'active')->get();
    }

    public function getCurrent(): ?Season
    {
        return Season::on($this->getConnection())->where('status', 'active')->first();
    }

    public function findWithRelations(string $id, array $relations): ?Season
    {
        return Season::on($this->getConnection())->with($relations)->find($id);
    }
}