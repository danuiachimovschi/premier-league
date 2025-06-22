<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

final readonly class Team
{
    public function __construct(
        private string $name,
    ) {
        if (empty($name)) {
            throw new \InvalidArgumentException('Team name cannot be empty');
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function equals(self $other): bool
    {
        return $this->name === $other->name;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}