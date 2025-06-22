<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Contracts\Repositories\GameRepositoryInterface;
use App\Domain\Models\Game;
use App\Domain\Models\Season;
use App\Domain\Models\Team;
use Illuminate\Support\Collection;

class GameRepository implements GameRepositoryInterface
{
    public function find(string $id): ?Game
    {
        return Game::find($id);
    }

    public function getBySeasonId(string $seasonId): Collection
    {
        return Game::where('season_id', $seasonId)
            ->orderBy('week')
            ->orderBy('played_at')
            ->get();
    }

    public function getBySeasonAndWeek(Season $season, int $week): Collection
    {
        return Game::where('season_id', $season->id)
            ->where('week', $week)
            ->with(['homeTeam', 'awayTeam'])
            ->orderBy('played_at')
            ->get();
    }

    public function getPlayedGames(Season $season): Collection
    {
        return Game::where('season_id', $season->id)
            ->where('is_played', true)
            ->with(['homeTeam', 'awayTeam'])
            ->orderBy('played_at')
            ->get();
    }

    public function getUnplayedGames(Season $season): Collection
    {
        return Game::where('season_id', $season->id)
            ->where('is_played', false)
            ->with(['homeTeam', 'awayTeam'])
            ->orderBy('week')
            ->orderBy('played_at')
            ->get();
    }

    public function getGamesBetweenTeams(Season $season, Team $team1, Team $team2): Collection
    {
        return Game::where('season_id', $season->id)
            ->where(function ($query) use ($team1, $team2) {
                $query->where(function ($q) use ($team1, $team2) {
                    $q->where('home_team_id', $team1->id)
                        ->where('away_team_id', $team2->id);
                })->orWhere(function ($q) use ($team1, $team2) {
                    $q->where('home_team_id', $team2->id)
                        ->where('away_team_id', $team1->id);
                });
            })
            ->orderBy('played_at')
            ->get();
    }

    public function create(array $data): Game
    {
        return Game::create($data);
    }

    public function update(Game $game, array $data): Game
    {
        $game->update($data);
        return $game->fresh();
    }

    public function deleteBySeasonId(string $seasonId): void
    {
        Game::where('season_id', $seasonId)->delete();
    }

    public function getWithRelations(Season $season, array $relations): Collection
    {
        return Game::where('season_id', $season->id)
            ->with($relations)
            ->orderBy('week')
            ->orderBy('played_at')
            ->get();
    }

    public function save(Game $game): bool
    {
        return $game->save();
    }
}