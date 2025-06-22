<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum MatchOutcome: string
{
    case HOME_WIN = 'home_win';
    case DRAW = 'draw';
    case AWAY_WIN = 'away_win';

    public function getPoints(bool $isHomeTeam): int
    {
        return match($this) {
            self::HOME_WIN => $isHomeTeam ? 3 : 0,
            self::AWAY_WIN => $isHomeTeam ? 0 : 3,
            self::DRAW => 1,
        };
    }
}