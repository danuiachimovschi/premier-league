<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Domain\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamSeason extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'team_id',
        'season_id',
        'played',
        'won',
        'drawn',
        'lost',
        'goals_for',
        'goals_against',
        'goal_difference',
        'points',
        'championship_probability',
        'form',
    ];

    protected $casts = [
        'played' => 'integer',
        'won' => 'integer',
        'drawn' => 'integer',
        'lost' => 'integer',
        'goals_for' => 'integer',
        'goals_against' => 'integer',
        'goal_difference' => 'integer',
        'points' => 'integer',
        'championship_probability' => 'float',
        'form' => 'array',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function updateStats(int $goalsFor, int $goalsAgainst, string $result): void
    {
        $this->played++;
        $this->goals_for += $goalsFor;
        $this->goals_against += $goalsAgainst;
        $this->goal_difference = $this->goals_for - $this->goals_against;

        switch ($result) {
            case 'W':
                $this->won++;
                $this->points += 3;
                break;
            case 'D':
                $this->drawn++;
                $this->points += 1;
                break;
            case 'L':
                $this->lost++;
                break;
        }

        $form = $this->form ?? [];
        array_push($form, $result);
        if (count($form) > 5) {
            array_shift($form);
        }
        $this->form = $form;

        $this->save();
    }

    public function getPointsPerGame(): float
    {
        return $this->played > 0 ? $this->points / $this->played : 0;
    }

    public function getRemainingMatches(): int
    {
        return ($this->season->total_weeks * 2) - $this->played;
    }
}