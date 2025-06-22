<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

final readonly class Goals
{
    public function __construct(
        private int $value,
    ) {
        if ($value < 0) {
            throw new \InvalidArgumentException('Goals cannot be negative');
        }
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function isGreaterThan(self $other): bool
    {
        return $this->value > $other->value;
    }

    public function isLessThan(self $other): bool
    {
        return $this->value < $other->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function add(self $other): self
    {
        return new self($this->value + $other->value);
    }

    public function subtract(self $other): self
    {
        return new self(max(0, $this->value - $other->value));
    }
}