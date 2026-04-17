<?php

declare(strict_types=1);

namespace App\Application\TimeParams\GetTimeParamsForEdit;

use App\Application\FinancialModel\FinancialModelRepository;
use App\Domain\FinancialModel\Enum\FinancialModelStatus;
use App\Domain\TimeParams\Enum\ForecastStep;

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

        $timeParams = $financialModel->getTimeParams();

        if (null === $timeParams) {
            throw new \LogicException('Финансовая модель не содержит обязательный блок временных параметров.');
        }

        $status = $financialModel->getStatus();
        if (null === $status) {
            throw new \LogicException('Финансовая модель не содержит статус.');
        }

        return new GetTimeParamsForEditResult(
            projectShortId: $financialModel->getProject()->getShortId(),
            projectTitle: $financialModel->getProject()->getTitle(),
            financialModelShortId: $financialModel->getShortId(),
            financialModelTitle: $financialModel->getTitle(),
            financialModelStatus: $status->value,
            isFinancialModelArchived: $status === FinancialModelStatus::Archived,
            investmentStartMonth: $timeParams->getInvestmentStartMonth()->toString(),
            investmentDurationMonths: $timeParams->getInvestmentDurationMonths(),
            commercialOperationDurationMonths: $timeParams->getCommercialOperationDurationMonths(),
            totalDurationMonths: $timeParams->getInvestmentDurationMonths() + $timeParams->getCommercialOperationDurationMonths(),
            forecastStep: $timeParams->getForecastStep()->value,
            forecastStepLabel: $this->forecastStepLabel($timeParams->getForecastStep()),
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
