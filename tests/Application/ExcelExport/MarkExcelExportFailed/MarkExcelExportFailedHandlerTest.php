<?php

declare(strict_types=1);

namespace App\Tests\Application\ExcelExport\MarkExcelExportFailed;

use App\Application\ExcelExport\MarkExcelExportFailed\ExcelExportForFailureNotFoundException;
use App\Application\ExcelExport\MarkExcelExportFailed\MarkExcelExportFailedCommand;
use App\Application\ExcelExport\MarkExcelExportFailed\MarkExcelExportFailedHandler;
use App\Domain\ExcelExport\Enum\ExcelExportStatus;
use App\Domain\Shared\ValueObject\MonthDuration;
use App\Domain\Shared\ValueObject\ShortId;
use App\Domain\Shared\ValueObject\YearMonth;
use App\Domain\TimeParams\Enum\ForecastStep;
use App\Entity\ExcelExport;
use App\Entity\FinancialModel;
use App\Entity\Project;
use App\Entity\TimeParams;
use App\Entity\User;
use App\Tests\Support\Application\ExcelExport\InMemoryExcelExportRepository;
use App\Tests\Support\Application\Shared\Transaction\ImmediateTransactionalRunner;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class MarkExcelExportFailedHandlerTest extends TestCase
{
    public function testMarksProcessingExcelExportAsFailed(): void
    {
        $repository = new InMemoryExcelExportRepository();
        $transactionalRunner = new ImmediateTransactionalRunner();
        $project = $this->createProject();
        $financialModel = $this->createFinancialModel($project);
        $excelExport = $this->createExcelExport($project, $financialModel, id: 15);
        $excelExport->markProcessing();
        $repository->save($excelExport);
        $handler = new MarkExcelExportFailedHandler(
            excelExportRepository: $repository,
            transactionalRunner: $transactionalRunner,
        );

        $result = $handler->handle(new MarkExcelExportFailedCommand(
            exportId: 15,
            errorMessage: '  Go worker timeout  ',
        ));

        self::assertSame(1, $transactionalRunner->runCount);
        self::assertSame(15, $result->exportId);
        self::assertSame(ExcelExportStatus::Failed->value, $result->status);
        self::assertSame('Go worker timeout', $result->errorMessage);
        self::assertNotNull($result->failedAt);
        self::assertSame(ExcelExportStatus::Failed, $excelExport->getStatus());
        self::assertSame('Go worker timeout', $excelExport->getErrorMessage());
        self::assertInstanceOf(\DateTimeImmutable::class, $excelExport->getFailedAt());
        self::assertFalse($excelExport->isCompleted());
        self::assertSame($excelExport, $repository->findById(15));
    }

    public function testThrowsWhenExcelExportDoesNotExist(): void
    {
        $transactionalRunner = new ImmediateTransactionalRunner();
        $handler = new MarkExcelExportFailedHandler(
            excelExportRepository: new InMemoryExcelExportRepository(),
            transactionalRunner: $transactionalRunner,
        );

        $this->expectException(ExcelExportForFailureNotFoundException::class);
        $this->expectExceptionMessage("Excel export '404' не найден.");

        try {
            $handler->handle(new MarkExcelExportFailedCommand(
                exportId: 404,
                errorMessage: 'Go worker timeout',
            ));
        } finally {
            self::assertSame(1, $transactionalRunner->runCount);
        }
    }

    public function testThrowsWhenExcelExportIsStillPending(): void
    {
        $repository = new InMemoryExcelExportRepository();
        $project = $this->createProject();
        $financialModel = $this->createFinancialModel($project);
        $excelExport = $this->createExcelExport($project, $financialModel, id: 15);
        $repository->save($excelExport);
        $handler = new MarkExcelExportFailedHandler(
            excelExportRepository: $repository,
            transactionalRunner: new ImmediateTransactionalRunner(),
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Ошибкой можно завершить только processing Excel export.');

        $handler->handle(new MarkExcelExportFailedCommand(
            exportId: 15,
            errorMessage: 'Go worker timeout',
        ));
    }

    private function createExcelExport(Project $project, FinancialModel $financialModel, int $id): ExcelExport
    {
        $excelExport = ExcelExport::create(
            project: $project,
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

    private function createProject(): Project
    {
        $user = (new User())
            ->setEmail('owner@example.com')
            ->setPassword('hashed-password');

        return Project::create(
            owner: $user,
            shortId: ShortId::fromString('abcdefghjk'),
            title: 'Проект',
        );
    }

    private function createFinancialModel(Project $project): FinancialModel
    {
        $timeParams = TimeParams::create(
            investmentStartMonth: YearMonth::fromString('2026-05'),
            investmentDuration: MonthDuration::fromInt(6),
            commercialOperationDuration: MonthDuration::fromInt(24),
            forecastStep: ForecastStep::Month,
        );

        return FinancialModel::create(
            project: $project,
            shortId: ShortId::fromString('mnpqrstuvw'),
            title: 'Модель',
            versionNumber: 1,
            timeParams: $timeParams,
        );
    }
}
