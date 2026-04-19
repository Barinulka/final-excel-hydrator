<?php

declare(strict_types=1);

namespace App\Domain\TimeParams\Calculation;

use App\Domain\Shared\ValueObject\MonthDuration;
use App\Domain\Shared\ValueObject\YearMonth;
use App\Domain\TimeParams\Enum\ForecastStep;

final readonly class TimelineCalculator
{
    public function calculate(
        YearMonth $investmentStartMonth,
        MonthDuration $investmentDuration,
        MonthDuration $commercialOperationDuration,
        ForecastStep $forecastStep,
    ): Timeline {
        $investmentDurationMonths = $investmentDuration->toInt();
        $commercialOperationDurationMonths = $commercialOperationDuration->toInt();

        $investmentStartDate = $investmentStartMonth->toDate();

        $investmentEndDate = $investmentStartDate
            ->modify(sprintf('+%d months', $investmentDurationMonths - 1))
            ->modify('last day of this month');

        $commercialOperationStartDate = $investmentEndDate->modify('+1 day');

        $commercialOperationEndDate = $commercialOperationStartDate
            ->modify(sprintf('+%d months', $commercialOperationDurationMonths - 1))
            ->modify('last day of this month');

        $modelStartDate = $investmentStartDate;
        $modelEndDate = $commercialOperationEndDate;

        $totalDurationMonths = $investmentDurationMonths + $commercialOperationDurationMonths;
        $periods = [];

        for ($monthIndex = 0; $monthIndex < $totalDurationMonths; $monthIndex++) {
            $periodStartDate = $investmentStartDate
                ->modify(sprintf('+%d months', $monthIndex))
                ->modify('first day of this month');

            $periodEndDate = $periodStartDate->modify('last day of this month');

            $periods[] = new TimelinePeriod(
                periodNumber: $monthIndex + 1,
                periodStartDate: $periodStartDate,
                periodEndDate: $periodEndDate,
                investmentActivity: $periodStartDate >= $investmentStartDate && $periodStartDate <= $investmentEndDate,
                operatingActivity: $periodStartDate >= $commercialOperationStartDate,
                operatingStart: $periodStartDate == $commercialOperationStartDate,
            );
        }

        return new Timeline(
            investmentStartDate: $investmentStartDate,
            investmentEndDate: $investmentEndDate,
            commercialOperationStartDate: $commercialOperationStartDate,
            commercialOperationEndDate: $commercialOperationEndDate,
            modelStartDate: $modelStartDate,
            modelEndDate: $modelEndDate,
            forecastStep: $forecastStep,
            periods: $periods,
        );
    }
}
