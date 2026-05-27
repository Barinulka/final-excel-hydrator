<?php

declare(strict_types=1);

namespace App\Tests\Application\Investments\ListInvestmentExpenseCategories;

use App\Application\Investments\ListInvestmentExpenseCategories\FinancialModelForInvestmentExpenseCategoriesNotFoundException;
use App\Application\Investments\ListInvestmentExpenseCategories\ListInvestmentExpenseCategoriesHandler;
use App\Application\Investments\ListInvestmentExpenseCategories\ListInvestmentExpenseCategoriesQuery;
use App\Domain\Investments\Enum\InvestmentExpenseCategoryType;
use App\Domain\Shared\ValueObject\MonthDuration;
use App\Domain\Shared\ValueObject\ShortId;
use App\Domain\Shared\ValueObject\YearMonth;
use App\Domain\TimeParams\Enum\ForecastStep;
use App\Entity\FinancialModel;
use App\Entity\InvestmentBlock;
use App\Entity\InvestmentExpenseCategory;
use App\Entity\TimeParams;
use App\Entity\User;
use App\Tests\Support\Application\FinancialModel\InMemoryFinancialModelRepository;
use App\Tests\Support\Application\Investments\InMemoryInvestmentBlockRepository;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

final class ListInvestmentExpenseCategoriesHandlerTest extends TestCase
{
    public function testReturnsSavedExpenseCategoriesForFinancialModel(): void
    {
        $owner = new User();
        $financialModel = $this->createFinancialModel($owner);
        $investmentBlock = new InvestmentBlock($financialModel);
        $equipmentCategory = InvestmentExpenseCategory::fromPredefinedType(
            investmentBlock: $investmentBlock,
            type: InvestmentExpenseCategoryType::Equipment,
            relatedExpenses: ' 1000000 ',
        );
        $customCategory = InvestmentExpenseCategory::fromCustomTitle(
            investmentBlock: $investmentBlock,
            customTitle: ' Кофейная станция ',
            relatedExpenses: '250000',
        );
        $this->setCategoryId($equipmentCategory, 10);
        $this->setCategoryId($customCategory, 11);

        $financialModelRepository = new InMemoryFinancialModelRepository();
        $financialModelRepository->save($financialModel);
        $investmentBlockRepository = new InMemoryInvestmentBlockRepository();
        $investmentBlockRepository->save($investmentBlock);

        $handler = new ListInvestmentExpenseCategoriesHandler(
            financialModelRepository: $financialModelRepository,
            investmentBlockRepository: $investmentBlockRepository,
        );

        $result = $handler->handle(new ListInvestmentExpenseCategoriesQuery(
            owner: $owner,
            financialModelShortId: ShortId::fromString('ab23456789'),
        ));

        self::assertCount(2, $result->items);
        self::assertSame(10, $result->items[0]->id);
        self::assertSame(InvestmentExpenseCategoryType::Equipment->value, $result->items[0]->type);
        self::assertSame('Оборудование', $result->items[0]->title);
        self::assertSame('1000000', $result->items[0]->relatedExpenses);
        self::assertSame(11, $result->items[1]->id);
        self::assertNull($result->items[1]->type);
        self::assertSame('Кофейная станция', $result->items[1]->title);
        self::assertSame('250000', $result->items[1]->relatedExpenses);
    }

    public function testReturnsEmptyListWhenInvestmentBlockDoesNotExist(): void
    {
        $owner = new User();
        $financialModel = $this->createFinancialModel($owner);
        $financialModelRepository = new InMemoryFinancialModelRepository();
        $financialModelRepository->save($financialModel);

        $handler = new ListInvestmentExpenseCategoriesHandler(
            financialModelRepository: $financialModelRepository,
            investmentBlockRepository: new InMemoryInvestmentBlockRepository(),
        );

        $result = $handler->handle(new ListInvestmentExpenseCategoriesQuery(
            owner: $owner,
            financialModelShortId: ShortId::fromString('ab23456789'),
        ));

        self::assertSame([], $result->items);
    }

    public function testThrowsWhenFinancialModelDoesNotBelongToOwner(): void
    {
        $financialModelRepository = new InMemoryFinancialModelRepository();
        $financialModelRepository->save($this->createFinancialModel(new User()));

        $handler = new ListInvestmentExpenseCategoriesHandler(
            financialModelRepository: $financialModelRepository,
            investmentBlockRepository: new InMemoryInvestmentBlockRepository(),
        );

        $this->expectException(FinancialModelForInvestmentExpenseCategoriesNotFoundException::class);

        $handler->handle(new ListInvestmentExpenseCategoriesQuery(
            owner: new User(),
            financialModelShortId: ShortId::fromString('ab23456789'),
        ));
    }

    public function testThrowsWhenFinancialModelDoesNotExist(): void
    {
        $handler = new ListInvestmentExpenseCategoriesHandler(
            financialModelRepository: new InMemoryFinancialModelRepository(),
            investmentBlockRepository: new InMemoryInvestmentBlockRepository(),
        );

        $this->expectException(FinancialModelForInvestmentExpenseCategoriesNotFoundException::class);

        $handler->handle(new ListInvestmentExpenseCategoriesQuery(
            owner: new User(),
            financialModelShortId: ShortId::fromString('ab23456789'),
        ));
    }

    private function createFinancialModel(User $owner): FinancialModel
    {
        return FinancialModel::create(
            owner: $owner,
            shortId: ShortId::fromString('ab23456789'),
            title: 'Test Model',
            description: null,
            versionNumber: 1,
            timeParams: TimeParams::create(
                investmentStartMonth: YearMonth::fromString('2026-01'),
                investmentDuration: MonthDuration::fromInt(3),
                commercialOperationDuration: MonthDuration::fromInt(12),
                forecastStep: ForecastStep::Month,
            ),
        );
    }

    private function setCategoryId(InvestmentExpenseCategory $category, int $id): void
    {
        $idProperty = new ReflectionProperty(InvestmentExpenseCategory::class, 'id');
        $idProperty->setValue($category, $id);
    }
}
