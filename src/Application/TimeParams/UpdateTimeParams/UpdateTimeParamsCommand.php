<?php

declare(strict_types=1);

namespace App\Application\TimeParams\UpdateTimeParams;

use App\Domain\Shared\ValueObject\MonthDuration;
use App\Domain\Shared\ValueObject\ShortId;
use App\Domain\Shared\ValueObject\YearMonth;
use App\Domain\TimeParams\Enum\ForecastStep;
use App\Entity\User;

final readonly class UpdateTimeParamsCommand
{
    public function __construct(
        public ShortId $financialModelShortId,
        public User $owner,
        public YearMonth $investmentStartMonth,
        public MonthDuration $investmentDuration,
        public MonthDuration $commercialOperationDuration,
        public ForecastStep $forecastStep,
    ) {
    }
}
