<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Contracts\Repositories\TeamSeasonRepositoryInterface;
use App\Domain\Models\Season;
use App\Domain\Models\Team;
use App\Domain\Models\TeamSeason;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TeamSeasonRepository implements TeamSeasonRepositoryInterface
{
    public function find(string $id): ?TeamSeason
    {
        return TeamSeason::find($id);
    }

    public function findByTeamAndSeason(Team $team, Season $season): ?TeamSeason
    {
        return TeamSeason::where('team_id', $team->id)
            ->where('season_id', $season->id)
            ->first();
    }

    public function getBySeason(Season $season): Collection
    {
        return TeamSeason::where('season_id', $season->id)
            ->with('team')
            ->get();
    }

    public function getStandings(Season $season): Collection
    {
        return TeamSeason::where('season_id', $season->id)
            ->with('team')
            ->orderByDesc('points')
            ->orderByDesc('goal_difference')
            ->orderByDesc('goals_for')
            ->get();
    }

    public function create(array $data): TeamSeason
    {
        return TeamSeason::create($data);
    }

    public function update(TeamSeason $teamSeason, array $data): TeamSeason
    {
        $teamSeason->update($data);
        return $teamSeason->fresh();
    }

    public function updateMatchStats(
        TeamSeason $teamSeason,
        int $goalsFor,
        int $goalsAgainst,
        int $points,
        string $result
    ): TeamSeason {
        DB::transaction(function () use ($teamSeason, $goalsFor, $goalsAgainst, $points, $result) {
            $teamSeason->played += 1;
            $teamSeason->goals_for += $goalsFor;
            $teamSeason->goals_against += $goalsAgainst;
            $teamSeason->goal_difference = $teamSeason->goals_for - $teamSeason->goals_against;
            $teamSeason->points += $points;

            switch ($result) {
                case 'W':
                    $teamSeason->won += 1;
                    break;
                case 'D':
                    $teamSeason->drawn += 1;
                    break;
                case 'L':
                    $teamSeason->lost += 1;
                    break;
            }

            $form = $teamSeason->form ?? [];
            array_push($form, $result);
            if (count($form) > 5) {
                array_shift($form);
            }
            $teamSeason->form = $form;

            $teamSeason->save();
        });

        return $teamSeason->fresh();
    }

    public function updateChampionshipProbability(TeamSeason $teamSeason, float $probability): TeamSeason
    {
        $teamSeason->championship_probability = $probability;
        $teamSeason->save();
        
        return $teamSeason->fresh();
    }

    public function resetSeasonStatistics(Season $season): void
    {
        TeamSeason::where('season_id', $season->id)->update([
            'played' => 0,
            'won' => 0,
            'drawn' => 0,
            'lost' => 0,
            'goals_for' => 0,
            'goals_against' => 0,
            'goal_difference' => 0,
            'points' => 0,
            'form' => null,
            'championship_probability' => 0,
        ]);
    }

    public function getWithRelations(Season $season, array $relations): Collection
    {
        return TeamSeason::where('season_id', $season->id)
            ->with($relations)
            ->orderByDesc('points')
            ->orderByDesc('goal_difference')
            ->orderByDesc('goals_for')
            ->get();
    }

    public function save(TeamSeason $teamSeason): bool
    {
        return $teamSeason->save();
    }

    public function getRemainingMatchesCount(TeamSeason $teamSeason): int
    {
        $totalMatches = ($teamSeason->season->teams()->count() - 1) * 2;
        return $totalMatches - $teamSeason->played;
    }
}