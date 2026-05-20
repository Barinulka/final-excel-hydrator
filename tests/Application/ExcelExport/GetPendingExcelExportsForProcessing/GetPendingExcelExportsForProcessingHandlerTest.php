<?php

declare(strict_types=1);

namespace App\Tests\Application\ExcelExport\GetPendingExcelExportsForProcessing;

use App\Application\ExcelExport\GetPendingExcelExportsForProcessing\GetPendingExcelExportsForProcessingHandler;
use App\Application\ExcelExport\GetPendingExcelExportsForProcessing\GetPendingExcelExportsForProcessingQuery;
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
use LogicException;
use PHPUnit\Framework\TestCase;

final class GetPendingExcelExportsForProcessingHandlerTest extends TestCase
{
    public function testReturnsPendingExportsForProcessing(): void
    {
        $repository = new InMemoryExcelExportRepository();
        $project = $this->createProject();
        $financialModel = $this->createFinancialModel($project);
        $newPendingExport = $this->createExcelExport(
            project: $project,
            financialModel: $financialModel,
            id: 10,
            createdAt: '2026-05-01 12:00:00',
        );
        $oldPendingExport = $this->createExcelExport(
            project: $project,
            financialModel: $financialModel,
            id: 11,
            createdAt: '2026-05-01 10:00:00',
        );
        $middlePendingExport = $this->createExcelExport(
            project: $project,
            financialModel: $financialModel,
            id: 12,
            createdAt: '2026-05-01 11:00:00',
        );
        $processingExport = $this->createExcelExport(
            project: $project,
            financialModel: $financialModel,
            id: 13,
            createdAt: '2026-05-01 09:00:00',
        );
        $processingExport->markProcessing();
        $repository->save($newPendingExport);
        $repository->save($oldPendingExport);
        $repository->save($middlePendingExport);
        $repository->save($processingExport);
        $handler = new GetPendingExcelExportsForProcessingHandler($repository);

        $result = $handler->handle(new GetPendingExcelExportsForProcessingQuery(limit: 2));

        self::assertCount(2, $result->exports);
        self::assertSame(11, $result->exports[0]->id);
        self::assertSame(ExcelExportStatus::Pending->value, $result->exports[0]->status);
        self::assertSame($this->createCalculationResultPayload(), $result->exports[0]->calculationResultPayload);
        self::assertSame('2026-05-01T10:00:00+00:00', $result->exports[0]->createdAt);
        self::assertSame(12, $result->exports[1]->id);
        self::assertSame('2026-05-01T11:00:00+00:00', $result->exports[1]->createdAt);
    }

    public function testReturnsEmptyListWhenThereAreNoPendingExports(): void
    {
        $handler = new GetPendingExcelExportsForProcessingHandler(new InMemoryExcelExportRepository());

        $result = $handler->handle(new GetPendingExcelExportsForProcessingQuery(limit: 10));

        self::assertSame([], $result->exports);
    }

    public function testThrowsWhenPendingExportHasNoId(): void
    {
        $repository = new InMemoryExcelExportRepository();
        $project = $this->createProject();
        $financialModel = $this->createFinancialModel($project);
        $repository->save($this->createExcelExportWithoutId($project, $financialModel));
        $handler = new GetPendingExcelExportsForProcessingHandler($repository);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Excel export for processing must have id.');

        $handler->handle(new GetPendingExcelExportsForProcessingQuery(limit: 1));
    }

    private function createExcelExport(
        Project $project,
        FinancialModel $financialModel,
        int $id,
        string $createdAt,
    ): ExcelExport {
        $excelExport = $this->createExcelExportWithoutId($project, $financialModel);
        $excelExport->setCreatedAt(new \DateTime($createdAt, new \DateTimeZone('UTC')));

        $idProperty = new \ReflectionProperty(ExcelExport::class, 'id');
        $idProperty->setValue($excelExport, $id);

        return $excelExport;
    }

    private function createExcelExportWithoutId(Project $project, FinancialModel $financialModel): ExcelExport
    {
        return ExcelExport::create(
            project: $project,
            financialModel: $financialModel,
            calculationResultPayload: $this->createCalculationResultPayload(),
        );
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
            description: null,
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
            owner: $project->getOwner(),
            shortId: ShortId::fromString('mnpqrstuvw'),
            title: 'Модель',
            description: null,
            versionNumber: 1,
            timeParams: $timeParams,
        );
    }
}
