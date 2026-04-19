<?php

declare(strict_types=1);

namespace App\Application\FinancialModel\BuildFinancialModelSummary;

final readonly class TimelineSummary
{
    /**
     * @param TimelinePeriodSummary[] $periods
     */
    public function __construct(
        public string $investmentStartDate,
        public string $investmentEndDate,
        public string $commercialOperationStartDate,
        public string $commercialOperationEndDate,
        public string $modelStartDate,
        public string $modelEndDate,
        public int $periodCount,
        public array $periods,
    ) {
    }
}
