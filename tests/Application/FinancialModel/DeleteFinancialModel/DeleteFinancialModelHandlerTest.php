<?php

declare(strict_types=1);

namespace App\Tests\Application\FinancialModel\DeleteFinancialModel;

use App\Application\FinancialModel\DeleteFinancialModel\ActiveFinancialModelCannotBeDeletedException;
use App\Application\FinancialModel\DeleteFinancialModel\DeleteFinancialModelCommand;
use App\Application\FinancialModel\DeleteFinancialModel\DeleteFinancialModelHandler;
use App\Application\FinancialModel\DeleteFinancialModel\FinancialModelForDeleteNotFoundException;
use App\Domain\Shared\ValueObject\MonthDuration;
use App\Domain\Shared\ValueObject\ShortId;
use App\Domain\Shared\ValueObject\YearMonth;
use App\Domain\TimeParams\Enum\ForecastStep;
use App\Entity\FinancialModel;
use App\Entity\TimeParams;
use App\Entity\User;
use App\Tests\Support\Application\FinancialModel\InMemoryFinancialModelRepository;
use App\Tests\Support\Application\Shared\Transaction\ImmediateTransactionalRunner;
use PHPUnit\Framework\TestCase;

final class DeleteFinancialModelHandlerTest extends TestCase
{
    public function testDeletesArchivedFinancialModelSuccessfully(): void
    {
        $owner = new User();
        $financialModel = $this->createArchivedFinancialModel($owner);
        $financialModelRepository = new InMemoryFinancialModelRepository();
        $financialModelRepository->save($financialModel);
        $transactionalRunner = new ImmediateTransactionalRunner();

        $handler = new DeleteFinancialModelHandler(
            financialModelRepository: $financialModelRepository,
            transactionalRunner: $transactionalRunner,
        );

        $result = $handler->handle($this->createCommand($owner));

        self::assertSame('ab23456789', $result->financialModelShortId);
        self::assertTrue($result->isDeleted);
        self::assertSame(1, $transactionalRunner->runCount);
        self::assertSame([], $financialModelRepository->savedFinancialModels);
        self::assertCount(1, $financialModelRepository->removedFinancialModels);
        self::assertSame($financialModel, $financialModelRepository->removedFinancialModels[0]);
    }

    public function testThrowsWhenFinancialModelDoesNotExist(): void
    {
        $financialModelRepository = new InMemoryFinancialModelRepository();
        $transactionalRunner = new ImmediateTransactionalRunner();

        $handler = new DeleteFinancialModelHandler(
            financialModelRepository: $financialModelRepository,
            transactionalRunner: $transactionalRunner,
        );

        try {
            $handler->handle($this->createCommand(new User()));

            self::fail('Expected financial model not found exception.');
        } catch (FinancialModelForDeleteNotFoundException) {
            self::assertSame(1, $transactionalRunner->runCount);
            self::assertSame([], $financialModelRepository->savedFinancialModels);
            self::assertSame([], $financialModelRepository->removedFinancialModels);
        }
    }

    public function testThrowsWhenFinancialModelBelongsToAnotherOwner(): void
    {
        $modelOwner = new User();
        $commandOwner = new User();
        $financialModel = $this->createArchivedFinancialModel($modelOwner);
        $financialModelRepository = new InMemoryFinancialModelRepository();
        $financialModelRepository->save($financialModel);

        $handler = new DeleteFinancialModelHandler(
            financialModelRepository: $financialModelRepository,
            transactionalRunner: new ImmediateTransactionalRunner(),
        );

        try {
            $handler->handle($this->createCommand($commandOwner));

            self::fail('Expected financial model not found exception.');
        } catch (FinancialModelForDeleteNotFoundException) {
            self::assertTrue($financialModel->isArchived());
            self::assertCount(1, $financialModelRepository->savedFinancialModels);
            self::assertSame([], $financialModelRepository->removedFinancialModels);
            self::assertSame($financialModel, $financialModelRepository->savedFinancialModels[0]);
        }
    }

    public function testThrowsWhenFinancialModelIsActive(): void
    {
        $owner = new User();
        $financialModel = $this->createFinancialModel($owner);
        $financialModelRepository = new InMemoryFinancialModelRepository();
        $financialModelRepository->save($financialModel);
        $transactionalRunner = new ImmediateTransactionalRunner();

        $handler = new DeleteFinancialModelHandler(
            financialModelRepository: $financialModelRepository,
            transactionalRunner: $transactionalRunner,
        );

        try {
            $handler->handle($this->createCommand($owner));

            self::fail('Expected active financial model cannot be deleted exception.');
        } catch (ActiveFinancialModelCannotBeDeletedException) {
            self::assertTrue($financialModel->isActive());
            self::assertFalse($financialModel->isArchived());
            self::assertSame(1, $transactionalRunner->runCount);
            self::assertCount(1, $financialModelRepository->savedFinancialModels);
            self::assertSame([], $financialModelRepository->removedFinancialModels);
            self::assertSame($financialModel, $financialModelRepository->savedFinancialModels[0]);
        }
    }

    private function createCommand(
        User $owner,
        string $financialModelShortId = 'ab23456789',
    ): DeleteFinancialModelCommand {
        return new DeleteFinancialModelCommand(
            owner: $owner,
            financialModelShortId: ShortId::fromString($financialModelShortId),
        );
    }

    private function createArchivedFinancialModel(User $owner): FinancialModel
    {
        $financialModel = $this->createFinancialModel($owner);
        $financialModel->archive();

        return $financialModel;
    }

    private function createFinancialModel(User $owner): FinancialModel
    {
        $timeParams = TimeParams::create(
            investmentStartMonth: YearMonth::fromString('2026-01'),
            investmentDuration: MonthDuration::fromInt(3),
            commercialOperationDuration: MonthDuration::fromInt(12),
            forecastStep: ForecastStep::Month,
        );

        return FinancialModel::create(
            owner: $owner,
            shortId: ShortId::fromString('ab23456789'),
            title: 'Test Project v1',
            description: null,
            versionNumber: 1,
            timeParams: $timeParams,
        );
    }
}
