<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Contracts\Repositories\TeamWriteRepositoryInterface;
use App\Infrastructure\Repositories\BaseWriteRepository;
use App\Domain\Models\Team;

class TeamWriteRepository extends BaseWriteRepository implements TeamWriteRepositoryInterface
{
    public function create(array $data): Team
    {
        return Team::on($this->getConnection())->create($data);
    }

    public function update(Team $team, array $data): Team
    {
        $team->update($data);
        return $team->fresh();
    }

    public function save(Team $team): bool
    {
        return $team->save();
    }

    public function delete(Team $team): bool
    {
        return $team->delete();
    }

    public function updateStrengths(Team $team, float $attackStrength, float $defenseStrength): Team
    {
        $team->update([
            'attack_strength' => $attackStrength,
            'defense_strength' => $defenseStrength,
        ]);
        
        return $team->fresh();
    }
}