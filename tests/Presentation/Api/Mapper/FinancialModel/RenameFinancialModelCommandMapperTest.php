<?php

declare(strict_types=1);

namespace App\Tests\Presentation\Api\Mapper\FinancialModel;

use App\Entity\User;
use App\Presentation\Api\Mapper\FinancialModel\RenameFinancialModelCommandMapper;
use App\Presentation\Api\Request\FinancialModel\RenameFinancialModelApiRequest;
use PHPUnit\Framework\TestCase;

final class RenameFinancialModelCommandMapperTest extends TestCase
{
    public function testMapsApiRequestToRenameFinancialModelCommand(): void
    {
        $owner = new User();
        $apiRequest = new RenameFinancialModelApiRequest(
            title: 'Базовый сценарий',
        );

        $mapper = new RenameFinancialModelCommandMapper();

        $command = $mapper->map(
            owner: $owner,
            financialModelShortId: 'ab23456789',
            apiRequest: $apiRequest,
        );

        self::assertSame($owner, $command->owner);
        self::assertSame('ab23456789', $command->financialModelShortId->toString());
        self::assertSame('Базовый сценарий', $command->title);
    }
}
