<?php

declare(strict_types=1);

namespace App\Application\TimeParams\UpdateTimeParams;

final readonly class UpdateTimeParamsResult
{
    public function __construct(
        public string $investmentStartMonth,
        public int $investmentDurationMonths,
        public int $commercialOperationDurationMonths,
        public string $forecastStep,
    ) {
    }
}
