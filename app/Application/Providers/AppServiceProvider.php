<?php

declare(strict_types=1);

namespace App\Application\Providers;

use App\Domain\Services\ChampionshipService;
use App\Domain\Services\MatchGeneratorService;
use App\Domain\Services\MatchSimulatorService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ChampionshipService::class);
        $this->app->singleton(MatchGeneratorService::class);
        $this->app->singleton(MatchSimulatorService::class);
    }
}