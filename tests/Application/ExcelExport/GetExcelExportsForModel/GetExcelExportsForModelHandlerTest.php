<?php

declare(strict_types=1);

namespace App\Tests\Application\ExcelExport\GetExcelExportsForModel;

use App\Application\ExcelExport\GetExcelExportsForModel\FinancialModelForExcelExportsNotFoundException;
use App\Application\ExcelExport\GetExcelExportsForModel\GetExcelExportsForModelHandler;
use App\Application\ExcelExport\GetExcelExportsForModel\GetExcelExportsForModelQuery;
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
use App\Tests\Support\Application\FinancialModel\InMemoryFinancialModelRepository;
use PHPUnit\Framework\TestCase;

final class GetExcelExportsForModelHandlerTest extends TestCase
{
    public function testReturnsLatestExcelExportsForFinancialModel(): void
    {
        $owner = $this->createUser();
        $project = $this->createProject($owner);
        $financialModel = $this->createFinancialModel($project);
        $otherFinancialModel = $this->createFinancialModel(
            project: $project,
            shortId: 'bc23456789',
            versionNumber: 2,
        );
        $financialModelRepository = new InMemoryFinancialModelRepository();
        $financialModelRepository->save($financialModel);
        $financialModelRepository->save($otherFinancialModel);
        $excelExportRepository = new InMemoryExcelExportRepository();
        $oldExport = $this->createExcelExport($project, $financialModel, '2026-04-20 10:00:00');
        $completedExport = $this->createExcelExport($project, $financialModel, '2026-04-21 10:00:00');
        $completedExport->markProcessing();
        $completedExport->markCompleted('/exports/model.xlsx');
        $otherModelExport = $this->createExcelExport($project, $otherFinancialModel, '2026-04-22 10:00:00');
        $excelExportRepository->save($oldExport);
        $excelExportRepository->save($completedExport);
        $excelExportRepository->save($otherModelExport);
        $handler = new GetExcelExportsForModelHandler(
            financialModelRepository: $financialModelRepository,
            excelExportRepository: $excelExportRepository,
        );

        $result = $handler->handle($this->createQuery($owner));

        self::assertCount(2, $result->exports);
        self::assertSame(ExcelExportStatus::Completed->value, $result->exports[0]->status);
        self::assertSame('/exports/model.xlsx', $result->exports[0]->filePath);
        self::assertSame('2026-04-21T10:00:00+00:00', $result->exports[0]->createdAt);
        self::assertSame(ExcelExportStatus::Pending->value, $result->exports[1]->status);
        self::assertNull($result->exports[1]->filePath);
        self::assertSame('2026-04-20T10:00:00+00:00', $result->exports[1]->createdAt);
    }

    public function testReturnsEmptyListWhenFinancialModelHasNoExports(): void
    {
        $owner = $this->createUser();
        $project = $this->createProject($owner);
        $financialModelRepository = new InMemoryFinancialModelRepository();
        $financialModelRepository->save($this->createFinancialModel($project));
        $handler = new GetExcelExportsForModelHandler(
            financialModelRepository: $financialModelRepository,
            excelExportRepository: new InMemoryExcelExportRepository(),
        );

        $result = $handler->handle($this->createQuery($owner));

        self::assertSame([], $result->exports);
    }

    public function testThrowsWhenFinancialModelDoesNotExist(): void
    {
        $handler = new GetExcelExportsForModelHandler(
            financialModelRepository: new InMemoryFinancialModelRepository(),
            excelExportRepository: new InMemoryExcelExportRepository(),
        );

        $this->expectException(FinancialModelForExcelExportsNotFoundException::class);

        $handler->handle($this->createQuery($this->createUser()));
    }

    public function testThrowsWhenFinancialModelBelongsToAnotherOwner(): void
    {
        $modelOwner = $this->createUser();
        $queryOwner = $this->createUser(email: 'another-owner@example.com');
        $financialModelRepository = new InMemoryFinancialModelRepository();
        $financialModelRepository->save($this->createFinancialModel($this->createProject($modelOwner)));
        $handler = new GetExcelExportsForModelHandler(
            financialModelRepository: $financialModelRepository,
            excelExportRepository: new InMemoryExcelExportRepository(),
        );

        $this->expectException(FinancialModelForExcelExportsNotFoundException::class);

        $handler->handle($this->createQuery($queryOwner));
    }

    private function createQuery(
        User $owner,
        string $projectShortId = '23456789ab',
        string $financialModelShortId = 'ab23456789',
    ): GetExcelExportsForModelQuery {
        return new GetExcelExportsForModelQuery(
            owner: $owner,
            projectShortId: ShortId::fromString($projectShortId),
            financialModelShortId: ShortId::fromString($financialModelShortId),
        );
    }

    private function createUser(string $email = 'owner@example.com'): User
    {
        return (new User())
            ->setEmail($email)
            ->setPassword('hashed-password');
    }

    private function createProject(User $owner): Project
    {
        return Project::create(
            owner: $owner,
            shortId: ShortId::fromString('23456789ab'),
            title: 'Test Project',
        );
    }

    private function createFinancialModel(
        Project $project,
        string $shortId = 'ab23456789',
        int $versionNumber = 1,
    ): FinancialModel {
        $timeParams = TimeParams::create(
            investmentStartMonth: YearMonth::fromString('2026-04'),
            investmentDuration: MonthDuration::fromInt(6),
            commercialOperationDuration: MonthDuration::fromInt(24),
            forecastStep: ForecastStep::Month,
        );

        return FinancialModel::create(
            project: $project,
            shortId: ShortId::fromString($shortId),
            title: sprintf('Test Project v%d', $versionNumber),
            versionNumber: $versionNumber,
            timeParams: $timeParams,
        );
    }

    private function createExcelExport(
        Project $project,
        FinancialModel $financialModel,
        string $createdAt,
    ): ExcelExport {
        return ExcelExport::create($project, $financialModel)
            ->setCreatedAt(new \DateTime($createdAt, new \DateTimeZone('UTC')));
    }
}
