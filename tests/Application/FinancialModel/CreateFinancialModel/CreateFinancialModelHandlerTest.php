<?php

declare(strict_types=1);

namespace App\Tests\Application\FinancialModel\CreateFinancialModel;

use App\Application\FinancialModel\CreateFinancialModel\CreateFinancialModelCommand;
use App\Application\FinancialModel\CreateFinancialModel\CreateFinancialModelHandler;
use App\Application\FinancialModel\CreateFinancialModel\CreateFinancialModelShortIdGenerationException;
use App\Domain\FinancialModel\Enum\AmountDisplayFormat;
use App\Domain\FinancialModel\Enum\FinancialModelStatus;
use App\Domain\Shared\ValueObject\MonthDuration;
use App\Domain\Shared\ValueObject\ShortId;
use App\Domain\Shared\ValueObject\YearMonth;
use App\Domain\TimeParams\Enum\ForecastStep;
use App\Entity\FinancialModel;
use App\Entity\TimeParams;
use App\Entity\User;
use App\Tests\Support\Application\FinancialModel\InMemoryFinancialModelRepository;
use App\Tests\Support\Application\Shared\ShortId\FixedShortIdGenerator;
use App\Tests\Support\Application\Shared\Transaction\ImmediateTransactionalRunner;
use PHPUnit\Framework\TestCase;

final class CreateFinancialModelHandlerTest extends TestCase
{
    public function testCreatesFinancialModelSuccessfully(): void
    {
        $owner = new User();
        $shortIdGenerator = new FixedShortIdGenerator('ab23456789');
        $transactionalRunner = new ImmediateTransactionalRunner();
        $financialModelRepository = new InMemoryFinancialModelRepository();

        $handler = new CreateFinancialModelHandler(
            transactionalRunner: $transactionalRunner,
            idGenerator: $shortIdGenerator,
            repository: $financialModelRepository,
        );

        $result = $handler->handle($this->createCommand($owner));

        self::assertSame('ab23456789', $result->financialModelShortId);
        self::assertSame('My financial model', $result->title);
        self::assertSame(1, $result->versionNumber);
        self::assertSame(1, $transactionalRunner->runCount);

        self::assertCount(1, $financialModelRepository->savedFinancialModels);
        $financialModel = $financialModelRepository->savedFinancialModels[0];

        self::assertTrue($financialModel->isOwnedBy($owner));
        self::assertSame('ab23456789', $financialModel->getShortId());
        self::assertSame('My financial model', $financialModel->getTitle());
        self::assertSame('Model description', $financialModel->getDescription());
        self::assertSame(1, $financialModel->getVersionNumber());
        self::assertSame(FinancialModelStatus::Active, $financialModel->getStatus());
        self::assertSame(AmountDisplayFormat::WholeRubles, $financialModel->getAmountDisplayFormat());

        $timeParams = $financialModel->getTimeParams();

        self::assertNotNull($timeParams);
        self::assertSame($financialModel, $timeParams->getFinancialModel());
        self::assertSame('2026-04', $timeParams->getInvestmentStartMonth()?->toString());
        self::assertSame(6, $timeParams->getInvestmentDurationMonths());
        self::assertSame(24, $timeParams->getCommercialOperationDurationMonths());
        self::assertSame(ForecastStep::Month, $timeParams->getForecastStep());
    }

    public function testCreatesNextVersionWhenOwnerAlreadyHasFinancialModel(): void
    {
        $owner = new User();
        $financialModelRepository = new InMemoryFinancialModelRepository();
        $financialModelRepository->save($this->createExistingFinancialModel($owner));

        $handler = new CreateFinancialModelHandler(
            transactionalRunner: new ImmediateTransactionalRunner(),
            idGenerator: new FixedShortIdGenerator('cdefghjkmn'),
            repository: $financialModelRepository,
        );

        $result = $handler->handle($this->createCommand($owner));

        self::assertSame('cdefghjkmn', $result->financialModelShortId);
        self::assertSame('My financial model', $result->title);
        self::assertSame(2, $result->versionNumber);

        self::assertCount(2, $financialModelRepository->savedFinancialModels);
        self::assertSame(2, $financialModelRepository->savedFinancialModels[1]->getVersionNumber());
        self::assertSame('My financial model', $financialModelRepository->savedFinancialModels[1]->getTitle());
    }

