<?php

declare(strict_types=1);

namespace App\Domain\Contracts\Repositories;

use App\Domain\Models\Team;

interface TeamWriteRepositoryInterface
{
    public function create(array $data): Team;

    public function update(Team $team, array $data): Team;

    public function save(Team $team): bool;

    public function delete(Team $team): bool;

    public function updateStrengths(Team $team, float $attackStrength, float $defenseStrength): Team;
}