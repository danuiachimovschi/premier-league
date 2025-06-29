<?php

declare(strict_types=1);

namespace App\Infrastructure\Routing;

use App\Domain\Exceptions\InvalidUuidException;
use App\Domain\Models\Game;
use App\Domain\Models\Season;
use App\Domain\Models\Team;
use Illuminate\Support\Facades\Route;

class RouteBindingService
{
    private const UUID_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

    public static function register(): void
    {
        self::bindSeasonRoute();
        self::bindTeamRoute();
        self::bindMatchRoute();
    }

    private static function bindSeasonRoute(): void
    {
        Route::bind('season', function ($value) {
            if (!preg_match(self::UUID_PATTERN, $value)) {
                throw InvalidUuidException::forParameter('season', $value);
            }
            return Season::findOrFail($value);
        });
    }

    private static function bindTeamRoute(): void
    {
        Route::bind('team', function ($value) {
            if (!preg_match(self::UUID_PATTERN, $value)) {
                throw InvalidUuidException::forParameter('team', $value);
            }
            return Team::findOrFail($value);
        });
    }

    private static function bindMatchRoute(): void
    {
        Route::bind('match', function ($value) {
            if (!preg_match(self::UUID_PATTERN, $value)) {
                throw InvalidUuidException::forParameter('match', $value);
            }
            return Game::findOrFail($value);
        });
    }
}