    public function testCreatesNextVersionOnlyForCurrentOwner(): void
    {
        $owner = new User();
        $anotherOwner = new User();
        $financialModelRepository = new InMemoryFinancialModelRepository();
        $financialModelRepository->save($this->createExistingFinancialModel($anotherOwner, versionNumber: 4));

        $handler = new CreateFinancialModelHandler(
            transactionalRunner: new ImmediateTransactionalRunner(),
            idGenerator: new FixedShortIdGenerator('defghjkmnp'),
            repository: $financialModelRepository,
        );

        $result = $handler->handle($this->createCommand($owner));

        self::assertSame(1, $result->versionNumber);
        self::assertCount(2, $financialModelRepository->savedFinancialModels);
        self::assertTrue($financialModelRepository->savedFinancialModels[1]->isOwnedBy($owner));
        self::assertSame(1, $financialModelRepository->savedFinancialModels[1]->getVersionNumber());
    }

    public function testRetriesShortIdGenerationWhenCollisionHappens(): void
    {
        $owner = new User();
        $financialModelRepository = new InMemoryFinancialModelRepository(existingShortIds: ['ab23456789']);
        $shortIdGenerator = new FixedShortIdGenerator('ab23456789', 'cdefghjkmn');

        $handler = new CreateFinancialModelHandler(
            transactionalRunner: new ImmediateTransactionalRunner(),
            idGenerator: $shortIdGenerator,
            repository: $financialModelRepository,
        );

        $result = $handler->handle($this->createCommand($owner));

        self::assertSame('cdefghjkmn', $result->financialModelShortId);
        self::assertSame(2, $shortIdGenerator->generatedCount);
        self::assertCount(1, $financialModelRepository->savedFinancialModels);
        self::assertSame('cdefghjkmn', $financialModelRepository->savedFinancialModels[0]->getShortId());
    }

    public function testThrowsWhenUniqueShortIdCannotBeGenerated(): void
    {
        $owner = new User();
        $financialModelRepository = new InMemoryFinancialModelRepository(existingShortIds: ['ab23456789']);
        $shortIdGenerator = new FixedShortIdGenerator(
            'ab23456789',
            'ab23456789',
            'ab23456789',
            'ab23456789',
            'ab23456789',
        );

        $handler = new CreateFinancialModelHandler(
            transactionalRunner: new ImmediateTransactionalRunner(),
            idGenerator: $shortIdGenerator,
            repository: $financialModelRepository,
        );

        try {
            $handler->handle($this->createCommand($owner));

            self::fail('Expected short id generation exception.');
        } catch (CreateFinancialModelShortIdGenerationException) {
            self::assertSame(5, $shortIdGenerator->generatedCount);
            self::assertSame([], $financialModelRepository->savedFinancialModels);
        }
    }

    private function createCommand(User $owner): CreateFinancialModelCommand
    {
        return new CreateFinancialModelCommand(
            owner: $owner,
            title: 'My financial model',
            description: 'Model description',
            investmentStartMonth: YearMonth::fromString('2026-04'),
            investmentDuration: MonthDuration::fromInt(6),
            commercialOperationDuration: MonthDuration::fromInt(24),
            forecastStep: ForecastStep::Month,
        );
    }

    private function createExistingFinancialModel(
        User $owner,
        int $versionNumber = 1,
    ): FinancialModel
    {
        $timeParams = TimeParams::create(
            investmentStartMonth: YearMonth::fromString('2026-04'),
            investmentDuration: MonthDuration::fromInt(6),
            commercialOperationDuration: MonthDuration::fromInt(24),
            forecastStep: ForecastStep::Month,
        );

        return FinancialModel::create(
            owner: $owner,
            shortId: ShortId::fromString('ab23456789'),
            title: 'Existing financial model',
            description: 'Model description',
            versionNumber: $versionNumber,
            timeParams: $timeParams,
        );
    }
}
