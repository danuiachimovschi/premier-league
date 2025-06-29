<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Domain\Contracts\StatisticsServiceInterface;
use App\Domain\DTOs\LeagueTableEntry;
use App\Domain\DTOs\MatchResult;
use App\Domain\DTOs\TeamStatistics;
use App\Domain\Enums\MatchOutcome;
use App\Domain\Services\TeamStatisticsRepositoryAdapter;
use App\Domain\ValueObjects\Goals;
use App\Domain\ValueObjects\Points;
use App\Domain\ValueObjects\Team;

final class TeamStatisticsService implements StatisticsServiceInterface
{
    private array $matches = [];

    public function __construct(
        private readonly TeamStatisticsRepositoryAdapter $teamRepository,
        private readonly float $learningRate = 0.3,
    ) {}

    public function addMatchResult(MatchResult $matchResult): void
    {
        $this->matches[] = $matchResult;

        $outcome = match(true) {
            $matchResult->isHomeWin() => MatchOutcome::HOME_WIN,
            $matchResult->isAwayWin() => MatchOutcome::AWAY_WIN,
            default => MatchOutcome::DRAW,
        };

        $this->updateTeamStats(
            team: $matchResult->homeTeam,
            goalsFor: $matchResult->homeGoals,
            goalsAgainst: $matchResult->awayGoals,
            points: new Points($outcome->getPoints(isHomeTeam: true)),
            matchStats: $matchResult->statistics,
            isHome: true,
        );

        $this->updateTeamStats(
            team: $matchResult->awayTeam,
            goalsFor: $matchResult->awayGoals,
            goalsAgainst: $matchResult->homeGoals,
            points: new Points($outcome->getPoints(isHomeTeam: false)),
            matchStats: $matchResult->statistics,
            isHome: false,
        );

        $this->updateTeamStrengths();
    }

    public function getTeamStatistics(Team $team): TeamStatistics
    {
        return $this->teamRepository->get($team);
    }

    public function getAllTeamStatistics(): array
    {
        return $this->teamRepository->getAll();
    }

    public function getLeagueTable(): array
    {
        $statistics = $this->getAllTeamStatistics();

        usort($statistics, function (TeamStatistics $a, TeamStatistics $b): int {
            if ($a->points->getValue() === $b->points->getValue()) {
                return $b->getGoalDifference() <=> $a->getGoalDifference();
            }
            return $b->points->getValue() <=> $a->points->getValue();
        });

        $entries = [];
        foreach ($statistics as $position => $stats) {
            $entries[] = new LeagueTableEntry(
                position: $position + 1,
                team: $stats->team,
                points: $stats->points->getValue(),
                played: $stats->gamesPlayed,
                goalsFor: $stats->goalsScored->getValue(),
                goalsAgainst: $stats->goalsConceded->getValue(),
                goalDifference: $stats->getGoalDifference(),
                attackStrength: $stats->attackStrength,
                defenseStrength: $stats->defenseStrength,
            );
        }

        return $entries;
    }

    private function updateTeamStats(
        Team $team,
        Goals $goalsFor,
        Goals $goalsAgainst,
        Points $points,
        $matchStats,
        bool $isHome,
    ): void {
        $stats = $this->teamRepository->get($team);

        $newStats = new TeamStatistics(
            team: $stats->team,
            points: $stats->points->add($points),
            goalsScored: $stats->goalsScored->add($goalsFor),
            goalsConceded: $stats->goalsConceded->add($goalsAgainst),
            gamesPlayed: $stats->gamesPlayed + 1,
            shotsPerGame: $this->updateAverage(
                $stats->shotsPerGame,
                $isHome ? $matchStats->homeShots : $matchStats->awayShots,
                $stats->gamesPlayed,
            ),
            shotsOnTargetPerGame: $this->updateAverage(
                $stats->shotsOnTargetPerGame,
                $isHome ? $matchStats->homeShotsOnTarget : $matchStats->awayShotsOnTarget,
                $stats->gamesPlayed,
            ),
            averagePossession: $this->updateAverage(
                $stats->averagePossession,
                $isHome ? $matchStats->homePossession : $matchStats->awayPossession,
                $stats->gamesPlayed,
            ),
            attackStrength: $stats->attackStrength,
            defenseStrength: $stats->defenseStrength,
        );

        $this->teamRepository->update($newStats);
    }

    private function updateAverage(float $currentAverage, float $newValue, int $previousCount): float
    {
        return ($currentAverage * $previousCount + $newValue) / ($previousCount + 1);
    }

    private function updateTeamStrengths(): void
    {
        foreach ($this->teamRepository->getAll() as $stats) {
            $recentMatches = $this->getRecentMatches($stats->team, 3);

            if (empty($recentMatches)) {
                continue;
            }

            $attackPerformance = 0.0;
            $defensePerformance = 0.0;

            foreach ($recentMatches as $match) {
                $isHome = $match->homeTeam->equals($stats->team);
                $goalsFor = $isHome ? $match->homeGoals->getValue() : $match->awayGoals->getValue();
                $goalsAgainst = $isHome ? $match->awayGoals->getValue() : $match->homeGoals->getValue();

                $attackPerformance += $goalsFor;
                $defensePerformance += max(0, 3 - $goalsAgainst);
            }

            $avgAttack = $attackPerformance / count($recentMatches);
            $avgDefense = $defensePerformance / count($recentMatches);

            $newAttackStrength = $this->smoothStrength(
                $stats->attackStrength,
                $avgAttack,
            );
            
            $newDefenseStrength = $this->smoothStrength(
                $stats->defenseStrength,
                $avgDefense,
            );

            $this->teamRepository->update(
                $stats->withUpdatedStrengths($newAttackStrength, $newDefenseStrength)
            );
        }
    }

    private function getRecentMatches(Team $team, int $count): array
    {
        $teamMatches = array_filter(
            $this->matches,
            fn(MatchResult $match) => $match->homeTeam->equals($team) || $match->awayTeam->equals($team)
        );

        return array_slice($teamMatches, -$count);
    }

    private function smoothStrength(float $currentStrength, float $newValue): float
    {
        $smoothed = (1 - $this->learningRate) * $currentStrength + $this->learningRate * $newValue;
        
        return max(0.1, min(3.0, $smoothed));
    }
}