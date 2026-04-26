<?php

declare(strict_types=1);

namespace App\Application\Calculation\BuildFinancialModelCalculation;

use App\Application\Calculation\CalculationResult;
use App\Application\Calculation\CalculationRow;
use App\Application\Calculation\CalculationTable;
use App\Application\FinancialModel\FinancialModelRepository;
use App\Domain\Shared\ValueObject\MonthDuration;
use App\Domain\TimeParams\Calculation\TimelineCalculator;
use App\Domain\TimeParams\Calculation\TimelinePeriod;

final readonly class BuildFinancialModelCalculationHandler
{
    public function __construct(
        private FinancialModelRepository $financialModelRepository,
        private TimelineCalculator $timelineCalculator,
    ) {
    }

    public function handle(BuildFinancialModelCalculationQuery $query): CalculationResult
    {
        $financialModel = $this->financialModelRepository->findOneByShortIdForProjectAndOwner(
            financialModelShortId: $query->financialModelShortId,
            projectShortId: $query->projectShortId,
            owner: $query->owner,
        );

        if (null === $financialModel) {
            throw new FinancialModelForCalculationNotFoundException("Модель '{$query->financialModelShortId}' не найдена.");
        }

        $timeParams = $financialModel->getTimeParams();

        if (null === $timeParams) {
            throw new \LogicException('Финансовая модель не содержит обязательный блок временных параметров.');
        }

        $investmentStartMonth = $timeParams->getInvestmentStartMonth();
        $investmentDurationMonths = $timeParams->getInvestmentDurationMonths();
        $commercialOperationDurationMonths = $timeParams->getCommercialOperationDurationMonths();
        $forecastStep = $timeParams->getForecastStep();

        if (
            null === $investmentStartMonth
            || null === $investmentDurationMonths
            || null === $commercialOperationDurationMonths
            || null === $forecastStep
        ) {
            throw new \LogicException('Временные параметры финансовой модели заполнены не полностью.');
        }

        $timeline = $this->timelineCalculator->calculate(
            investmentStartMonth: $investmentStartMonth,
            investmentDuration: MonthDuration::fromInt($investmentDurationMonths),
            commercialOperationDuration: MonthDuration::fromInt($commercialOperationDurationMonths),
            forecastStep: $forecastStep,
        );

        $periods = $timeline->getPeriods();

        return new CalculationResult(
            tables: [
                new CalculationTable(
                    code: 'timeline',
                    title: 'Временная шкала',
                    periods: array_map(
                        static fn (TimelinePeriod $period): string => $period->getYearMonth()->toString(),
                        $periods,
                    ),
                    rows: [
                        new CalculationRow(
                            code: 'period_start_date',
                            title: 'Начало месяца',
                            values: array_map(
                                static fn (TimelinePeriod $period): string => $period->getPeriodStartDate()->format('Y-m-d'),
                                $periods,
                            ),
                        ),
                        new CalculationRow(
                            code: 'period_end_date',
                            title: 'Окончание месяца',
                            values: array_map(
                                static fn (TimelinePeriod $period): string => $period->getPeriodEndDate()->format('Y-m-d'),
                                $periods,
                            ),
                        ),
                        new CalculationRow(
                            code: 'investment_activity',
                            title: 'Инвестиционная деятельность',
                            values: array_map(
                                static fn (TimelinePeriod $period): int => $period->isInvestmentActivity() ? 1 : 0,
                                $periods,
                            ),
                        ),
                        new CalculationRow(
                            code: 'operating_activity',
                            title: 'Операционная деятельность',
                            values: array_map(
                                static fn (TimelinePeriod $period): int => $period->isOperatingActivity() ? 1 : 0,
                                $periods,
                            ),
                        ),
                        new CalculationRow(
                            code: 'operating_start',
                            title: 'Старт операционной деятельности',
                            values: array_map(
                                static fn (TimelinePeriod $period): int => $period->isOperatingStart() ? 1 : 0,
                                $periods,
                            ),
                        ),
                    ],
                ),
            ],
            metrics: [
                'period_count' => $timeline->getPeriodCount(),
            ],
            warnings: [],
        );
    }
}
