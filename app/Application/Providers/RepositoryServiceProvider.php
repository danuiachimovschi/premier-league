<?php

declare(strict_types=1);

namespace App\Application\Providers;

use App\Domain\Contracts\Repositories\GameRepositoryInterface;
use App\Domain\Contracts\Repositories\SeasonRepositoryInterface;
use App\Domain\Contracts\Repositories\TeamRepositoryInterface;
use App\Domain\Contracts\Repositories\TeamSeasonRepositoryInterface;
use App\Domain\Repositories\GameRepository;
use App\Domain\Repositories\SeasonRepository;
use App\Domain\Repositories\TeamRepository;
use App\Domain\Repositories\TeamSeasonRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(GameRepositoryInterface::class, GameRepository::class);
        $this->app->singleton(SeasonRepositoryInterface::class, SeasonRepository::class);
        $this->app->singleton(TeamRepositoryInterface::class, TeamRepository::class);
        $this->app->singleton(TeamSeasonRepositoryInterface::class, TeamSeasonRepository::class);
    }
}