<?php

declare(strict_types=1);

namespace App\Presentation\Api\Mapper\TimeParams;

use App\Application\TimeParams\UpdateTimeParams\UpdateTimeParamsCommand;
use App\Domain\Shared\ValueObject\MonthDuration;
use App\Domain\Shared\ValueObject\ShortId;
use App\Domain\Shared\ValueObject\YearMonth;
use App\Domain\TimeParams\Enum\ForecastStep;
use App\Entity\User;
use App\Presentation\Api\Request\TimeParams\UpdateTimeParamsApiRequest;

final readonly class UpdateTimeParamsCommandMapper
{
    public function map(
        User $owner,
        string $projectShortId,
        string $financialModelShortId,
        UpdateTimeParamsApiRequest $apiRequest,
    ): UpdateTimeParamsCommand {
        return new UpdateTimeParamsCommand(
            financialModelShortId: ShortId::fromString($financialModelShortId),
            projectShortId: ShortId::fromString($projectShortId),
            owner: $owner,
            investmentStartMonth: YearMonth::fromString($apiRequest->investmentStartMonth),
            investmentDuration: MonthDuration::fromInt((int) $apiRequest->investmentDurationMonths),
            commercialOperationDuration: MonthDuration::fromInt((int) $apiRequest->commercialOperationDurationMonths),
            forecastStep: ForecastStep::from($apiRequest->forecastStep),
        );
    }
}
