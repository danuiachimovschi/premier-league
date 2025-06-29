<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Contracts\Repositories\SeasonWriteRepositoryInterface;
use App\Infrastructure\Repositories\BaseWriteRepository;
use App\Domain\Models\Season;

class SeasonWriteRepository extends BaseWriteRepository implements SeasonWriteRepositoryInterface
{
    public function create(array $data): Season
    {
        return Season::on($this->getConnection())->create($data);
    }

    public function update(Season $season, array $data): Season
    {
        $season->update($data);
        return $season->fresh();
    }

    public function save(Season $season): bool
    {
        return $season->save();
    }

    public function delete(Season $season): bool
    {
        return $season->delete();
    }

    public function resetStatistics(Season $season): void
    {
        $season->teamSeasons()->update([
            'won' => 0,
            'drawn' => 0,
            'lost' => 0,
            'goals_for' => 0,
            'goals_against' => 0,
            'points' => 0,
        ]);
        
        $season->games()->update([
            'is_played' => false,
            'home_goals' => null,
            'away_goals' => null,
        ]);
    }
}