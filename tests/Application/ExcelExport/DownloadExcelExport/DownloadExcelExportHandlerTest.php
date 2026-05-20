<?php

declare(strict_types=1);

namespace App\Tests\Application\ExcelExport\DownloadExcelExport;

use App\Application\ExcelExport\DownloadExcelExport\DownloadExcelExportHandler;
use App\Application\ExcelExport\DownloadExcelExport\DownloadExcelExportQuery;
use App\Application\ExcelExport\DownloadExcelExport\ExcelExportFileNotFoundException;
use App\Application\ExcelExport\DownloadExcelExport\ExcelExportFileNotReadyException;
use App\Application\ExcelExport\DownloadExcelExport\ExcelExportForDownloadNotFoundException;
use App\Application\ExcelExport\Storage\LocalExcelExportStorage;
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

final class DownloadExcelExportHandlerTest extends TestCase
{
    public function testReturnsFileForCompletedExcelExport(): void
    {
        $owner = $this->createUser();
        $project = $this->createProject($owner);
        $financialModel = $this->createFinancialModel($project);
        $financialModelRepository = new InMemoryFinancialModelRepository();
        $financialModelRepository->save($financialModel);
        $storageRoot = $this->createStorageRoot();
        $absoluteFilePath = $storageRoot . '/excel-exports/excel-export-15.xlsx';
        mkdir(dirname($absoluteFilePath), recursive: true);
        file_put_contents($absoluteFilePath, 'xlsx');
        $excelExport = $this->createCompletedExcelExport(
            project: $project,
            financialModel: $financialModel,
            id: 15,
            filePath: 'excel-exports/excel-export-15.xlsx',
        );
        $excelExportRepository = new InMemoryExcelExportRepository();
        $excelExportRepository->save($excelExport);
        $handler = $this->createHandler(
            financialModelRepository: $financialModelRepository,
            excelExportRepository: $excelExportRepository,
            storageRoot: $storageRoot,
        );

        $result = $handler->handle($this->createQuery($owner, exportId: 15));

        self::assertSame(realpath($absoluteFilePath), $result->absolutePath);
        self::assertSame('financial-model-mnpqrstuvw-export-15.xlsx', $result->downloadName);
    }

    public function testThrowsWhenFinancialModelDoesNotBelongToOwner(): void
    {
        $modelOwner = $this->createUser();
        $queryOwner = $this->createUser(email: 'another-owner@example.com');
        $project = $this->createProject($modelOwner);
        $financialModelRepository = new InMemoryFinancialModelRepository();
        $financialModelRepository->save($this->createFinancialModel($project));
        $handler = $this->createHandler(
            financialModelRepository: $financialModelRepository,
            excelExportRepository: new InMemoryExcelExportRepository(),
            storageRoot: $this->createStorageRoot(),
        );

        $this->expectException(ExcelExportForDownloadNotFoundException::class);

        $handler->handle($this->createQuery($queryOwner, exportId: 15));
    }

    public function testThrowsWhenExcelExportBelongsToAnotherModel(): void
    {
        $owner = $this->createUser();
        $project = $this->createProject($owner);
        $financialModel = $this->createFinancialModel($project);
        $otherFinancialModel = $this->createFinancialModel($project, shortId: 'zyxwvutsrq', versionNumber: 2);
        $financialModelRepository = new InMemoryFinancialModelRepository();
        $financialModelRepository->save($financialModel);
        $financialModelRepository->save($otherFinancialModel);
        $excelExportRepository = new InMemoryExcelExportRepository();
        $excelExportRepository->save($this->createCompletedExcelExport(
            project: $project,
            financialModel: $otherFinancialModel,
            id: 15,
            filePath: 'excel-exports/excel-export-15.xlsx',
        ));
        $handler = $this->createHandler(
            financialModelRepository: $financialModelRepository,
            excelExportRepository: $excelExportRepository,
            storageRoot: $this->createStorageRoot(),
        );

        $this->expectException(ExcelExportForDownloadNotFoundException::class);

        $handler->handle($this->createQuery($owner, exportId: 15));
    }

