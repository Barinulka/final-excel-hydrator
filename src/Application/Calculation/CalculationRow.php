<?php

declare(strict_types=1);

namespace App\Application\Calculation;

final readonly class CalculationRow
{
    /**
     * @param list<int|float|string|null> $values
     */
    public function __construct(
        public string $code,
        public string $title,
        public array $values,
    ) {
    }
}
