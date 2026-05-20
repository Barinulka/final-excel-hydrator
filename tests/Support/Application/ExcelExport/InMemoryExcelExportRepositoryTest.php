<?php

declare(strict_types=1);

namespace App\Tests\Support\Application\ExcelExport;

use App\Domain\Shared\ValueObject\MonthDuration;
use App\Domain\Shared\ValueObject\ShortId;
use App\Domain\Shared\ValueObject\YearMonth;
use App\Domain\TimeParams\Enum\ForecastStep;
use App\Entity\ExcelExport;
use App\Entity\FinancialModel;
use App\Entity\Project;
use App\Entity\TimeParams;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

final class InMemoryExcelExportRepositoryTest extends TestCase
{
    public function testFindsExcelExportById(): void
    {
        $repository = new InMemoryExcelExportRepository();
        $project = $this->createProject();
        $financialModel = $this->createFinancialModel($project);
        $firstExport = $this->createExcelExport($project, $financialModel, id: 10);
        $secondExport = $this->createExcelExport($project, $financialModel, id: 15);
        $repository->save($firstExport);
        $repository->save($secondExport);

        self::assertSame($secondExport, $repository->findById(15));
        self::assertNull($repository->findById(999));
    }

    public function testFindsPendingExportsForProcessingFromOldestToNewestWithLimit(): void
    {
        $repository = new InMemoryExcelExportRepository();
        $project = $this->createProject();
        $financialModel = $this->createFinancialModel($project);
        $newPendingExport = $this->createExcelExport(
            project: $project,
            financialModel: $financialModel,
            id: 1,
            createdAt: '2026-05-01 12:00:00',
        );
        $oldPendingExport = $this->createExcelExport(
            project: $project,
            financialModel: $financialModel,
            id: 2,
            createdAt: '2026-05-01 10:00:00',
        );
        $processingExport = $this->createExcelExport(
            project: $project,
            financialModel: $financialModel,
            id: 3,
            createdAt: '2026-05-01 09:00:00',
        );
        $middlePendingExport = $this->createExcelExport(
            project: $project,
            financialModel: $financialModel,
            id: 4,
            createdAt: '2026-05-01 11:00:00',
        );
        $processingExport->markProcessing();
        $repository->save($newPendingExport);
        $repository->save($oldPendingExport);
        $repository->save($processingExport);
        $repository->save($middlePendingExport);

        $exports = $repository->findPendingForProcessing(limit: 2);

        self::assertSame([$oldPendingExport, $middlePendingExport], $exports);
    }

    public function testReturnsEmptyPendingListWhenLimitIsZero(): void
    {
        $repository = new InMemoryExcelExportRepository();
        $project = $this->createProject();
        $financialModel = $this->createFinancialModel($project);
        $repository->save($this->createExcelExport($project, $financialModel, id: 10));

        self::assertSame([], $repository->findPendingForProcessing(limit: 0));
    }

    private function createExcelExport(
        Project $project,
        FinancialModel $financialModel,
        int $id,
        string $createdAt = '2026-05-01 10:00:00',
    ): ExcelExport {
        $excelExport = ExcelExport::create(
            project: $project,
            financialModel: $financialModel,
            calculationResultPayload: $this->createCalculationResultPayload(),
        );
        $excelExport->setCreatedAt(new \DateTime($createdAt, new \DateTimeZone('UTC')));

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
