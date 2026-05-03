<?php

declare(strict_types=1);

namespace App\Application\TimeParams\GetTimeParamsForEdit;

use App\Application\FinancialModel\FinancialModelRepository;
use App\Domain\FinancialModel\Enum\FinancialModelStatus;
use App\Domain\TimeParams\Enum\ForecastStep;
use App\Entity\FinancialModel;

final readonly class GetTimeParamsForEditHandler
{
    public function __construct(
        private FinancialModelRepository $financialModelRepository,
    ) {
    }

    public function handle(GetTimeParamsForEditQuery $query): GetTimeParamsForEditResult
    {
        $financialModel = $this->financialModelRepository->findOneByShortIdForProjectAndOwner(
            $query->financialModelShortId,
            $query->projectShortId,
            $query->owner
        );

        if ($financialModel === null) {
            throw new TimeParamsForEditNotFoundException("Модель '{$query->financialModelShortId}' не найдена");
        }

        return $this->buildResult($financialModel);
    }

    public function handleByModel(GetTimeParamsForEditByModelQuery $query): GetTimeParamsForEditResult
    {
        $financialModel = $this->financialModelRepository->findOneByShortIdForOwner(
            $query->financialModelShortId,
            $query->owner,
        );

        if ($financialModel === null) {
            throw new TimeParamsForEditNotFoundException("Модель '{$query->financialModelShortId}' не найдена");
        }

        return $this->buildResult($financialModel);
    }

    private function buildResult(FinancialModel $financialModel): GetTimeParamsForEditResult
    {
        $timeParams = $financialModel->getTimeParams();

        if (null === $timeParams) {
            throw new \LogicException('Финансовая модель не содержит обязательный блок временных параметров.');
        }

        $status = $financialModel->getStatus();
        if (null === $status) {
            throw new \LogicException('Финансовая модель не содержит статус.');
        }

        $project = $financialModel->getProject();
        if (null === $project) {
            throw new \LogicException('Финансовая модель не привязана к проекту.');
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
            throw new \LogicException('Финансовая модель содержит неполный блок временных параметров.');
        }

        return new GetTimeParamsForEditResult(
            projectShortId: (string) $project->getShortId(),
            projectTitle: (string) $project->getTitle(),
            financialModelShortId: (string) $financialModel->getShortId(),
            financialModelTitle: (string) $financialModel->getTitle(),
            financialModelDescription: (string) $financialModel->getDescription(),
            financialModelStatus: $status->value,
            isFinancialModelArchived: $status === FinancialModelStatus::Archived,
            investmentStartMonth: $investmentStartMonth->toString(),
            investmentDurationMonths: $investmentDurationMonths,
            commercialOperationDurationMonths: $commercialOperationDurationMonths,
            totalDurationMonths: $investmentDurationMonths + $commercialOperationDurationMonths,
            forecastStep: $forecastStep->value,
            forecastStepLabel: $this->forecastStepLabel($forecastStep),
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
