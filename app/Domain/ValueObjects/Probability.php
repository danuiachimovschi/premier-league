<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

final readonly class Probability
{
    public function __construct(
        private float $value,
    ) {
        if ($value < 0 || $value > 1) {
            throw new \InvalidArgumentException('Probability must be between 0 and 1');
        }
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function getPercentage(): float
    {
        return $this->value * 100;
    }

    public function complement(): self
    {
        return new self(1 - $this->value);
    }

    public static function fromPercentage(float $percentage): self
    {
        return new self($percentage / 100);
    }
}