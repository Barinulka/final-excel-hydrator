<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Domain\ExcelExport\Enum\ExcelExportStatus;
use App\Domain\Shared\ValueObject\MonthDuration;
use App\Domain\Shared\ValueObject\ShortId;
use App\Domain\Shared\ValueObject\YearMonth;
use App\Domain\TimeParams\Enum\ForecastStep;
use App\Entity\ExcelExport;
use App\Entity\FinancialModel;
use App\Entity\TimeParams;
use App\Entity\User;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ExcelExportTest extends TestCase
{
    public function testCreatesPendingExcelExport(): void
    {
        $project = $this->createProject();
        $financialModel = $this->createFinancialModel($project);

        $calculationResultPayload = $this->createCalculationResultPayload();

        $excelExport = ExcelExport::create(
            financialModel: $financialModel,
            calculationResultPayload: $calculationResultPayload,
        );

        self::assertSame($financialModel, $excelExport->getFinancialModel());
        self::assertSame(ExcelExportStatus::Pending, $excelExport->getStatus());
        self::assertNull($excelExport->getFilePath());
        self::assertNull($excelExport->getErrorMessage());
        self::assertNull($excelExport->getStartedAt());
        self::assertNull($excelExport->getCompletedAt());
        self::assertNull($excelExport->getFailedAt());
        self::assertSame($calculationResultPayload, $excelExport->getCalculationResultPayload());
        self::assertFalse($excelExport->isCompleted());
    }

    public function testRejectsEmptyCalculationResultPayload(): void
    {
        $project = $this->createProject();
        $financialModel = $this->createFinancialModel($project);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Нельзя создать Excel export без CalculationResult payload.');

        ExcelExport::create(
            financialModel: $financialModel,
            calculationResultPayload: [],
        );
    }

    public function testMarksProcessing(): void
    {
        $excelExport = $this->createExcelExport();

        $result = $excelExport->markProcessing();

        self::assertSame($excelExport, $result);
        self::assertSame(ExcelExportStatus::Processing, $excelExport->getStatus());
        self::assertInstanceOf(\DateTimeImmutable::class, $excelExport->getStartedAt());
        self::assertNull($excelExport->getCompletedAt());
        self::assertNull($excelExport->getFailedAt());
        self::assertFalse($excelExport->isCompleted());
    }

    public function testCannotMarkProcessingTwice(): void
    {
        $excelExport = $this->createExcelExport();
        $excelExport->markProcessing();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('В обработку можно взять только pending Excel export.');

        $excelExport->markProcessing();
    }

    public function testMarksCompleted(): void
    {
        $excelExport = $this->createExcelExport();
        $excelExport->markProcessing();

        $result = $excelExport->markCompleted('  exports/model.xlsx  ');

        self::assertSame($excelExport, $result);
        self::assertSame(ExcelExportStatus::Completed, $excelExport->getStatus());
        self::assertSame('exports/model.xlsx', $excelExport->getFilePath());
        self::assertNull($excelExport->getErrorMessage());
        self::assertInstanceOf(\DateTimeImmutable::class, $excelExport->getCompletedAt());
        self::assertTrue($excelExport->isCompleted());
    }

    public function testCannotMarkCompletedWhenPending(): void
    {
        $excelExport = $this->createExcelExport();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Завершить можно только processing Excel export.');

        $excelExport->markCompleted('exports/model.xlsx');
    }

    public function testCannotMarkCompletedWithEmptyFilePath(): void
    {
        $excelExport = $this->createExcelExport();
        $excelExport->markProcessing();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Нельзя отметить Excel export завершенным без filePath.');

        $excelExport->markCompleted('   ');
    }

    public function testMarksFailed(): void
    {
        $excelExport = $this->createExcelExport();
        $excelExport->markProcessing();

        $result = $excelExport->markFailed('  Go worker timeout  ');

        self::assertSame($excelExport, $result);
        self::assertSame(ExcelExportStatus::Failed, $excelExport->getStatus());
        self::assertSame('Go worker timeout', $excelExport->getErrorMessage());
        self::assertNull($excelExport->getFilePath());
        self::assertInstanceOf(\DateTimeImmutable::class, $excelExport->getFailedAt());
        self::assertFalse($excelExport->isCompleted());
    }

    public function testCannotMarkFailedWhenPending(): void
    {
        $excelExport = $this->createExcelExport();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Ошибкой можно завершить только processing Excel export.');

        $excelExport->markFailed('Go worker timeout');
    }

    public function testCannotMarkFailedWithEmptyErrorMessage(): void
    {
        $excelExport = $this->createExcelExport();
        $excelExport->markProcessing();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Нельзя отметить Excel export ошибочным без errorMessage.');

        $excelExport->markFailed('   ');
    }

    private function createExcelExport(): ExcelExport
    {
        $project = $this->createProject();

        return ExcelExport::create(
            financialModel: $this->createFinancialModel($project),
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
                    'periods' => ['2026-04'],
                    'rows' => [],
                ],
            ],
            'metrics' => [
                'period_count' => 1,
            ],
            'warnings' => [],
        ];
    }

    private function createProject(string $shortId = 'abcdefghjk'): User
    {
        $user = (new User())
            ->setEmail(sprintf('owner-%s@example.com', $shortId))
            ->setPassword('hashed-password');
        return $user;
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
            shortId: ShortId::fromString('mnpqrstuvw'),
            title: 'Модель',
            description: null,
            versionNumber: 1,
            timeParams: $timeParams,
        );
    }
}
