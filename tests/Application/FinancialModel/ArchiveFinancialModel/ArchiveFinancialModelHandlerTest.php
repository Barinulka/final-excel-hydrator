<?php

declare(strict_types=1);

namespace App\Tests\Application\FinancialModel\ArchiveFinancialModel;

use App\Application\FinancialModel\ArchiveFinancialModel\ArchiveFinancialModelCommand;
use App\Application\FinancialModel\ArchiveFinancialModel\ArchiveFinancialModelHandler;
use App\Application\FinancialModel\ArchiveFinancialModel\FinancialModelAlreadyArchivedException;
use App\Application\FinancialModel\ArchiveFinancialModel\FinancialModelForArchiveNotFoundException;
use App\Domain\FinancialModel\Enum\FinancialModelStatus;
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

final class ArchiveFinancialModelHandlerTest extends TestCase
{
    public function testArchivesActiveFinancialModelSuccessfully(): void
    {
        $owner = new User();
        $financialModel = $this->createFinancialModel($owner);
        $financialModelRepository = new InMemoryFinancialModelRepository();
        $financialModelRepository->save($financialModel);
        $transactionalRunner = new ImmediateTransactionalRunner();

        $handler = new ArchiveFinancialModelHandler(
            financialModelRepository: $financialModelRepository,
            transactionalRunner: $transactionalRunner,
        );

        $result = $handler->handle($this->createCommand($owner));

        self::assertSame('ab23456789', $result->financialModelShortId);
        self::assertSame(FinancialModelStatus::Archived->value, $result->status);
        self::assertTrue($result->isArchived);
        self::assertTrue($financialModel->isArchived());
        self::assertFalse($financialModel->isActive());
        self::assertSame(1, $transactionalRunner->runCount);
        self::assertCount(1, $financialModelRepository->savedFinancialModels);
        self::assertSame($financialModel, $financialModelRepository->savedFinancialModels[0]);
        self::assertNotNull($financialModel->getArchivedAt());
        self::assertNotNull($result->archivedAt);
        self::assertSame(
            $financialModel->getArchivedAt()->format(\DateTimeInterface::ATOM),
            $result->archivedAt,
        );
    }

    public function testThrowsWhenFinancialModelDoesNotExist(): void
    {
        $financialModelRepository = new InMemoryFinancialModelRepository();
        $transactionalRunner = new ImmediateTransactionalRunner();

        $handler = new ArchiveFinancialModelHandler(
            financialModelRepository: $financialModelRepository,
            transactionalRunner: $transactionalRunner,
        );

        try {
            $handler->handle($this->createCommand(new User()));

            self::fail('Expected financial model not found exception.');
        } catch (FinancialModelForArchiveNotFoundException) {
            self::assertSame(1, $transactionalRunner->runCount);
            self::assertSame([], $financialModelRepository->savedFinancialModels);
        }
    }

    public function testThrowsWhenFinancialModelBelongsToAnotherOwner(): void
    {
        $modelOwner = new User();
        $commandOwner = new User();
        $financialModel = $this->createFinancialModel($modelOwner);
        $financialModelRepository = new InMemoryFinancialModelRepository();
        $financialModelRepository->save($financialModel);

        $handler = new ArchiveFinancialModelHandler(
            financialModelRepository: $financialModelRepository,
            transactionalRunner: new ImmediateTransactionalRunner(),
        );

        try {
            $handler->handle($this->createCommand($commandOwner));

            self::fail('Expected financial model not found exception.');
        } catch (FinancialModelForArchiveNotFoundException) {
            self::assertTrue($financialModel->isActive());
            self::assertFalse($financialModel->isArchived());
            self::assertNull($financialModel->getArchivedAt());
            self::assertCount(1, $financialModelRepository->savedFinancialModels);
            self::assertSame($financialModel, $financialModelRepository->savedFinancialModels[0]);
        }
    }

    public function testThrowsWhenFinancialModelIsAlreadyArchived(): void
    {
        $owner = new User();
        $financialModel = $this->createFinancialModel($owner);
        $financialModel->archive();

        $financialModelRepository = new InMemoryFinancialModelRepository();
        $financialModelRepository->save($financialModel);
        $transactionalRunner = new ImmediateTransactionalRunner();

        $archivedAt = $financialModel->getArchivedAt();

        $handler = new ArchiveFinancialModelHandler(
            financialModelRepository: $financialModelRepository,
            transactionalRunner: $transactionalRunner,
        );

        try {
            $handler->handle($this->createCommand($owner));

            self::fail('Expected financial model already archived exception.');
        } catch (FinancialModelAlreadyArchivedException) {
            self::assertTrue($financialModel->isArchived());
            self::assertSame(1, $transactionalRunner->runCount);
            self::assertCount(1, $financialModelRepository->savedFinancialModels);
            self::assertSame($financialModel, $financialModelRepository->savedFinancialModels[0]);
            self::assertSame($archivedAt, $financialModel->getArchivedAt());
        }
    }

    private function createCommand(
        User $owner,
        string $financialModelShortId = 'ab23456789',
    ): ArchiveFinancialModelCommand {
        return new ArchiveFinancialModelCommand(
            owner: $owner,
            financialModelShortId: ShortId::fromString($financialModelShortId),
        );
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
