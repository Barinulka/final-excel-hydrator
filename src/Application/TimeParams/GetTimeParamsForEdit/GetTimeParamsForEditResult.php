<?php

declare(strict_types=1);

namespace App\Application\TimeParams\GetTimeParamsForEdit;

final readonly class GetTimeParamsForEditResult
{
    public function __construct(
        public string $projectShortId,
        public string $projectTitle,
        public string $financialModelShortId,
        public string $financialModelTitle,
        public string $investmentStartMonth,
        public int $investmentDurationMonths,
        public int $commercialOperationDurationMonths,
        public string $forecastStep,
    ) {
    }
}
