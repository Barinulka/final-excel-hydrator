<?php

declare(strict_types=1);

namespace App\Application\FinancialModel\BuildFinancialModelSummary;

use App\Application\FinancialModel\FinancialModelRepository;
use App\Domain\Shared\ValueObject\MonthDuration;
use App\Domain\TimeParams\Calculation\TimelineCalculator;
use App\Domain\TimeParams\Enum\ForecastStep;
use LogicException;

final readonly class BuildFinancialModelSummaryHandler
{
    public function __construct(
        private FinancialModelRepository $financialModelRepository,
        private TimelineCalculator $timelineCalculator,
    ) {
    }

    public function handle(BuildFinancialModelSummaryQuery $query): BuildFinancialModelSummaryResult
    {
        $financialModel = $this->financialModelRepository->findOneByShortIdForProjectAndOwner(
            financialModelShortId: $query->financialModelShortId,
            projectShortId: $query->projectShortId,
            owner: $query->owner,
        );

        if (null === $financialModel) {
            throw new FinancialModelSummaryNotFoundException("Финансовая модель '{$query->financialModelShortId}' не найдена.");
        }

        $project = $financialModel->getProject();
        if (null === $project) {
            throw new LogicException('Финансовая модель не привязана к проекту.');
        }

        $timeParams = $financialModel->getTimeParams();
        if (null === $timeParams) {
            throw new LogicException('Финансовая модель не содержит обязательный блок временных параметров.');
        }

        $status = $financialModel->getStatus();
        if (null === $status) {
            throw new LogicException('Финансовая модель не содержит статус.');
        }

        $amountDisplayFormat = $financialModel->getAmountDisplayFormat();
        if (null === $amountDisplayFormat) {
            throw new LogicException('Финансовая модель не содержит формат отображения сумм.');
        }

        $investmentStartMonth = $timeParams->getInvestmentStartMonth();
        if (null === $investmentStartMonth) {
            throw new LogicException('Временные параметры не содержат дату начала инвестиций.');
        }

        $investmentDurationMonths = $timeParams->getInvestmentDurationMonths();
        if (null === $investmentDurationMonths) {
            throw new LogicException('Временные параметры не содержат длительность инвестиций.');
        }

        $commercialOperationDurationMonths = $timeParams->getCommercialOperationDurationMonths();
        if (null === $commercialOperationDurationMonths) {
            throw new LogicException('Временные параметры не содержат длительность коммерческой эксплуатации.');
        }

        $forecastStep = $timeParams->getForecastStep();
        if (null === $forecastStep) {
            throw new LogicException('Временные параметры не содержат шаг прогнозирования.');
        }

        $timeline = $this->timelineCalculator->calculate(
            investmentStartMonth: $investmentStartMonth,
            investmentDuration: MonthDuration::fromInt($investmentDurationMonths),
            commercialOperationDuration: MonthDuration::fromInt($commercialOperationDurationMonths),
            forecastStep: $forecastStep,
        );

        return new BuildFinancialModelSummaryResult(
            projectShortId: $project->getShortId(),
            projectTitle: $project->getTitle(),
            financialModelShortId: $financialModel->getShortId(),
            financialModelTitle: $financialModel->getTitle(),
            financialModelStatus: $status->value,
            isFinancialModelArchived: $financialModel->isArchived(),
            amountDisplayFormat: $amountDisplayFormat->value,
            timeParams: new TimeParamsSummary(
                investmentStartMonth: $investmentStartMonth->toString(),
                investmentDurationMonths: $investmentDurationMonths,
                commercialOperationDurationMonths: $commercialOperationDurationMonths,
                totalDurationMonths: $investmentDurationMonths + $commercialOperationDurationMonths,
                forecastStep: $forecastStep->value,
                forecastStepLabel: $this->forecastStepLabel($forecastStep),
            ),
            timeline: new TimelineSummary(
                investmentStartDate: $timeline->getInvestmentStartDate()->format('Y-m-d'),
                investmentEndDate: $timeline->getInvestmentEndDate()->format('Y-m-d'),
                commercialOperationStartDate: $timeline->getCommercialOperationStartDate()->format('Y-m-d'),
                commercialOperationEndDate: $timeline->getCommercialOperationEndDate()->format('Y-m-d'),
                modelStartDate: $timeline->getModelStartDate()->format('Y-m-d'),
                modelEndDate: $timeline->getModelEndDate()->format('Y-m-d'),
                periodCount: $timeline->getPeriodCount(),
                periods: array_map(
                    static fn ($period): TimelinePeriodSummary => new TimelinePeriodSummary(
                        periodNumber: $period->getPeriodNumber(),
                        yearMonth: $period->getYearMonth()->toString(),
                        periodStartDate: $period->getPeriodStartDate()->format('Y-m-d'),
                        periodEndDate: $period->getPeriodEndDate()->format('Y-m-d'),
                        investmentActivity: $period->isInvestmentActivity(),
                        operatingActivity: $period->isOperatingActivity(),
                        operatingStart: $period->isOperatingStart(),
                    ),
                    $timeline->getPeriods(),
                ),
            ),
            warnings: [],
        );
    }

    private function forecastStepLabel(ForecastStep $forecastStep): string
    {
        return match ($forecastStep) {
            ForecastStep::Month => 'мес.',
            ForecastStep::Quarter => 'кв.',
            ForecastStep::Year => 'год',
        };
    }
}
