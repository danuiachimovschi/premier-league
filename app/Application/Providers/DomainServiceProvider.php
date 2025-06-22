<?php

declare(strict_types=1);

namespace App\Application\Providers;

use App\Domain\Contracts\Services\ChampionshipServiceInterface;
use App\Domain\Contracts\Services\LeagueTableServiceInterface;
use App\Domain\Contracts\Services\MatchGeneratorServiceInterface;
use App\Domain\Contracts\Services\MatchServiceInterface;
use App\Domain\Contracts\Services\MatchSimulatorServiceInterface;
use App\Domain\Contracts\Services\PredictionServiceInterface;
use App\Domain\Contracts\Services\SeasonServiceInterface;
use App\Domain\Contracts\Services\TeamServiceInterface;
use App\Domain\Services\ChampionshipService;
use App\Domain\Services\LeagueTableService;
use App\Domain\Services\MatchGeneratorService;
use App\Domain\Services\MatchService;
use App\Domain\Services\MatchSimulatorService;
use App\Domain\Services\PredictionService;
use App\Domain\Services\SeasonService;
use App\Domain\Services\TeamService;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class DomainServiceProvider extends BaseServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            ChampionshipServiceInterface::class,
            ChampionshipService::class
        );

        $this->app->singleton(
            MatchGeneratorServiceInterface::class,
            MatchGeneratorService::class
        );

        $this->app->singleton(
            MatchSimulatorServiceInterface::class,
            MatchSimulatorService::class
        );

        $this->app->singleton(
            LeagueTableServiceInterface::class,
            LeagueTableService::class
        );

        $this->app->singleton(
            MatchServiceInterface::class,
            MatchService::class
        );

        $this->app->singleton(
            PredictionServiceInterface::class,
            PredictionService::class
        );

        $this->app->singleton(
            SeasonServiceInterface::class,
            SeasonService::class
        );

        $this->app->singleton(
            TeamServiceInterface::class,
            TeamService::class
        );
    }
}