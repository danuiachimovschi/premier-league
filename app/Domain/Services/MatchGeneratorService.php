<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Domain\Contracts\Services\MatchGeneratorServiceInterface;
use App\Domain\Exceptions\SeasonException;
use App\Domain\Models\Game;
use App\Domain\Models\Season;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class MatchGeneratorService implements MatchGeneratorServiceInterface
{
    public function generateSeasonMatches(Season $season): void
    {
        $teams = $season->teamSeasons()->with('team')->get()->pluck('team');
        
        $requiredTeams = config('league.teams.required_count', 4);
        if ($teams->count() !== $requiredTeams) {
            throw SeasonException::insufficientTeams();
        }

        $teamsArray = $teams->toArray();
        $matches = [];

        $schedule = [
            1 => [[0, 1], [2, 3]],
            2 => [[0, 2], [1, 3]],
            3 => [[0, 3], [1, 2]],
            4 => [[1, 0], [3, 2]],
            5 => [[2, 0], [3, 1]],
            6 => [[3, 0], [2, 1]]
        ];

        foreach ($schedule as $week => $weekMatches) {
            foreach ($weekMatches as $match) {
                $homeTeamIndex = $match[0];
                $awayTeamIndex = $match[1];
                
                $matches[] = [
                    'season_id' => $season->id,
                    'home_team_id' => $teamsArray[$homeTeamIndex]['id'],
                    'away_team_id' => $teamsArray[$awayTeamIndex]['id'],
                    'week' => $week,
                ];
            }
        }

        Game::insert(array_map(function ($match) {
            return array_merge($match, [
                'id' => Str::uuid(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }, $matches));
    }

    public function generateWeekMatches(Season $season, int $week): Collection
    {
        $weekMatches = $this->getWeekMatches($season, $week);
        
        if ($weekMatches->isNotEmpty()) {
            return $weekMatches;
        }

        $teams = $season->teamSeasons()->with('team')->get()->pluck('team');
        
        $requiredTeams = config('league.teams.required_count', 4);
        if ($teams->count() !== $requiredTeams) {
            throw SeasonException::insufficientTeams();
        }

        $teamsArray = $teams->toArray();
        $schedule = $this->getSchedule();

        if (!isset($schedule[$week])) {
            return collect();
        }

        $matches = [];
        foreach ($schedule[$week] as $match) {
            $homeTeamIndex = $match[0];
            $awayTeamIndex = $match[1];
            
            $gameData = [
                'id' => Str::uuid(),
                'season_id' => $season->id,
                'home_team_id' => $teamsArray[$homeTeamIndex]['id'],
                'away_team_id' => $teamsArray[$awayTeamIndex]['id'],
                'week' => $week,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            $game = Game::create($gameData);
            $matches[] = $game;
        }

        return collect($matches);
    }

    public function getWeekMatches(Season $season, int $week): Collection
    {
        return $season->matches()
            ->where('week', $week)
            ->with(['homeTeam', 'awayTeam'])
            ->get();
    }

    public function hasWeekMatches(Season $season, int $week): bool
    {
        return $season->matches()->where('week', $week)->exists();
    }

    private function getSchedule(): array
    {
        return [
            1 => [[0, 1], [2, 3]],
            2 => [[0, 2], [1, 3]],
            3 => [[0, 3], [1, 2]],
            4 => [[1, 0], [3, 2]],
            5 => [[2, 0], [3, 1]],
            6 => [[3, 0], [2, 1]]
        ];
    }
}