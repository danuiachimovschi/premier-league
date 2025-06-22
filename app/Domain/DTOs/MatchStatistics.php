<?php

declare(strict_types=1);

namespace App\Domain\DTOs;

use InvalidArgumentException;

final readonly class MatchStatistics
{
    public function __construct(
        public int $homeShots = 0,
        public int $awayShots = 0,
        public int $homeShotsOnTarget = 0,
        public int $awayShotsOnTarget = 0,
        public float $homePossession = 50.0,
        public float $awayPossession = 50.0,
    ) {
        if ($homePossession + $awayPossession !== 100.0) {
            throw new InvalidArgumentException('Home and away possession must sum to 100%');
        }
    }

    public static function empty(): self
    {
        return new self();
    }
}