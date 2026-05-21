<?php

declare(strict_types=1);

namespace App\Tests\Application\ExcelExport\CreateExcelExport;

use App\Application\Calculation\BuildFinancialModelCalculation\BuildFinancialModelCalculationHandler;
use App\Application\Calculation\CalculationResultPayloadFactory;
use App\Application\ExcelExport\CreateExcelExport\ArchivedFinancialModelCannotBeExportedException;
use App\Application\ExcelExport\CreateExcelExport\CreateExcelExportCommand;
use App\Application\ExcelExport\CreateExcelExport\CreateExcelExportHandler;
use App\Application\ExcelExport\CreateExcelExport\FinancialModelForExcelExportNotFoundException;
use App\Domain\ExcelExport\Enum\ExcelExportStatus;
use App\Domain\Shared\ValueObject\MonthDuration;
use App\Domain\Shared\ValueObject\ShortId;
use App\Domain\Shared\ValueObject\YearMonth;
use App\Domain\TimeParams\Calculation\TimelineCalculator;
use App\Domain\TimeParams\Enum\ForecastStep;
use App\Entity\ExcelExport;
use App\Entity\FinancialModel;
use App\Entity\TimeParams;
use App\Entity\User;
use App\Tests\Support\Application\ExcelExport\InMemoryExcelExportRepository;
use App\Tests\Support\Application\FinancialModel\InMemoryFinancialModelRepository;
use App\Tests\Support\Application\Shared\Transaction\ImmediateTransactionalRunner;
use PHPUnit\Framework\TestCase;

final class CreateExcelExportHandlerTest extends TestCase
{
    public function testCreatesPendingExcelExportForFinancialModel(): void
    {
        $owner = $this->createUser();
        $financialModel = $this->createFinancialModelWithoutProject($owner);
        $financialModelRepository = new InMemoryFinancialModelRepository();
        $financialModelRepository->save($financialModel);
        $excelExportRepository = new InMemoryExcelExportRepository();
        $transactionalRunner = new ImmediateTransactionalRunner();

        $handler = $this->createHandler(
            excelExportRepository: $excelExportRepository,
            financialModelRepository: $financialModelRepository,
            transactionalRunner: $transactionalRunner,
        );

        $result = $handler->handle($this->createCommand($owner));

        self::assertNull($result->exportId);
        self::assertSame('ab23456789', $result->financialModelShortId);
        self::assertSame(ExcelExportStatus::Pending->value, $result->status);
        self::assertSame(1, $transactionalRunner->runCount);
        self::assertCount(1, $excelExportRepository->savedExcelExports);

        $savedExcelExport = $excelExportRepository->savedExcelExports[0];

        self::assertInstanceOf(ExcelExport::class, $savedExcelExport);
        self::assertSame($financialModel, $savedExcelExport->getFinancialModel());
        self::assertSame(ExcelExportStatus::Pending, $savedExcelExport->getStatus());
        self::assertSame('timeline', $savedExcelExport->getCalculationResultPayload()['tables'][0]['code']);
        self::assertSame('Временная шкала', $savedExcelExport->getCalculationResultPayload()['tables'][0]['title']);
        self::assertSame('2026-04', $savedExcelExport->getCalculationResultPayload()['tables'][0]['periods'][0]);
        self::assertSame(30, $savedExcelExport->getCalculationResultPayload()['metrics']['period_count']);
        self::assertSame([], $savedExcelExport->getCalculationResultPayload()['warnings']);
    }

    public function testThrowsWhenFinancialModelDoesNotExist(): void
    {
        $excelExportRepository = new InMemoryExcelExportRepository();
        $handler = $this->createHandler(
            excelExportRepository: $excelExportRepository,
            financialModelRepository: new InMemoryFinancialModelRepository(),
            transactionalRunner: new ImmediateTransactionalRunner(),
        );

        $this->expectException(FinancialModelForExcelExportNotFoundException::class);

        try {
            $handler->handle($this->createCommand($this->createUser()));
        } finally {
            self::assertSame([], $excelExportRepository->savedExcelExports);
        }
    }

    public function testThrowsWhenFinancialModelBelongsToAnotherOwner(): void
    {
        $modelOwner = $this->createUser();
        $queryOwner = $this->createUser(email: 'another-owner@example.com');
        $financialModelRepository = new InMemoryFinancialModelRepository();
        $financialModelRepository->save($this->createFinancialModel($this->createProject($modelOwner)));
        $excelExportRepository = new InMemoryExcelExportRepository();
        $handler = $this->createHandler(
            excelExportRepository: $excelExportRepository,
            financialModelRepository: $financialModelRepository,
            transactionalRunner: new ImmediateTransactionalRunner(),
        );

        $this->expectException(FinancialModelForExcelExportNotFoundException::class);

        try {
            $handler->handle($this->createCommand($queryOwner));
        } finally {
            self::assertSame([], $excelExportRepository->savedExcelExports);
        }
    }

    public function testThrowsWhenFinancialModelIsArchived(): void
    {
        $owner = $this->createUser();
        $financialModel = $this->createFinancialModel($this->createProject($owner));
        $financialModel->archive();
        $financialModelRepository = new InMemoryFinancialModelRepository();
        $financialModelRepository->save($financialModel);
        $excelExportRepository = new InMemoryExcelExportRepository();
        $handler = $this->createHandler(
            excelExportRepository: $excelExportRepository,
            financialModelRepository: $financialModelRepository,
            transactionalRunner: new ImmediateTransactionalRunner(),
        );

        $this->expectException(ArchivedFinancialModelCannotBeExportedException::class);

        try {
            $handler->handle($this->createCommand($owner));
        } finally {
            self::assertSame([], $excelExportRepository->savedExcelExports);
        }
    }

    private function createHandler(
        InMemoryExcelExportRepository $excelExportRepository,
        InMemoryFinancialModelRepository $financialModelRepository,
        ImmediateTransactionalRunner $transactionalRunner,
    ): CreateExcelExportHandler {
        return new CreateExcelExportHandler(
            excelExportRepository: $excelExportRepository,
            financialModelRepository: $financialModelRepository,
            transactionalRunner: $transactionalRunner,
            calculationHandler: new BuildFinancialModelCalculationHandler(
                financialModelRepository: $financialModelRepository,
                timelineCalculator: new TimelineCalculator(),
            ),
            calculationResultPayloadFactory: new CalculationResultPayloadFactory(),
        );
    }

    private function createCommand(
        User $owner,
        string $financialModelShortId = 'ab23456789',
    ): CreateExcelExportCommand {
        return new CreateExcelExportCommand(
            owner: $owner,
            financialModelShortId: ShortId::fromString($financialModelShortId),
        );
    }

    private function createUser(string $email = 'owner@example.com'): User
    {
        return (new User())
            ->setEmail($email)
            ->setPassword('hashed-password');
    }

    private function createProject(User $owner): User
    {
        return $owner;
    }

    private function createFinancialModel(User $owner): FinancialModel
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
            title: 'Test Project v1',
            description: null,
            versionNumber: 1,
            timeParams: $timeParams,
        );
    }

    private function createFinancialModelWithoutProject(User $owner): FinancialModel
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
            title: 'Test Model',
            description: null,
            versionNumber: 1,
            timeParams: $timeParams,
        );
    }
}
