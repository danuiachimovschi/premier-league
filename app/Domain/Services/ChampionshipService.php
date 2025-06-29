<?php

namespace App\Domain\Services;

use App\Domain\Contracts\Services\ChampionshipServiceInterface;
use App\Domain\DTOs\MatchResult;
use App\Domain\DTOs\MatchStatistics;
use App\Domain\Models\Season;
use App\Domain\Services\TeamStatisticsRepositoryAdapter;
use App\Domain\Repositories\TeamReadRepositoryInterface;
use App\Domain\ValueObjects\Goals;
use App\Domain\ValueObjects\Team as DomainTeam;
use Illuminate\Support\Facades\Cache;

class ChampionshipService implements ChampionshipServiceInterface
{
    private ChampionshipPredictorService $predictor;

    public function __construct(
        private readonly ?TeamReadRepositoryInterface $teamRepository = null
    ) {
        $this->predictor = $this->createPredictorService();
    }

    public function updateProbabilities(Season $season): void
    {
        // Get all matches for the season
        $matches = $season->matches()
            ->where('is_played', true)
            ->with(['homeTeam', 'awayTeam'])
            ->orderBy('played_at')
            ->get();

        if ($matches->isEmpty()) {
            return;
        }

        $this->predictor = $this->createPredictorService($season);

        foreach ($matches as $match) {
            $matchResult = new MatchResult(
                new DomainTeam($match->homeTeam->name),
                new DomainTeam($match->awayTeam->name),
                new Goals($match->home_goals),
                new Goals($match->away_goals),
                new MatchStatistics(
                    $match->game_statistics['home_shots'] ?? 10,
                    $match->game_statistics['away_shots'] ?? 10,
                    $match->game_statistics['home_shots_on_target'] ?? 5,
                    $match->game_statistics['away_shots_on_target'] ?? 5,
                    $match->game_statistics['home_possession'] ?? 50,
                    $match->game_statistics['away_possession'] ?? 50
                )
            );

            $this->predictor->addMatchResultAndUpdatePredictions($matchResult);
        }

        $probabilities = $this->predictor->calculateChampionshipProbabilities();

        foreach ($season->teamSeasons()->with('team')->get() as $teamSeason) {
            $teamName = $teamSeason->team->name;
            if (isset($probabilities[$teamName])) {
                $teamSeason->update([
                    'championship_probability' => $probabilities[$teamName] * 100
                ]);
            }
        }

        $this->cachePredictionHistory($season, $probabilities);
    }

    public function getPredictionHistory(Season $season): array
    {
        $cacheKey = "prediction_history_season_{$season->id}";
        return Cache::get($cacheKey, []);
    }

    public function getChampionshipPredictions(Season $season): array
    {
        $this->predictor = $this->createPredictorService($season);

        $matches = $season->matches()
            ->where('is_played', true)
            ->with(['homeTeam', 'awayTeam'])
            ->orderBy('played_at')
            ->get();

        foreach ($matches as $match) {
            $matchResult = new MatchResult(
                new DomainTeam($match->homeTeam->name),
                new DomainTeam($match->awayTeam->name),
                new Goals($match->home_goals),
                new Goals($match->away_goals),
                new MatchStatistics(
                    $match->game_statistics['home_shots'] ?? 10,
                    $match->game_statistics['away_shots'] ?? 10,
                    $match->game_statistics['home_shots_on_target'] ?? 5,
                    $match->game_statistics['away_shots_on_target'] ?? 5,
                    $match->game_statistics['home_possession'] ?? 50,
                    $match->game_statistics['away_possession'] ?? 50
                )
            );

            $this->predictor->addMatchResultAndUpdatePredictions($matchResult);
        }

        return $this->predictor->calculateChampionshipProbabilities();
    }

    public function clearPredictionsCache(Season $season): void
    {
        $cacheKey = "prediction_history_season_{$season->id}";
        Cache::forget($cacheKey);
    }

    private function createPredictorService(?Season $season = null): ChampionshipPredictorService
    {
        $teamNames = $season ?
            $season->teamSeasons()->with('team')->get()->pluck('team.name')->toArray() :
            ['Arsenal', 'Chelsea', 'Liverpool', 'Manchester City'];

        $teamRepositoryAdapter = new TeamStatisticsRepositoryAdapter($this->teamRepository);
        $teamRepositoryAdapter->initializeTeams($teamNames);
        $statisticsService = new TeamStatisticsService($teamRepositoryAdapter);

        return new ChampionshipPredictorService($statisticsService, 6);
    }

    private function cachePredictionHistory(Season $season, array $probabilities): void
    {
        $cacheKey = "prediction_history_season_{$season->id}";
        $history = Cache::get($cacheKey, []);

        $entry = [
            'week' => $season->current_week,
            'date' => now()->toISOString(),
            'probabilities' => array_map(fn($prob) => $prob * 100, $probabilities),
        ];

        $history[] = $entry;

        if (count($history) > 20) {
            $history = array_slice($history, -20);
        }

        Cache::put($cacheKey, $history, now()->addDays(30));
    }

    public function getDetailedAnalysis(Season $season): array
    {
        if ($season->current_week === 0) {
            return [];
        }

        $this->predictor = $this->createPredictorService($season);

        $matches = $season->matches()
            ->where('is_played', true)
            ->with(['homeTeam', 'awayTeam'])
            ->orderBy('played_at')
            ->get();

        foreach ($matches as $match) {
            $matchResult = new MatchResult(
                new DomainTeam($match->homeTeam->name),
                new DomainTeam($match->awayTeam->name),
                $match->home_goals,
                $match->away_goals,
                new MatchStatistics(
                    $match->game_statistics['home_shots'] ?? 10,
                    $match->game_statistics['away_shots'] ?? 10,
                    $match->game_statistics['home_shots_on_target'] ?? 5,
                    $match->game_statistics['away_shots_on_target'] ?? 5,
                    $match->game_statistics['home_possession'] ?? 50,
                    $match->game_statistics['away_possession'] ?? 50
                )
            );

            $this->predictor->addMatchResultAndUpdatePredictions($matchResult);
        }

        return $this->predictor->getDetailedChampionshipAnalysis();
    }
}