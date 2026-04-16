<?php

declare(strict_types=1);

namespace App\Application\TimeParams\GetTimeParamsForEdit;

use App\Application\FinancialModel\FinancialModelRepository;

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

        return new GetTimeParamsForEditResult(
            projectShortId: $financialModel->getProject()->getShortId(),
            projectTitle: $financialModel->getProject()->getTitle(),
            financialModelShortId: $financialModel->getShortId(),
            financialModelTitle: $financialModel->getTitle(),
            investmentStartMonth: $timeParams->getInvestmentStartMonth()->toString(),
            investmentDurationMonths: $timeParams->getInvestmentDurationMonths(),
            commercialOperationDurationMonths: $timeParams->getCommercialOperationDurationMonths(),
            forecastStep: $timeParams->getForecastStep()->value
        );
    }
}