    public function testThrowsWhenExcelExportIsNotCompleted(): void
    {
        $owner = $this->createUser();
        $project = $this->createProject($owner);
        $financialModel = $this->createFinancialModel($project);
        $financialModelRepository = new InMemoryFinancialModelRepository();
        $financialModelRepository->save($financialModel);
        $excelExportRepository = new InMemoryExcelExportRepository();
        $excelExportRepository->save($this->createExcelExport(
            project: $project,
            financialModel: $financialModel,
            id: 15,
        ));
        $handler = $this->createHandler(
            financialModelRepository: $financialModelRepository,
            excelExportRepository: $excelExportRepository,
            storageRoot: $this->createStorageRoot(),
        );

        $this->expectException(ExcelExportFileNotReadyException::class);

        $handler->handle($this->createQuery($owner, exportId: 15));
    }

    public function testThrowsWhenCompletedFileDoesNotExist(): void
    {
        $owner = $this->createUser();
        $project = $this->createProject($owner);
        $financialModel = $this->createFinancialModel($project);
        $financialModelRepository = new InMemoryFinancialModelRepository();
        $financialModelRepository->save($financialModel);
        $excelExportRepository = new InMemoryExcelExportRepository();
        $excelExportRepository->save($this->createCompletedExcelExport(
            project: $project,
            financialModel: $financialModel,
            id: 15,
            filePath: 'excel-exports/missing.xlsx',
        ));
        $handler = $this->createHandler(
            financialModelRepository: $financialModelRepository,
            excelExportRepository: $excelExportRepository,
            storageRoot: $this->createStorageRoot(),
        );

        $this->expectException(ExcelExportFileNotFoundException::class);

        $handler->handle($this->createQuery($owner, exportId: 15));
    }

    private function createHandler(
        InMemoryFinancialModelRepository $financialModelRepository,
        InMemoryExcelExportRepository $excelExportRepository,
        string $storageRoot,
    ): DownloadExcelExportHandler {
        return new DownloadExcelExportHandler(
            financialModelRepository: $financialModelRepository,
            excelExportRepository: $excelExportRepository,
            excelExportStorage: new LocalExcelExportStorage($storageRoot),
        );
    }

    private function createQuery(User $owner, int $exportId): DownloadExcelExportQuery
    {
        return new DownloadExcelExportQuery(
            owner: $owner,
            projectShortId: ShortId::fromString('abcdefghjk'),
            financialModelShortId: ShortId::fromString('mnpqrstuvw'),
            exportId: $exportId,
        );
    }

    private function createCompletedExcelExport(
        Project $project,
        FinancialModel $financialModel,
        int $id,
        string $filePath,
    ): ExcelExport {
        $excelExport = $this->createExcelExport($project, $financialModel, $id);
        $excelExport->markProcessing();
        $excelExport->markCompleted($filePath);

        return $excelExport;
    }

    private function createExcelExport(Project $project, FinancialModel $financialModel, int $id): ExcelExport
    {
        $excelExport = ExcelExport::create(
            project: $project,
            financialModel: $financialModel,
            calculationResultPayload: [
                'tables' => [],
                'metrics' => [],
                'warnings' => [],
            ],
        );

        $idProperty = new \ReflectionProperty(ExcelExport::class, 'id');
        $idProperty->setValue($excelExport, $id);

        return $excelExport;
    }

    private function createProject(User $owner): Project
    {
        return Project::create(
            owner: $owner,
            shortId: ShortId::fromString('abcdefghjk'),
            title: 'Проект',
            description: null,
        );
    }

    private function createFinancialModel(
        Project $project,
        string $shortId = 'mnpqrstuvw',
        int $versionNumber = 1,
    ): FinancialModel {
        $timeParams = TimeParams::create(
            investmentStartMonth: YearMonth::fromString('2026-05'),
            investmentDuration: MonthDuration::fromInt(6),
            commercialOperationDuration: MonthDuration::fromInt(24),
            forecastStep: ForecastStep::Month,
        );

        return FinancialModel::create(
            project: $project,
            owner: $project->getOwner(),
            shortId: ShortId::fromString($shortId),
            title: 'Модель',
            description: null,
            versionNumber: $versionNumber,
            timeParams: $timeParams,
        );
    }

    private function createUser(string $email = 'owner@example.com'): User
    {
        return (new User())
            ->setEmail($email)
            ->setPassword('hashed-password');
    }

    private function createStorageRoot(): string
    {
        $storageRoot = sys_get_temp_dir() . '/excel-export-download-test-' . bin2hex(random_bytes(8));
        mkdir($storageRoot, recursive: true);

        return $storageRoot;
    }
}
