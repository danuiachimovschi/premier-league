<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Domain\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Season extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'name',
        'status',
        'current_week',
        'total_weeks',
    ];

    protected $casts = [
        'current_week' => 'integer',
        'total_weeks' => 'integer',
    ];

    public function games(): HasMany
    {
        return $this->hasMany(Game::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(Game::class);
    }

    public function teamSeasons(): HasMany
    {
        return $this->hasMany(TeamSeason::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function isCompleted(): bool
    {
        return $this->current_week >= $this->total_weeks;
    }

    public function getRemainingWeeks(): int
    {
        return max(0, $this->total_weeks - $this->current_week);
    }
}