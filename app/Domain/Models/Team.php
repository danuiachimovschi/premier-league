<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Domain\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'name',
        'attack_strength',
        'defense_strength',
        'logo_url',
    ];

    protected $casts = [
        'attack_strength' => 'float',
        'defense_strength' => 'float',
    ];

    public function teamSeasons(): HasMany
    {
        return $this->hasMany(TeamSeason::class);
    }

    public function homeMatches(): HasMany
    {
        return $this->hasMany(Game::class, 'home_team_id', 'id');
    }

    public function awayMatches(): HasMany
    {
        return $this->hasMany(Game::class, 'away_team_id');
    }

    public function currentSeasonStats()
    {
        return $this->teamSeasons()
            ->whereHas('season', function ($query) {
                $query->where('status', 'active');
            })
            ->first();
    }
}