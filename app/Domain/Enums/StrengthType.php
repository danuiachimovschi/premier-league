<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum StrengthType: string
{
    case ATTACK = 'attack';
    case DEFENSE = 'defense';

    public function getDefaultValue(): float
    {
        return 1.0;
    }

    public function getMinValue(): float
    {
        return 0.1;
    }

    public function getMaxValue(): float
    {
        return 3.0;
    }
}