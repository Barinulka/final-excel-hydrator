<?php

declare(strict_types=1);

namespace App\Application\FinancialModel\BuildFinancialModelSummary;

final readonly class TimelinePeriodSummary
{
    public function __construct(
        public int $periodNumber,
        public string $yearMonth,
        public string $periodStartDate,
        public string $periodEndDate,
        public bool $investmentActivity,
        public bool $operatingActivity,
        public bool $operatingStart,
    ) {
    }
}
