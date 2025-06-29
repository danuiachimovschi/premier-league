<?php

declare(strict_types=1);

namespace App\Application\Providers;

use App\Domain\Contracts\Repositories\GameReadRepositoryInterface;
use App\Domain\Contracts\Repositories\GameRepositoryInterface;
use App\Domain\Contracts\Repositories\GameWriteRepositoryInterface;
use App\Domain\Contracts\Repositories\SeasonReadRepositoryInterface;
use App\Domain\Contracts\Repositories\SeasonRepositoryInterface;
use App\Domain\Contracts\Repositories\SeasonWriteRepositoryInterface;
use App\Domain\Contracts\Repositories\TeamReadRepositoryInterface;
use App\Domain\Contracts\Repositories\TeamRepositoryInterface;
use App\Domain\Contracts\Repositories\TeamSeasonReadRepositoryInterface;
use App\Domain\Contracts\Repositories\TeamSeasonRepositoryInterface;
use App\Domain\Contracts\Repositories\TeamSeasonWriteRepositoryInterface;
use App\Domain\Contracts\Repositories\TeamWriteRepositoryInterface;
use App\Domain\Repositories\GameReadRepository;
use App\Domain\Repositories\GameRepository;
use App\Domain\Repositories\GameWriteRepository;
use App\Domain\Repositories\SeasonReadRepository;
use App\Domain\Repositories\SeasonRepository;
use App\Domain\Repositories\SeasonWriteRepository;
use App\Domain\Repositories\TeamReadRepository;
use App\Domain\Repositories\TeamRepository;
use App\Domain\Repositories\TeamSeasonReadRepository;
use App\Domain\Repositories\TeamSeasonRepository;
use App\Domain\Repositories\TeamSeasonWriteRepository;
use App\Domain\Repositories\TeamWriteRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Keep original combined interfaces for backward compatibility
        $this->app->singleton(GameRepositoryInterface::class, GameRepository::class);
        $this->app->singleton(SeasonRepositoryInterface::class, SeasonRepository::class);
        $this->app->singleton(TeamRepositoryInterface::class, TeamRepository::class);
        $this->app->singleton(TeamSeasonRepositoryInterface::class, TeamSeasonRepository::class);

        // Register separated read/write repositories
        $this->app->singleton(GameReadRepositoryInterface::class, GameReadRepository::class);
        $this->app->singleton(GameWriteRepositoryInterface::class, GameWriteRepository::class);
        $this->app->singleton(SeasonReadRepositoryInterface::class, SeasonReadRepository::class);
        $this->app->singleton(SeasonWriteRepositoryInterface::class, SeasonWriteRepository::class);
        $this->app->singleton(TeamReadRepositoryInterface::class, TeamReadRepository::class);
        $this->app->singleton(TeamWriteRepositoryInterface::class, TeamWriteRepository::class);
        $this->app->singleton(TeamSeasonReadRepositoryInterface::class, TeamSeasonReadRepository::class);
        $this->app->singleton(TeamSeasonWriteRepositoryInterface::class, TeamSeasonWriteRepository::class);
    }
}