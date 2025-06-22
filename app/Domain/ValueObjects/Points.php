<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

final readonly class Points
{
    public function __construct(
        private int $value,
    ) {
        if ($value < 0) {
            throw new \InvalidArgumentException('Points cannot be negative');
        }
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function add(self $other): self
    {
        return new self($this->value + $other->value);
    }

    public static function forWin(): self
    {
        return new self(3);
    }

    public static function forDraw(): self
    {
        return new self(1);
    }

    public static function forLoss(): self
    {
        return new self(0);
    }
}