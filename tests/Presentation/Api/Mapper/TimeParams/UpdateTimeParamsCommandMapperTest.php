<?php

declare(strict_types=1);

namespace App\Tests\Presentation\Api\Mapper\TimeParams;

use App\Domain\TimeParams\Enum\ForecastStep;
use App\Entity\User;
use App\Presentation\Api\Mapper\TimeParams\UpdateTimeParamsCommandMapper;
use App\Presentation\Api\Request\TimeParams\UpdateTimeParamsApiRequest;
use PHPUnit\Framework\TestCase;

final class UpdateTimeParamsCommandMapperTest extends TestCase
{
    public function testMapsApiRequestToUpdateTimeParamsCommand(): void
    {
        $owner = new User();
        $apiRequest = new UpdateTimeParamsApiRequest(
            investmentStartMonth: '2026-04',
            investmentDurationMonths: '6',
            commercialOperationDurationMonths: '24',
            forecastStep: 'quarter',
        );

        $mapper = new UpdateTimeParamsCommandMapper();

        $command = $mapper->map(
            owner: $owner,
            projectShortId: '23456789ab',
            financialModelShortId: 'ab23456789',
            apiRequest: $apiRequest,
        );

        self::assertSame($owner, $command->owner);
        self::assertSame('23456789ab', $command->projectShortId->toString());
        self::assertSame('ab23456789', $command->financialModelShortId->toString());
        self::assertSame('2026-04', $command->investmentStartMonth->toString());
        self::assertSame(6, $command->investmentDuration->toInt());
        self::assertSame(24, $command->commercialOperationDuration->toInt());
        self::assertSame(ForecastStep::Quarter, $command->forecastStep);
    }
}
