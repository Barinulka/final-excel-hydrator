<?php

declare(strict_types=1);

namespace App\Application\FinancialModel\CreateFinancialModel;

use App\Domain\Shared\ValueObject\MonthDuration;
use App\Domain\Shared\ValueObject\ShortId;
use App\Domain\Shared\ValueObject\YearMonth;
use App\Domain\TimeParams\Enum\ForecastStep;
use App\Entity\User;

final readonly class CreateFinancialModelCommand
{
    public function __construct(
        public User $owner,
        public ShortId $projectShortId,
        public string $title,
        public ?string $description,
        public YearMonth $investmentStartMonth,
        public MonthDuration $investmentDuration,
        public MonthDuration $commercialOperationDuration,
        public ForecastStep $forecastStep,
    ) {
    }
}
