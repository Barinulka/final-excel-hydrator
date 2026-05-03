<?php

declare(strict_types=1);

namespace App\Presentation\Web\Page\FinancialModel;

use App\Application\FinancialModel\FinancialModelRepository;
use App\Domain\FinancialModel\Enum\FinancialModelStatus;
use App\Domain\TimeParams\Enum\ForecastStep;
use App\Entity\FinancialModel;
use App\Entity\User;

final readonly class FinancialModelListPageBuilder
{
    public function __construct(
        private FinancialModelRepository $financialModelRepository,
    ) {
    }

    public function build(User $owner): FinancialModelListPage
    {
        $financialModels = $this->financialModelRepository->findAllForOwner($owner);

        return new FinancialModelListPage(
            userEmail: $owner->getEmail() ?? '',
            models: $this->buildItems($financialModels),
        );
    }

    /**
     * @param FinancialModel[] $financialModels
     *
     * @return FinancialModelListItem[]
     */
    private function buildItems(array $financialModels): array
    {
        $items = [];

        foreach ($financialModels as $financialModel) {
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
                throw new \LogicException('Финансовая модель содержит неполный блок временных параметров.');
            }

            $items[] = new FinancialModelListItem(
                shortId: (string) $financialModel->getShortId(),
                title: (string) $financialModel->getTitle(),
                versionNumber: (int) $financialModel->getVersionNumber(),
                isArchived: $financialModel->getStatus() === FinancialModelStatus::Archived,
                investmentStartMonth: $investmentStartMonth->toString(),
                totalDurationMonths: $investmentDurationMonths + $commercialOperationDurationMonths,
                forecastStepLabel: $this->forecastStepLabel($forecastStep),
            );
        }

        return $items;
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
