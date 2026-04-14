<?php

declare(strict_types=1);

namespace App\Domain\Shared\ValueObject;

final readonly class MonthDuration
{
    private function __construct(
        private int $months
    ) {
    }

    public static function fromInt(int $value): self
    {
        if ($value <= 0) {
            throw new \InvalidArgumentException('Длительность должна быть целым положительным числом');
        }

        return new self($value);
    }

    public function toInt(): int
    {
        return $this->months;
    }
}
