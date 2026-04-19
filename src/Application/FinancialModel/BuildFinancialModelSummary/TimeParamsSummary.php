<?php

declare(strict_types=1);

namespace App\Application\FinancialModel\BuildFinancialModelSummary;

final readonly class TimeParamsSummary
{
    public function __construct(
        public string $investmentStartMonth,
        public int $investmentDurationMonths,
        public int $commercialOperationDurationMonths,
        public int $totalDurationMonths,
        public string $forecastStep,
        public string $forecastStepLabel,
    ) {
    }
}
