<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Domain\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Game extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'season_id',
        'home_team_id',
        'away_team_id',
        'home_goals',
        'away_goals',
        'week',
        'game_statistics',
        'is_played',
        'played_at',
    ];

    protected $casts = [
        'home_goals' => 'integer',
        'away_goals' => 'integer',
        'week' => 'integer',
        'game_statistics' => 'array',
        'is_played' => 'boolean',
        'played_at' => 'datetime',
    ];

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    public function scopePlayed($query)
    {
        return $query->where('is_played', true);
    }

    public function scopeUnplayed($query)
    {
        return $query->where('is_played', false);
    }

    public function scopeForWeek($query, int $week)
    {
        return $query->where('week', $week);
    }

    public function getWinner(): ?string
    {
        if (!$this->is_played) {
            return null;
        }

        if ($this->home_goals > $this->away_goals) {
            return 'home';
        } elseif ($this->away_goals > $this->home_goals) {
            return 'away';
        }

        return 'draw';
    }

}