<?php

declare(strict_types=1);

namespace App\Application\Calculation;

final readonly class CalculationResult
{
    /**
     * @param list<CalculationTable> $tables
     * @param array<string, int|float|string|null> $metrics
     * @param list<string> $warnings
     */
    public function __construct(
        public array $tables,
        public array $metrics,
        public array $warnings,
    ) {
    }
}
