<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Domain\Contracts\Repositories\TeamReadRepositoryInterface;
use App\Domain\Contracts\Services\TeamServiceInterface;
use App\Domain\Models\Team;
use Illuminate\Support\Collection;

class TeamService implements TeamServiceInterface
{
    public function __construct(
        private readonly TeamReadRepositoryInterface $teamReadRepository,
    ) {}

    public function getAllTeams(): Collection
    {
        return $this->teamReadRepository->all();
    }

    public function getTeamWithStats(Team $team): array
    {
        $team->load(['teamSeasons' => function ($query) {
            $query->whereHas('season', function ($q) {
                $q->where('status', 'active');
            })->with('season');
        }]);

        $currentStats = $team->currentSeasonStats();
        $recentMatches = $this->getRecentMatches($team);

        return [
            'team' => $team,
            'current_season_stats' => $currentStats,
            'recent_matches' => $recentMatches,
        ];
    }

    public function getRecentMatches(Team $team, int $limit = 5): Collection
    {
        $homeMatches = $team->homeMatches()
            ->where('is_played', true)
            ->with(['awayTeam', 'season'])
            ->orderBy('played_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($match) {
                return [
                    'id' => $match->id,
                    'date' => $match->played_at,
                    'home_team' => $match->homeTeam->name,
                    'away_team' => $match->awayTeam->name,
                    'home_goals' => $match->home_goals,
                    'away_goals' => $match->away_goals,
                    'result' => $match->getWinner() === 'home' ? 'W' : ($match->getWinner() === 'draw' ? 'D' : 'L'),
                    'venue' => 'home',
                ];
            });

        $awayMatches = $team->awayMatches()
            ->where('is_played', true)
            ->with(['homeTeam', 'season'])
            ->orderBy('played_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($match) {
                return [
                    'id' => $match->id,
                    'date' => $match->played_at,
                    'home_team' => $match->homeTeam->name,
                    'away_team' => $match->awayTeam->name,
                    'home_goals' => $match->home_goals,
                    'away_goals' => $match->away_goals,
                    'result' => $match->getWinner() === 'away' ? 'W' : ($match->getWinner() === 'draw' ? 'D' : 'L'),
                    'venue' => 'away',
                ];
            });

        return $homeMatches->concat($awayMatches)
            ->sortByDesc('date')
            ->take($limit)
            ->values();
    }
}