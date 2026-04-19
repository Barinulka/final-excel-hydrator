<?php

declare(strict_types=1);

namespace App\Tests\Application\FinancialModel\RestoreFinancialModel;

use App\Application\FinancialModel\RestoreFinancialModel\FinancialModelAlreadyActiveException;
use App\Application\FinancialModel\RestoreFinancialModel\FinancialModelForRestoreNotFoundException;
use App\Application\FinancialModel\RestoreFinancialModel\RestoreFinancialModelCommand;
use App\Application\FinancialModel\RestoreFinancialModel\RestoreFinancialModelHandler;
use App\Domain\FinancialModel\Enum\FinancialModelStatus;
use App\Domain\Shared\ValueObject\MonthDuration;
use App\Domain\Shared\ValueObject\ShortId;
use App\Domain\Shared\ValueObject\YearMonth;
use App\Domain\TimeParams\Enum\ForecastStep;
use App\Entity\FinancialModel;
use App\Entity\Project;
use App\Entity\TimeParams;
use App\Entity\User;
use App\Tests\Support\Application\FinancialModel\InMemoryFinancialModelRepository;
use App\Tests\Support\Application\Shared\Transaction\ImmediateTransactionalRunner;
use PHPUnit\Framework\TestCase;

final class RestoreFinancialModelHandlerTest extends TestCase
{
    public function testRestoresArchivedFinancialModelSuccessfully(): void
    {
        $owner = new User();
        $financialModel = $this->createArchivedFinancialModel($owner);
        $financialModelRepository = new InMemoryFinancialModelRepository();
        $financialModelRepository->save($financialModel);
        $transactionalRunner = new ImmediateTransactionalRunner();

        $handler = new RestoreFinancialModelHandler(
            financialModelRepository: $financialModelRepository,
            transactionalRunner: $transactionalRunner,
        );

        $result = $handler->handle($this->createCommand($owner));

        self::assertSame('23456789ab', $result->projectShortId);
        self::assertSame('ab23456789', $result->financialModelShortId);
        self::assertSame(FinancialModelStatus::Active->value, $result->status);
        self::assertFalse($result->isArchived);
        self::assertTrue($financialModel->isActive());
        self::assertFalse($financialModel->isArchived());
        self::assertSame(1, $transactionalRunner->runCount);
        self::assertCount(1, $financialModelRepository->savedFinancialModels);
        self::assertSame($financialModel, $financialModelRepository->savedFinancialModels[0]);
    }

    public function testThrowsWhenFinancialModelDoesNotExist(): void
    {
        $financialModelRepository = new InMemoryFinancialModelRepository();
        $transactionalRunner = new ImmediateTransactionalRunner();

        $handler = new RestoreFinancialModelHandler(
            financialModelRepository: $financialModelRepository,
            transactionalRunner: $transactionalRunner,
        );

        try {
            $handler->handle($this->createCommand(new User()));

            self::fail('Expected financial model not found exception.');
        } catch (FinancialModelForRestoreNotFoundException) {
            self::assertSame(1, $transactionalRunner->runCount);
            self::assertSame([], $financialModelRepository->savedFinancialModels);
        }
    }

    public function testThrowsWhenFinancialModelBelongsToAnotherOwner(): void
    {
        $modelOwner = new User();
        $commandOwner = new User();
        $financialModel = $this->createArchivedFinancialModel($modelOwner);
        $financialModelRepository = new InMemoryFinancialModelRepository();
        $financialModelRepository->save($financialModel);

        $handler = new RestoreFinancialModelHandler(
            financialModelRepository: $financialModelRepository,
            transactionalRunner: new ImmediateTransactionalRunner(),
        );

        try {
            $handler->handle($this->createCommand($commandOwner));

            self::fail('Expected financial model not found exception.');
        } catch (FinancialModelForRestoreNotFoundException) {
            self::assertTrue($financialModel->isArchived());
            self::assertFalse($financialModel->isActive());
            self::assertCount(1, $financialModelRepository->savedFinancialModels);
            self::assertSame($financialModel, $financialModelRepository->savedFinancialModels[0]);
        }
    }

    public function testThrowsWhenProjectShortIdDoesNotMatch(): void
    {
        $owner = new User();
        $financialModel = $this->createArchivedFinancialModel($owner);
        $financialModelRepository = new InMemoryFinancialModelRepository();
        $financialModelRepository->save($financialModel);

        $handler = new RestoreFinancialModelHandler(
            financialModelRepository: $financialModelRepository,
            transactionalRunner: new ImmediateTransactionalRunner(),
        );

        try {
            $handler->handle($this->createCommand($owner, projectShortId: '3456789abc'));

            self::fail('Expected financial model not found exception.');
        } catch (FinancialModelForRestoreNotFoundException) {
            self::assertTrue($financialModel->isArchived());
            self::assertFalse($financialModel->isActive());
            self::assertCount(1, $financialModelRepository->savedFinancialModels);
            self::assertSame($financialModel, $financialModelRepository->savedFinancialModels[0]);
        }
    }

    public function testThrowsWhenFinancialModelIsAlreadyActive(): void
    {
        $owner = new User();
        $financialModel = $this->createFinancialModel($owner);
        $financialModelRepository = new InMemoryFinancialModelRepository();
        $financialModelRepository->save($financialModel);
        $transactionalRunner = new ImmediateTransactionalRunner();

        $handler = new RestoreFinancialModelHandler(
            financialModelRepository: $financialModelRepository,
            transactionalRunner: $transactionalRunner,
        );

        try {
            $handler->handle($this->createCommand($owner));

            self::fail('Expected financial model already active exception.');
        } catch (FinancialModelAlreadyActiveException) {
            self::assertTrue($financialModel->isActive());
            self::assertFalse($financialModel->isArchived());
            self::assertSame(1, $transactionalRunner->runCount);
            self::assertCount(1, $financialModelRepository->savedFinancialModels);
            self::assertSame($financialModel, $financialModelRepository->savedFinancialModels[0]);
        }
    }

    private function createCommand(
        User $owner,
        string $projectShortId = '23456789ab',
        string $financialModelShortId = 'ab23456789',
    ): RestoreFinancialModelCommand {
        return new RestoreFinancialModelCommand(
            owner: $owner,
            projectShortId: ShortId::fromString($projectShortId),
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
        $project = Project::create(
            owner: $owner,
            shortId: ShortId::fromString('23456789ab'),
            title: 'Test Project',
            description: null,
        );

        $timeParams = TimeParams::create(
            investmentStartMonth: YearMonth::fromString('2026-01'),
            investmentDuration: MonthDuration::fromInt(3),
            commercialOperationDuration: MonthDuration::fromInt(12),
            forecastStep: ForecastStep::Month,
        );

        return FinancialModel::create(
            project: $project,
            shortId: ShortId::fromString('ab23456789'),
            title: 'Test Project v1',
            versionNumber: 1,
            timeParams: $timeParams,
        );
    }
}
