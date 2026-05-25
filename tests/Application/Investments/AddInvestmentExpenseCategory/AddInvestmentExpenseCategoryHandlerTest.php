<?php

declare(strict_types=1);

namespace App\Tests\Application\Investments\AddInvestmentExpenseCategory;

use App\Application\Investments\AddInvestmentExpenseCategory\AddInvestmentExpenseCategoryCommand;
use App\Application\Investments\AddInvestmentExpenseCategory\AddInvestmentExpenseCategoryHandler;
use App\Application\Investments\AddInvestmentExpenseCategory\ArchivedFinancialModelCannotBeChangedException;
use App\Application\Investments\AddInvestmentExpenseCategory\FinancialModelForInvestmentExpenseCategoryNotFoundException;
use App\Domain\Investments\Enum\InvestmentExpenseCategoryType;
use App\Domain\Shared\ValueObject\MonthDuration;
use App\Domain\Shared\ValueObject\ShortId;
use App\Domain\Shared\ValueObject\YearMonth;
use App\Domain\TimeParams\Enum\ForecastStep;
use App\Entity\FinancialModel;
use App\Entity\InvestmentBlock;
use App\Entity\TimeParams;
use App\Entity\User;
use App\Tests\Support\Application\FinancialModel\InMemoryFinancialModelRepository;
use App\Tests\Support\Application\Investments\InMemoryInvestmentBlockRepository;
use App\Tests\Support\Application\Shared\Transaction\ImmediateTransactionalRunner;
use PHPUnit\Framework\TestCase;

final class AddInvestmentExpenseCategoryHandlerTest extends TestCase
{
    public function testAddsPredefinedCategoryToExistingInvestmentBlock(): void
    {
        $owner = new User();
        $financialModel = $this->createFinancialModel($owner);
        $investmentBlock = new InvestmentBlock($financialModel);
        $financialModelRepository = new InMemoryFinancialModelRepository();
        $financialModelRepository->save($financialModel);
        $investmentBlockRepository = new InMemoryInvestmentBlockRepository();
        $investmentBlockRepository->save($investmentBlock);
        $transactionalRunner = new ImmediateTransactionalRunner();

        $handler = new AddInvestmentExpenseCategoryHandler(
            financialModelRepository: $financialModelRepository,
            investmentBlockRepository: $investmentBlockRepository,
            transactionalRunner: $transactionalRunner,
        );

        $result = $handler->handle(new AddInvestmentExpenseCategoryCommand(
            owner: $owner,
            financialModelShortId: ShortId::fromString('ab23456789'),
            type: InvestmentExpenseCategoryType::Equipment,
            customTitle: null,
            relatedExpenses: ' Доставка и монтаж ',
        ));

        self::assertSame('ab23456789', $result->financialModelShortId);
        self::assertSame('Оборудование', $result->title);
        self::assertSame(InvestmentExpenseCategoryType::Equipment->value, $result->type);
        self::assertNull($result->customTitle);
        self::assertSame('Доставка и монтаж', $result->relatedExpenses);
        self::assertSame(1, $transactionalRunner->runCount);
        self::assertCount(1, $investmentBlockRepository->savedInvestmentBlocks);
        self::assertSame($investmentBlock, $investmentBlockRepository->savedInvestmentBlocks[0]);
        self::assertCount(1, $investmentBlock->getExpenseCategories());
    }

    public function testCreatesInvestmentBlockWhenItDoesNotExist(): void
    {
        $owner = new User();
        $financialModel = $this->createFinancialModel($owner);
        $financialModelRepository = new InMemoryFinancialModelRepository();
        $financialModelRepository->save($financialModel);
        $investmentBlockRepository = new InMemoryInvestmentBlockRepository();
        $transactionalRunner = new ImmediateTransactionalRunner();

        $handler = new AddInvestmentExpenseCategoryHandler(
            financialModelRepository: $financialModelRepository,
            investmentBlockRepository: $investmentBlockRepository,
            transactionalRunner: $transactionalRunner,
        );

        $result = $handler->handle(new AddInvestmentExpenseCategoryCommand(
            owner: $owner,
            financialModelShortId: ShortId::fromString('ab23456789'),
            type: null,
            customTitle: '  Кухонная линия  ',
            relatedExpenses: ' Проектирование ',
        ));

        self::assertSame('Кухонная линия', $result->title);
        self::assertNull($result->type);
        self::assertSame('Кухонная линия', $result->customTitle);
        self::assertSame('Проектирование', $result->relatedExpenses);
        self::assertSame(1, $transactionalRunner->runCount);
        self::assertCount(1, $investmentBlockRepository->savedInvestmentBlocks);

        $createdInvestmentBlock = $investmentBlockRepository->savedInvestmentBlocks[0];

        self::assertSame($financialModel, $createdInvestmentBlock->getFinancialModel());
        self::assertCount(1, $createdInvestmentBlock->getExpenseCategories());
    }

    public function testThrowsWhenFinancialModelDoesNotExist(): void
    {
        $financialModelRepository = new InMemoryFinancialModelRepository();
        $investmentBlockRepository = new InMemoryInvestmentBlockRepository();
        $transactionalRunner = new ImmediateTransactionalRunner();

        $handler = new AddInvestmentExpenseCategoryHandler(
            financialModelRepository: $financialModelRepository,
            investmentBlockRepository: $investmentBlockRepository,
            transactionalRunner: $transactionalRunner,
        );

        try {
            $handler->handle(new AddInvestmentExpenseCategoryCommand(
                owner: new User(),
                financialModelShortId: ShortId::fromString('ab23456789'),
                type: InvestmentExpenseCategoryType::Equipment,
                customTitle: null,
                relatedExpenses: null,
            ));

            self::fail('Expected financial model not found exception.');
        } catch (FinancialModelForInvestmentExpenseCategoryNotFoundException) {
            self::assertSame(1, $transactionalRunner->runCount);
            self::assertSame([], $investmentBlockRepository->savedInvestmentBlocks);
        }
    }

    public function testThrowsWhenFinancialModelIsArchived(): void
    {
        $owner = new User();
        $financialModel = $this->createFinancialModel($owner);
        $financialModel->archive();
        $financialModelRepository = new InMemoryFinancialModelRepository();
        $financialModelRepository->save($financialModel);
        $investmentBlockRepository = new InMemoryInvestmentBlockRepository();
        $transactionalRunner = new ImmediateTransactionalRunner();

        $handler = new AddInvestmentExpenseCategoryHandler(
            financialModelRepository: $financialModelRepository,
            investmentBlockRepository: $investmentBlockRepository,
            transactionalRunner: $transactionalRunner,
        );

        try {
            $handler->handle(new AddInvestmentExpenseCategoryCommand(
                owner: $owner,
                financialModelShortId: ShortId::fromString('ab23456789'),
                type: InvestmentExpenseCategoryType::Equipment,
                customTitle: null,
                relatedExpenses: null,
            ));

            self::fail('Expected archived financial model exception.');
        } catch (ArchivedFinancialModelCannotBeChangedException) {
            self::assertSame(1, $transactionalRunner->runCount);
            self::assertSame([], $investmentBlockRepository->savedInvestmentBlocks);
        }
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
}
