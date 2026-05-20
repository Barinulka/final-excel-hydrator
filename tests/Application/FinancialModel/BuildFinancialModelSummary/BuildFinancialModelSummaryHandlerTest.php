<?php

declare(strict_types=1);

namespace App\Tests\Application\FinancialModel\BuildFinancialModelSummary;

use App\Application\FinancialModel\BuildFinancialModelSummary\BuildFinancialModelSummaryHandler;
use App\Application\FinancialModel\BuildFinancialModelSummary\BuildFinancialModelSummaryQuery;
use App\Application\FinancialModel\BuildFinancialModelSummary\FinancialModelSummaryNotFoundException;
use App\Domain\FinancialModel\Enum\AmountDisplayFormat;
use App\Domain\FinancialModel\Enum\FinancialModelStatus;
use App\Domain\Shared\ValueObject\MonthDuration;
use App\Domain\Shared\ValueObject\ShortId;
use App\Domain\Shared\ValueObject\YearMonth;
use App\Domain\TimeParams\Calculation\TimelineCalculator;
use App\Domain\TimeParams\Enum\ForecastStep;
use App\Entity\FinancialModel;
use App\Entity\Project;
use App\Entity\TimeParams;
use App\Entity\User;
use App\Tests\Support\Application\FinancialModel\InMemoryFinancialModelRepository;
use LogicException;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

final class BuildFinancialModelSummaryHandlerTest extends TestCase
{
    public function testBuildsFinancialModelSummarySuccessfully(): void
    {
        $owner = new User();
        $project = $this->createProject($owner);
        $financialModel = $this->createFinancialModel($project);
        $financialModelRepository = new InMemoryFinancialModelRepository();
        $financialModelRepository->save($financialModel);

        $handler = new BuildFinancialModelSummaryHandler(
            financialModelRepository: $financialModelRepository,
            timelineCalculator: new TimelineCalculator(),
        );

        $result = $handler->handle($this->createQuery($owner));

        self::assertSame('23456789ab', $result->projectShortId);
        self::assertSame('Test Project', $result->projectTitle);
        self::assertSame('ab23456789', $result->financialModelShortId);
        self::assertSame('Test Project v1', $result->financialModelTitle);
        self::assertSame(FinancialModelStatus::Active->value, $result->financialModelStatus);
        self::assertFalse($result->isFinancialModelArchived);
        self::assertSame(AmountDisplayFormat::WholeRubles->value, $result->amountDisplayFormat);
        self::assertSame([], $result->warnings);

        self::assertSame('2026-04', $result->timeParams->investmentStartMonth);
        self::assertSame(6, $result->timeParams->investmentDurationMonths);
        self::assertSame(24, $result->timeParams->commercialOperationDurationMonths);
        self::assertSame(30, $result->timeParams->totalDurationMonths);
        self::assertSame(ForecastStep::Quarter->value, $result->timeParams->forecastStep);
        self::assertSame('кв.', $result->timeParams->forecastStepLabel);

        self::assertSame('2026-04-01', $result->timeline->investmentStartDate);
        self::assertSame('2026-09-30', $result->timeline->investmentEndDate);
        self::assertSame('2026-10-01', $result->timeline->commercialOperationStartDate);
        self::assertSame('2028-09-30', $result->timeline->commercialOperationEndDate);
        self::assertSame('2026-04-01', $result->timeline->modelStartDate);
        self::assertSame('2028-09-30', $result->timeline->modelEndDate);
        self::assertSame(30, $result->timeline->periodCount);
        self::assertCount(30, $result->timeline->periods);

        $firstPeriod = $result->timeline->periods[0];
        self::assertSame(1, $firstPeriod->periodNumber);
        self::assertSame('2026-04', $firstPeriod->yearMonth);
        self::assertSame('2026-04-01', $firstPeriod->periodStartDate);
        self::assertSame('2026-04-30', $firstPeriod->periodEndDate);
        self::assertTrue($firstPeriod->investmentActivity);
        self::assertFalse($firstPeriod->operatingActivity);
        self::assertFalse($firstPeriod->operatingStart);

        $firstOperatingPeriod = $result->timeline->periods[6];
        self::assertSame(7, $firstOperatingPeriod->periodNumber);
        self::assertSame('2026-10', $firstOperatingPeriod->yearMonth);
        self::assertFalse($firstOperatingPeriod->investmentActivity);
        self::assertTrue($firstOperatingPeriod->operatingActivity);
        self::assertTrue($firstOperatingPeriod->operatingStart);
    }

