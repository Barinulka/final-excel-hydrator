<?php

declare(strict_types=1);

namespace App\Tests\Application\Calculation\BuildFinancialModelCalculation;

use App\Application\Calculation\BuildFinancialModelCalculation\BuildFinancialModelCalculationHandler;
use App\Application\Calculation\BuildFinancialModelCalculation\BuildFinancialModelCalculationQuery;
use App\Application\Calculation\BuildFinancialModelCalculation\FinancialModelForCalculationNotFoundException;
use App\Domain\Shared\ValueObject\MonthDuration;
use App\Domain\Shared\ValueObject\ShortId;
use App\Domain\Shared\ValueObject\YearMonth;
use App\Domain\TimeParams\Calculation\TimelineCalculator;
use App\Domain\TimeParams\Enum\ForecastStep;
use App\Entity\FinancialModel;
use App\Entity\TimeParams;
use App\Entity\User;
use App\Tests\Support\Application\FinancialModel\InMemoryFinancialModelRepository;
use PHPUnit\Framework\TestCase;

final class BuildFinancialModelCalculationHandlerTest extends TestCase
{
    public function testBuildsCalculationResultFromFinancialModelTimeParams(): void
    {
        $owner = new User();
        $financialModel = $this->createFinancialModel($owner);
        $repository = new InMemoryFinancialModelRepository();
        $repository->save($financialModel);

        $handler = new BuildFinancialModelCalculationHandler(
            financialModelRepository: $repository,
            timelineCalculator: new TimelineCalculator(),
        );

        $result = $handler->handle($this->createQuery($owner));

        self::assertCount(1, $result->tables);
        self::assertSame([], $result->warnings);
        self::assertSame(5, $result->metrics['period_count']);

        $timelineTable = $result->tables[0];

        self::assertSame('timeline', $timelineTable->code);
        self::assertSame('Временная шкала', $timelineTable->title);
        self::assertSame(['2026-01', '2026-02', '2026-03', '2026-04', '2026-05'], $timelineTable->periods);
        self::assertCount(5, $timelineTable->rows);

        self::assertSame('period_start_date', $timelineTable->rows[0]->code);
        self::assertSame(
            ['2026-01-01', '2026-02-01', '2026-03-01', '2026-04-01', '2026-05-01'],
            $timelineTable->rows[0]->values,
        );

        self::assertSame('period_end_date', $timelineTable->rows[1]->code);
        self::assertSame(
            ['2026-01-31', '2026-02-28', '2026-03-31', '2026-04-30', '2026-05-31'],
            $timelineTable->rows[1]->values,
        );

        self::assertSame('investment_activity', $timelineTable->rows[2]->code);
        self::assertSame([1, 1, 0, 0, 0], $timelineTable->rows[2]->values);

        self::assertSame('operating_activity', $timelineTable->rows[3]->code);
        self::assertSame([0, 0, 1, 1, 1], $timelineTable->rows[3]->values);

        self::assertSame('operating_start', $timelineTable->rows[4]->code);
        self::assertSame([0, 0, 1, 0, 0], $timelineTable->rows[4]->values);
    }

    public function testThrowsWhenFinancialModelDoesNotExist(): void
    {
        $handler = new BuildFinancialModelCalculationHandler(
            financialModelRepository: new InMemoryFinancialModelRepository(),
            timelineCalculator: new TimelineCalculator(),
        );

        $this->expectException(FinancialModelForCalculationNotFoundException::class);

        $handler->handle($this->createQuery(new User()));
    }

    public function testThrowsWhenFinancialModelBelongsToAnotherOwner(): void
    {
        $modelOwner = new User();
        $queryOwner = new User();
        $repository = new InMemoryFinancialModelRepository();
        $repository->save($this->createFinancialModel($modelOwner));

        $handler = new BuildFinancialModelCalculationHandler(
            financialModelRepository: $repository,
            timelineCalculator: new TimelineCalculator(),
        );

        $this->expectException(FinancialModelForCalculationNotFoundException::class);

        $handler->handle($this->createQuery($queryOwner));
    }

    public function testBuildsCalculationResultWithoutProject(): void
    {
        $owner = new User();
        $financialModel = $this->createFinancialModelWithoutProject($owner);
        $repository = new InMemoryFinancialModelRepository();
        $repository->save($financialModel);

        $handler = new BuildFinancialModelCalculationHandler(
            financialModelRepository: $repository,
            timelineCalculator: new TimelineCalculator(),
        );

        $result = $handler->handle($this->createQuery($owner));

        self::assertCount(1, $result->tables);
        self::assertSame([], $result->warnings);
        self::assertSame(5, $result->metrics['period_count']);
        self::assertSame('timeline', $result->tables[0]->code);
    }

    private function createQuery(
        User $owner,
        string $financialModelShortId = 'ab23456789',
    ): BuildFinancialModelCalculationQuery {
        return new BuildFinancialModelCalculationQuery(
            owner: $owner,
            financialModelShortId: ShortId::fromString($financialModelShortId),
        );
    }

    private function createFinancialModel(User $owner): FinancialModel
    {
        $timeParams = TimeParams::create(
            investmentStartMonth: YearMonth::fromString('2026-01'),
            investmentDuration: MonthDuration::fromInt(2),
            commercialOperationDuration: MonthDuration::fromInt(3),
            forecastStep: ForecastStep::Month,
        );

        return FinancialModel::create(
            shortId: ShortId::fromString('ab23456789'),
            owner: $owner,
            title: 'Test Project v1',
            description: null,
            versionNumber: 1,
            timeParams: $timeParams,
        );
    }

    private function createFinancialModelWithoutProject(User $owner): FinancialModel
    {
        return FinancialModel::create(
            shortId: ShortId::fromString('ab23456789'),
            owner: $owner,
            title: 'Test Model',
            description: null,
            versionNumber: 1,
            timeParams: TimeParams::create(
                investmentStartMonth: YearMonth::fromString('2026-01'),
                investmentDuration: MonthDuration::fromInt(2),
                commercialOperationDuration: MonthDuration::fromInt(3),
                forecastStep: ForecastStep::Month,
            ),
        );
    }
}
