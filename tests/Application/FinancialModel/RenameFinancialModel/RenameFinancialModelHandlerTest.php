<?php

declare(strict_types=1);

namespace App\Tests\Application\FinancialModel\RenameFinancialModel;

use App\Application\FinancialModel\RenameFinancialModel\ArchivedFinancialModelCannotBeRenamedException;
use App\Application\FinancialModel\RenameFinancialModel\FinancialModelForRenameNotFoundException;
use App\Application\FinancialModel\RenameFinancialModel\RenameFinancialModelCommand;
use App\Application\FinancialModel\RenameFinancialModel\RenameFinancialModelHandler;
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

final class RenameFinancialModelHandlerTest extends TestCase
{
    public function testRenamesActiveFinancialModelSuccessfully(): void
    {
        $owner = new User();
        $financialModel = $this->createFinancialModel($owner);
        $financialModelRepository = new InMemoryFinancialModelRepository();
        $financialModelRepository->save($financialModel);
        $transactionalRunner = new ImmediateTransactionalRunner();

        $handler = new RenameFinancialModelHandler(
            financialModelRepository: $financialModelRepository,
            transactionalRunner: $transactionalRunner,
        );

        $result = $handler->handle($this->createCommand($owner, title: 'Базовый сценарий'));

        self::assertSame('23456789ab', $result->projectShortId);
        self::assertSame('ab23456789', $result->financialModelShortId);
        self::assertSame('Базовый сценарий', $result->title);
        self::assertSame('Базовый сценарий', $financialModel->getTitle());
        self::assertSame(1, $transactionalRunner->runCount);
        self::assertCount(1, $financialModelRepository->savedFinancialModels);
        self::assertSame($financialModel, $financialModelRepository->savedFinancialModels[0]);
    }

    public function testReturnsTrimmedTitle(): void
    {
        $owner = new User();
        $financialModel = $this->createFinancialModel($owner);
        $financialModelRepository = new InMemoryFinancialModelRepository();
        $financialModelRepository->save($financialModel);

        $handler = new RenameFinancialModelHandler(
            financialModelRepository: $financialModelRepository,
            transactionalRunner: new ImmediateTransactionalRunner(),
        );

        $result = $handler->handle($this->createCommand($owner, title: '  Оптимистичный сценарий  '));

        self::assertSame('Оптимистичный сценарий', $result->title);
        self::assertSame('Оптимистичный сценарий', $financialModel->getTitle());
    }

    public function testThrowsWhenFinancialModelDoesNotExist(): void
    {
        $financialModelRepository = new InMemoryFinancialModelRepository();
        $transactionalRunner = new ImmediateTransactionalRunner();

        $handler = new RenameFinancialModelHandler(
            financialModelRepository: $financialModelRepository,
            transactionalRunner: $transactionalRunner,
        );

        try {
            $handler->handle($this->createCommand(new User()));

            self::fail('Expected financial model not found exception.');
        } catch (FinancialModelForRenameNotFoundException) {
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

        $handler = new RenameFinancialModelHandler(
            financialModelRepository: $financialModelRepository,
            transactionalRunner: new ImmediateTransactionalRunner(),
        );

        try {
            $handler->handle($this->createCommand($commandOwner));

            self::fail('Expected financial model not found exception.');
        } catch (FinancialModelForRenameNotFoundException) {
            self::assertSame('Test Project v1', $financialModel->getTitle());
            self::assertCount(1, $financialModelRepository->savedFinancialModels);
            self::assertSame($financialModel, $financialModelRepository->savedFinancialModels[0]);
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

        $handler = new RenameFinancialModelHandler(
            financialModelRepository: $financialModelRepository,
            transactionalRunner: $transactionalRunner,
        );

        try {
            $handler->handle($this->createCommand($owner));

            self::fail('Expected archived financial model exception.');
        } catch (ArchivedFinancialModelCannotBeRenamedException) {
            self::assertSame('Test Project v1', $financialModel->getTitle());
            self::assertSame(1, $transactionalRunner->runCount);
            self::assertCount(1, $financialModelRepository->savedFinancialModels);
            self::assertSame($financialModel, $financialModelRepository->savedFinancialModels[0]);
        }
    }

    private function createCommand(
        User $owner,
        string $projectShortId = '23456789ab',
        string $financialModelShortId = 'ab23456789',
        string $title = 'Новое название',
    ): RenameFinancialModelCommand {
        return new RenameFinancialModelCommand(
            owner: $owner,
            projectShortId: ShortId::fromString($projectShortId),
            financialModelShortId: ShortId::fromString($financialModelShortId),
            title: $title,
        );
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
