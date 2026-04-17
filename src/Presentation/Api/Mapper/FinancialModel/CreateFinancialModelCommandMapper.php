<?php

declare(strict_types=1);

namespace App\Presentation\Api\Mapper\FinancialModel;

use App\Application\FinancialModel\CreateFinancialModel\CreateFinancialModelCommand;
use App\Domain\Shared\ValueObject\MonthDuration;
use App\Domain\Shared\ValueObject\ShortId;
use App\Domain\Shared\ValueObject\YearMonth;
use App\Domain\TimeParams\Enum\ForecastStep;
use App\Entity\User;
use App\Presentation\Api\Request\FinancialModel\CreateFinancialModelApiRequest;

final readonly class CreateFinancialModelCommandMapper
{
    public function map(
        User $owner,
        string $projectShortId,
        CreateFinancialModelApiRequest $apiRequest,
    ): CreateFinancialModelCommand {
        return new CreateFinancialModelCommand(
            owner: $owner,
            projectShortId: ShortId::fromString($projectShortId),
            investmentStartMonth: YearMonth::fromString($apiRequest->investmentStartMonth),
            investmentDuration: MonthDuration::fromInt((int) $apiRequest->investmentDurationMonths),
            commercialOperationDuration: MonthDuration::fromInt((int) $apiRequest->commercialOperationDurationMonths),
            forecastStep: ForecastStep::from($apiRequest->forecastStep),
        );
    }
}
