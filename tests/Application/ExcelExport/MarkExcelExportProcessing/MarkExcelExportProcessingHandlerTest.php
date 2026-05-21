<?php

declare(strict_types=1);

namespace App\Tests\Application\ExcelExport\MarkExcelExportProcessing;

use App\Application\ExcelExport\MarkExcelExportProcessing\ExcelExportForProcessingNotFoundException;
use App\Application\ExcelExport\MarkExcelExportProcessing\MarkExcelExportProcessingCommand;
use App\Application\ExcelExport\MarkExcelExportProcessing\MarkExcelExportProcessingHandler;
use App\Domain\ExcelExport\Enum\ExcelExportStatus;
use App\Domain\Shared\ValueObject\MonthDuration;
use App\Domain\Shared\ValueObject\ShortId;
use App\Domain\Shared\ValueObject\YearMonth;
use App\Domain\TimeParams\Enum\ForecastStep;
use App\Entity\ExcelExport;
use App\Entity\FinancialModel;
use App\Entity\TimeParams;
use App\Entity\User;
use App\Tests\Support\Application\ExcelExport\InMemoryExcelExportRepository;
use App\Tests\Support\Application\Shared\Transaction\ImmediateTransactionalRunner;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class MarkExcelExportProcessingHandlerTest extends TestCase
{
    public function testMarksPendingExcelExportAsProcessing(): void
    {
        $repository = new InMemoryExcelExportRepository();
        $transactionalRunner = new ImmediateTransactionalRunner();
        $project = $this->createProject();
        $financialModel = $this->createFinancialModel($project);
        $excelExport = $this->createExcelExport($project, $financialModel, id: 15);
        $repository->save($excelExport);
        $handler = new MarkExcelExportProcessingHandler(
            excelExportRepository: $repository,
            transactionalRunner: $transactionalRunner,
        );

        $result = $handler->handle(new MarkExcelExportProcessingCommand(exportId: 15));

        self::assertSame(1, $transactionalRunner->runCount);
        self::assertSame(15, $result->exportId);
        self::assertSame(ExcelExportStatus::Processing->value, $result->status);
        self::assertNotNull($result->startedAt);
        self::assertSame(ExcelExportStatus::Processing, $excelExport->getStatus());
        self::assertInstanceOf(\DateTimeImmutable::class, $excelExport->getStartedAt());
        self::assertSame($excelExport, $repository->findById(15));
    }

    public function testThrowsWhenExcelExportDoesNotExist(): void
    {
        $transactionalRunner = new ImmediateTransactionalRunner();
        $handler = new MarkExcelExportProcessingHandler(
            excelExportRepository: new InMemoryExcelExportRepository(),
            transactionalRunner: $transactionalRunner,
        );

        $this->expectException(ExcelExportForProcessingNotFoundException::class);
        $this->expectExceptionMessage("Excel export '404' не найден.");

        try {
            $handler->handle(new MarkExcelExportProcessingCommand(exportId: 404));
        } finally {
            self::assertSame(1, $transactionalRunner->runCount);
        }
    }

    public function testThrowsWhenExcelExportIsAlreadyProcessing(): void
    {
        $repository = new InMemoryExcelExportRepository();
        $project = $this->createProject();
        $financialModel = $this->createFinancialModel($project);
        $excelExport = $this->createExcelExport($project, $financialModel, id: 15);
        $excelExport->markProcessing();
        $repository->save($excelExport);
        $handler = new MarkExcelExportProcessingHandler(
            excelExportRepository: $repository,
            transactionalRunner: new ImmediateTransactionalRunner(),
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('В обработку можно взять только pending Excel export.');

        $handler->handle(new MarkExcelExportProcessingCommand(exportId: 15));
    }

    private function createExcelExport(User $owner, FinancialModel $financialModel, int $id): ExcelExport
    {
        $excelExport = ExcelExport::create(
            financialModel: $financialModel,
            calculationResultPayload: $this->createCalculationResultPayload(),
        );

        $idProperty = new \ReflectionProperty(ExcelExport::class, 'id');
        $idProperty->setValue($excelExport, $id);

        return $excelExport;
    }

    private function createCalculationResultPayload(): array
    {
        return [
            'tables' => [
                [
                    'code' => 'timeline',
                    'title' => 'Временная шкала',
                    'periods' => ['2026-05'],
                    'rows' => [],
                ],
            ],
            'metrics' => [
                'period_count' => 1,
            ],
            'warnings' => [],
        ];
    }

    private function createProject(): User
    {
        $user = (new User())
            ->setEmail('owner@example.com')
            ->setPassword('hashed-password');
        return $user;
    }

    private function createFinancialModel(User $owner): FinancialModel
    {
        $timeParams = TimeParams::create(
            investmentStartMonth: YearMonth::fromString('2026-05'),
            investmentDuration: MonthDuration::fromInt(6),
            commercialOperationDuration: MonthDuration::fromInt(24),
            forecastStep: ForecastStep::Month,
        );

        return FinancialModel::create(
            owner: $owner,
            shortId: ShortId::fromString('mnpqrstuvw'),
            title: 'Модель',
            description: null,
            versionNumber: 1,
            timeParams: $timeParams,
        );
    }
}
