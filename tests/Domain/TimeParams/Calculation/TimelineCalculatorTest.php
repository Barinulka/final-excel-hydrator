<?php

declare(strict_types=1);

namespace App\Tests\Domain\TimeParams\Calculation;

use App\Domain\Shared\ValueObject\MonthDuration;
use App\Domain\Shared\ValueObject\YearMonth;
use App\Domain\TimeParams\Calculation\Timeline;
use App\Domain\TimeParams\Calculation\TimelineCalculator;
use App\Domain\TimeParams\Enum\ForecastStep;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class TimelineCalculatorTest extends TestCase
{
    public function testBuildsMonthlyTimelineFromTimeParams(): void
    {
        $timeline = $this->createTimeline();

        $this->assertDate('2026-04-01', $timeline->getInvestmentStartDate());
        $this->assertDate('2026-09-30', $timeline->getInvestmentEndDate());
        $this->assertDate('2026-10-01', $timeline->getCommercialOperationStartDate());
        $this->assertDate('2028-09-30', $timeline->getCommercialOperationEndDate());
        $this->assertDate('2026-04-01', $timeline->getModelStartDate());
        $this->assertDate('2028-09-30', $timeline->getModelEndDate());
        self::assertSame(30, $timeline->getPeriodCount());
        self::assertSame(ForecastStep::Quarter, $timeline->getForecastStep());
    }

    public function testBuildsContinuousMonthlyPeriods(): void
    {
        $periods = $this->createTimeline()->getPeriods();

        self::assertSame(1, $periods[0]->getPeriodNumber());
        self::assertSame('2026-04', $periods[0]->getYearMonth()->toString());
        $this->assertDate('2026-04-01', $periods[0]->getPeriodStartDate());
        $this->assertDate('2026-04-30', $periods[0]->getPeriodEndDate());

        self::assertSame(2, $periods[1]->getPeriodNumber());
        self::assertSame('2026-05', $periods[1]->getYearMonth()->toString());
        $this->assertDate('2026-05-01', $periods[1]->getPeriodStartDate());
        $this->assertDate('2026-05-31', $periods[1]->getPeriodEndDate());

        self::assertSame(30, $periods[29]->getPeriodNumber());
        self::assertSame('2028-09', $periods[29]->getYearMonth()->toString());
        $this->assertDate('2028-09-01', $periods[29]->getPeriodStartDate());
        $this->assertDate('2028-09-30', $periods[29]->getPeriodEndDate());
    }

    public function testBuildsActivityFlags(): void
    {
        $periods = $this->createTimeline()->getPeriods();

        $firstPeriod = $periods[0];
        self::assertTrue($firstPeriod->isInvestmentActivity());
        self::assertFalse($firstPeriod->isOperatingActivity());
        self::assertFalse($firstPeriod->isOperatingStart());

        $lastInvestmentPeriod = $periods[5];
        self::assertTrue($lastInvestmentPeriod->isInvestmentActivity());
        self::assertFalse($lastInvestmentPeriod->isOperatingActivity());
        self::assertFalse($lastInvestmentPeriod->isOperatingStart());

        $firstOperatingPeriod = $periods[6];
        self::assertFalse($firstOperatingPeriod->isInvestmentActivity());
        self::assertTrue($firstOperatingPeriod->isOperatingActivity());
        self::assertTrue($firstOperatingPeriod->isOperatingStart());

        $secondOperatingPeriod = $periods[7];
        self::assertFalse($secondOperatingPeriod->isInvestmentActivity());
        self::assertTrue($secondOperatingPeriod->isOperatingActivity());
        self::assertFalse($secondOperatingPeriod->isOperatingStart());
    }

    public function testUsesOneMonthDurationsWithoutAddingExtraMonths(): void
    {
        $timeline = (new TimelineCalculator())->calculate(
            investmentStartMonth: YearMonth::fromString('2026-04'),
            investmentDuration: MonthDuration::fromInt(1),
            commercialOperationDuration: MonthDuration::fromInt(1),
            forecastStep: ForecastStep::Month,
        );

        $this->assertDate('2026-04-01', $timeline->getInvestmentStartDate());
        $this->assertDate('2026-04-30', $timeline->getInvestmentEndDate());
        $this->assertDate('2026-05-01', $timeline->getCommercialOperationStartDate());
        $this->assertDate('2026-05-31', $timeline->getCommercialOperationEndDate());
        self::assertSame(2, $timeline->getPeriodCount());

        $periods = $timeline->getPeriods();

        self::assertTrue($periods[0]->isInvestmentActivity());
        self::assertFalse($periods[0]->isOperatingActivity());
        self::assertFalse($periods[0]->isOperatingStart());

        self::assertFalse($periods[1]->isInvestmentActivity());
        self::assertTrue($periods[1]->isOperatingActivity());
        self::assertTrue($periods[1]->isOperatingStart());
    }

    public function testForecastStepDoesNotAggregatePeriodsYet(): void
    {
        foreach (ForecastStep::cases() as $forecastStep) {
            $timeline = (new TimelineCalculator())->calculate(
                investmentStartMonth: YearMonth::fromString('2026-04'),
                investmentDuration: MonthDuration::fromInt(2),
                commercialOperationDuration: MonthDuration::fromInt(2),
                forecastStep: $forecastStep,
            );

            self::assertSame($forecastStep, $timeline->getForecastStep());
            self::assertSame(4, $timeline->getPeriodCount());
        }
    }

    private function createTimeline(): Timeline
    {
        return (new TimelineCalculator())->calculate(
            investmentStartMonth: YearMonth::fromString('2026-04'),
            investmentDuration: MonthDuration::fromInt(6),
            commercialOperationDuration: MonthDuration::fromInt(24),
            forecastStep: ForecastStep::Quarter,
        );
    }

    private function assertDate(string $expectedDate, DateTimeImmutable $actualDate): void
    {
        self::assertSame($expectedDate, $actualDate->format('Y-m-d'));
    }
}
