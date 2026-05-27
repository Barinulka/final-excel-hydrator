<?php

declare(strict_types=1);

namespace App\Tests\Application\Investments\DeleteInvestmentExpenseCategory;

use App\Application\Investments\DeleteInvestmentExpenseCategory\ArchivedFinancialModelCannotDeleteInvestmentExpenseCategoryException;
use App\Application\Investments\DeleteInvestmentExpenseCategory\DeleteInvestmentExpenseCategoryCommand;
use App\Application\Investments\DeleteInvestmentExpenseCategory\DeleteInvestmentExpenseCategoryHandler;
use App\Application\Investments\DeleteInvestmentExpenseCategory\FinancialModelForInvestmentExpenseCategoryDeleteNotFoundException;
use App\Application\Investments\DeleteInvestmentExpenseCategory\InvestmentExpenseCategoryForDeleteNotFoundException;
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
use App\Tests\Support\Application\Shared\Transaction\ImmediateTransactionalRunner;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

final class DeleteInvestmentExpenseCategoryHandlerTest extends TestCase
{
    public function testDeletesExpenseCategoryFromFinancialModel(): void
    {
        $owner = new User();
        $financialModel = $this->createFinancialModel($owner);
        $investmentBlock = new InvestmentBlock($financialModel);
        $category = InvestmentExpenseCategory::fromPredefinedType(
            investmentBlock: $investmentBlock,
            type: InvestmentExpenseCategoryType::Equipment,
            relatedExpenses: '1000000',
        );
        $this->setCategoryId($category, 12);
        $financialModelRepository = new InMemoryFinancialModelRepository();
        $financialModelRepository->save($financialModel);
        $investmentBlockRepository = new InMemoryInvestmentBlockRepository();
        $investmentBlockRepository->save($investmentBlock);
        $transactionalRunner = new ImmediateTransactionalRunner();

        $handler = new DeleteInvestmentExpenseCategoryHandler(
            financialModelRepository: $financialModelRepository,
            investmentBlockRepository: $investmentBlockRepository,
            transactionalRunner: $transactionalRunner,
        );

        $result = $handler->handle(new DeleteInvestmentExpenseCategoryCommand(
            owner: $owner,
            financialModelShortId: ShortId::fromString('ab23456789'),
            categoryId: 12,
        ));

        self::assertSame(12, $result->categoryId);
        self::assertTrue($result->isDeleted);
        self::assertSame(1, $transactionalRunner->runCount);
        self::assertCount(0, $investmentBlock->getExpenseCategories());
    }

    public function testThrowsWhenFinancialModelDoesNotExist(): void
    {
        $transactionalRunner = new ImmediateTransactionalRunner();
        $handler = new DeleteInvestmentExpenseCategoryHandler(
            financialModelRepository: new InMemoryFinancialModelRepository(),
            investmentBlockRepository: new InMemoryInvestmentBlockRepository(),
            transactionalRunner: $transactionalRunner,
        );

        try {
            $handler->handle(new DeleteInvestmentExpenseCategoryCommand(
                owner: new User(),
                financialModelShortId: ShortId::fromString('ab23456789'),
                categoryId: 12,
            ));

            self::fail('Expected financial model not found exception.');
        } catch (FinancialModelForInvestmentExpenseCategoryDeleteNotFoundException) {
            self::assertSame(1, $transactionalRunner->runCount);
        }
    }

    public function testThrowsWhenInvestmentBlockDoesNotExist(): void
    {
        $owner = new User();
        $financialModel = $this->createFinancialModel($owner);
        $financialModelRepository = new InMemoryFinancialModelRepository();
        $financialModelRepository->save($financialModel);
        $transactionalRunner = new ImmediateTransactionalRunner();
        $handler = new DeleteInvestmentExpenseCategoryHandler(
            financialModelRepository: $financialModelRepository,
            investmentBlockRepository: new InMemoryInvestmentBlockRepository(),
            transactionalRunner: $transactionalRunner,
        );

        try {
            $handler->handle(new DeleteInvestmentExpenseCategoryCommand(
                owner: $owner,
                financialModelShortId: ShortId::fromString('ab23456789'),
                categoryId: 12,
            ));

            self::fail('Expected category not found exception.');
        } catch (InvestmentExpenseCategoryForDeleteNotFoundException) {
            self::assertSame(1, $transactionalRunner->runCount);
        }
    }

    public function testThrowsWhenExpenseCategoryDoesNotExist(): void
    {
        $owner = new User();
        $financialModel = $this->createFinancialModel($owner);
        $financialModelRepository = new InMemoryFinancialModelRepository();
        $financialModelRepository->save($financialModel);
        $investmentBlockRepository = new InMemoryInvestmentBlockRepository();
        $investmentBlockRepository->save(new InvestmentBlock($financialModel));
        $transactionalRunner = new ImmediateTransactionalRunner();
        $handler = new DeleteInvestmentExpenseCategoryHandler(
            financialModelRepository: $financialModelRepository,
            investmentBlockRepository: $investmentBlockRepository,
            transactionalRunner: $transactionalRunner,
        );

        try {
            $handler->handle(new DeleteInvestmentExpenseCategoryCommand(
                owner: $owner,
                financialModelShortId: ShortId::fromString('ab23456789'),
                categoryId: 12,
            ));

            self::fail('Expected category not found exception.');
        } catch (InvestmentExpenseCategoryForDeleteNotFoundException) {
            self::assertSame(1, $transactionalRunner->runCount);
        }
    }

    public function testThrowsWhenFinancialModelIsArchived(): void
    {
        $owner = new User();
        $financialModel = $this->createFinancialModel($owner);
        $financialModel->archive();
        $financialModelRepository = new InMemoryFinancialModelRepository();
        $financialModelRepository->save($financialModel);
        $transactionalRunner = new ImmediateTransactionalRunner();
        $handler = new DeleteInvestmentExpenseCategoryHandler(
            financialModelRepository: $financialModelRepository,
            investmentBlockRepository: new InMemoryInvestmentBlockRepository(),
            transactionalRunner: $transactionalRunner,
        );

        try {
            $handler->handle(new DeleteInvestmentExpenseCategoryCommand(
                owner: $owner,
                financialModelShortId: ShortId::fromString('ab23456789'),
                categoryId: 12,
            ));

            self::fail('Expected archived financial model exception.');
        } catch (ArchivedFinancialModelCannotDeleteInvestmentExpenseCategoryException) {
            self::assertSame(1, $transactionalRunner->runCount);
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

    private function setCategoryId(InvestmentExpenseCategory $category, int $id): void
    {
        $idProperty = new ReflectionProperty(InvestmentExpenseCategory::class, 'id');
        $idProperty->setValue($category, $id);
    }
}
