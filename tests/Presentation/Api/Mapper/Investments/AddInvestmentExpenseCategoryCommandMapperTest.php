<?php

declare(strict_types=1);

namespace App\Tests\Presentation\Api\Mapper\Investments;

use App\Domain\Investments\Enum\InvestmentExpenseCategoryType;
use App\Domain\Shared\ValueObject\ShortId;
use App\Entity\User;
use App\Presentation\Api\Mapper\Investments\AddInvestmentExpenseCategoryCommandMapper;
use App\Presentation\Api\Request\Investments\AddInvestmentExpenseCategoryApiRequest;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class AddInvestmentExpenseCategoryCommandMapperTest extends TestCase
{
    public function testMapsPredefinedCategoryApiRequestToCommand(): void
    {
        $owner = new User();
        $financialModelShortId = ShortId::fromString('ab23456789');
        $apiRequest = new AddInvestmentExpenseCategoryApiRequest();
        $apiRequest->type = 'equipment';
        $apiRequest->customTitle = null;
        $apiRequest->relatedExpenses = ' Доставка ';

        $mapper = new AddInvestmentExpenseCategoryCommandMapper();

        $command = $mapper->map(
            owner: $owner,
            financialModelShortId: $financialModelShortId,
            request: $apiRequest,
        );

        self::assertSame($owner, $command->owner);
        self::assertSame($financialModelShortId, $command->financialModelShortId);
        self::assertSame(InvestmentExpenseCategoryType::Equipment, $command->type);
        self::assertNull($command->customTitle);
        self::assertSame(' Доставка ', $command->relatedExpenses);
    }

    public function testMapsCustomCategoryApiRequestToCommand(): void
    {
        $owner = new User();
        $financialModelShortId = ShortId::fromString('ab23456789');
        $apiRequest = new AddInvestmentExpenseCategoryApiRequest();
        $apiRequest->type = null;
        $apiRequest->customTitle = 'Кухонная линия';
        $apiRequest->relatedExpenses = 'Проектирование';

        $mapper = new AddInvestmentExpenseCategoryCommandMapper();

        $command = $mapper->map(
            owner: $owner,
            financialModelShortId: $financialModelShortId,
            request: $apiRequest,
        );

        self::assertSame($owner, $command->owner);
        self::assertSame($financialModelShortId, $command->financialModelShortId);
        self::assertNull($command->type);
        self::assertSame('Кухонная линия', $command->customTitle);
        self::assertSame('Проектирование', $command->relatedExpenses);
    }

    public function testThrowsWhenCategoryTypeIsInvalid(): void
    {
        $apiRequest = new AddInvestmentExpenseCategoryApiRequest();
        $apiRequest->type = 'unknown';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Недопустимый тип категории инвестиций.');

        (new AddInvestmentExpenseCategoryCommandMapper())->map(
            owner: new User(),
            financialModelShortId: ShortId::fromString('ab23456789'),
            request: $apiRequest,
        );
    }
}