    public function testBuildsArchivedFinancialModelSummary(): void
    {
        $owner = new User();
        $project = $this->createProject($owner);
        $financialModel = $this->createFinancialModel($project);
        $financialModel->archive();

        $financialModelRepository = new InMemoryFinancialModelRepository();
        $financialModelRepository->save($financialModel);

        $handler = new BuildFinancialModelSummaryHandler(
            financialModelRepository: $financialModelRepository,
            timelineCalculator: new TimelineCalculator(),
        );

        $result = $handler->handle($this->createQuery($owner));

        self::assertSame(FinancialModelStatus::Archived->value, $result->financialModelStatus);
        self::assertTrue($result->isFinancialModelArchived);
        self::assertSame('2026-04-01', $result->timeline->investmentStartDate);
        self::assertSame(30, $result->timeline->periodCount);
    }

    public function testThrowsWhenFinancialModelDoesNotExist(): void
    {
        $handler = new BuildFinancialModelSummaryHandler(
            financialModelRepository: new InMemoryFinancialModelRepository(),
            timelineCalculator: new TimelineCalculator(),
        );

        $this->expectException(FinancialModelSummaryNotFoundException::class);

        $handler->handle($this->createQuery(new User()));
    }

    public function testThrowsWhenFinancialModelBelongsToAnotherOwner(): void
    {
        $modelOwner = new User();
        $commandOwner = new User();
        $project = $this->createProject($modelOwner);
        $financialModelRepository = new InMemoryFinancialModelRepository();
        $financialModelRepository->save($this->createFinancialModel($project));

        $handler = new BuildFinancialModelSummaryHandler(
            financialModelRepository: $financialModelRepository,
            timelineCalculator: new TimelineCalculator(),
        );

        $this->expectException(FinancialModelSummaryNotFoundException::class);

        $handler->handle($this->createQuery($commandOwner));
    }

    public function testThrowsWhenProjectShortIdIsWrong(): void
    {
        $owner = new User();
        $project = $this->createProject($owner);
        $financialModelRepository = new InMemoryFinancialModelRepository();
        $financialModelRepository->save($this->createFinancialModel($project));

        $handler = new BuildFinancialModelSummaryHandler(
            financialModelRepository: $financialModelRepository,
            timelineCalculator: new TimelineCalculator(),
        );

        $this->expectException(FinancialModelSummaryNotFoundException::class);

        $handler->handle($this->createQuery(
            owner: $owner,
            projectShortId: 'cdefghjkmn',
        ));
    }

    public function testThrowsWhenFinancialModelHasNoTimeParams(): void
    {
        $owner = new User();
        $project = $this->createProject($owner);
        $financialModel = $this->createFinancialModel($project);
        $this->removeTimeParams($financialModel);

        $financialModelRepository = new InMemoryFinancialModelRepository();
        $financialModelRepository->save($financialModel);

        $handler = new BuildFinancialModelSummaryHandler(
            financialModelRepository: $financialModelRepository,
            timelineCalculator: new TimelineCalculator(),
        );

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Финансовая модель не содержит обязательный блок временных параметров.');

        $handler->handle($this->createQuery($owner));
    }

    private function createQuery(
        User $owner,
        string $projectShortId = '23456789ab',
        string $financialModelShortId = 'ab23456789',
    ): BuildFinancialModelSummaryQuery {
        return new BuildFinancialModelSummaryQuery(
            owner: $owner,
            projectShortId: ShortId::fromString($projectShortId),
            financialModelShortId: ShortId::fromString($financialModelShortId),
        );
    }

    private function createProject(User $owner): Project
    {
        return Project::create(
            owner: $owner,
            shortId: ShortId::fromString('23456789ab'),
            title: 'Test Project',
            description: null,
        );
    }

    private function createFinancialModel(Project $project): FinancialModel
    {
        return FinancialModel::create(
            project: $project,
            owner: $project->getOwner(),
            shortId: ShortId::fromString('ab23456789'),
            title: 'Test Project v1',
            description: null,
            versionNumber: 1,
            timeParams: TimeParams::create(
                investmentStartMonth: YearMonth::fromString('2026-04'),
                investmentDuration: MonthDuration::fromInt(6),
                commercialOperationDuration: MonthDuration::fromInt(24),
                forecastStep: ForecastStep::Quarter,
            ),
        );
    }

    private function removeTimeParams(FinancialModel $financialModel): void
    {
        $timeParamsProperty = new ReflectionProperty(FinancialModel::class, 'timeParams');
        $timeParamsProperty->setValue($financialModel, null);
    }
}
