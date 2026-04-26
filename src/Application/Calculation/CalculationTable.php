<?php

declare(strict_types=1);

namespace App\Application\Calculation;

final readonly class CalculationTable
{
    /**
     * @param list<string> $periods
     * @param list<CalculationRow> $rows
     */
    public function __construct(
        public string $code,
        public string $title,
        public array $periods,
        public array $rows,
    ) {
    }
}
