<?php

declare(strict_types=1);

namespace App\Tests\Presentation\Api\Mapper\FinancialModel;

use App\Domain\TimeParams\Enum\ForecastStep;
use App\Entity\User;
use App\Presentation\Api\Mapper\FinancialModel\CreateFinancialModelCommandMapper;
use App\Presentation\Api\Request\FinancialModel\CreateFinancialModelApiRequest;
use PHPUnit\Framework\TestCase;

final class CreateFinancialModelCommandMapperTest extends TestCase
{
    public function testMapsApiRequestToCreateFinancialModelCommand(): void
    {
        $owner = new User();
        $apiRequest = new CreateFinancialModelApiRequest(
            investmentStartMonth: '2026-04',
            investmentDurationMonths: '6',
            commercialOperationDurationMonths: '24',
            forecastStep: 'quarter',
        );

        $mapper = new CreateFinancialModelCommandMapper();

        $command = $mapper->map(
            owner: $owner,
            projectShortId: '23456789ab',
            apiRequest: $apiRequest,
        );

        self::assertSame($owner, $command->owner);
        self::assertSame('23456789ab', $command->projectShortId->toString());
        self::assertSame('2026-04', $command->investmentStartMonth->toString());
        self::assertSame(6, $command->investmentDuration->toInt());
        self::assertSame(24, $command->commercialOperationDuration->toInt());
        self::assertSame(ForecastStep::Quarter, $command->forecastStep);
    }
}
