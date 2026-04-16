<?php

declare(strict_types=1);

namespace App\Application\TimeParams\UpdateTimeParams;

use App\Application\FinancialModel\FinancialModelRepository;
use App\Application\Shared\Transaction\TransactionalRunner;

final readonly class UpdateTimeParamsHandler
{
    public function __construct(
        private TransactionalRunner $transactionalRunner,
        private FinancialModelRepository $financialModelRepository,
    ) {
    }

    public function handle(UpdateTimeParamsCommand $command): UpdateTimeParamsResult
    {
        return $this->transactionalRunner->run(function () use ($command): UpdateTimeParamsResult
        {
            $financialModel = $this->financialModelRepository->findOneByShortIdForProjectAndOwner(
                financialModelShortId: $command->financialModelShortId,
                projectShortId: $command->projectShortId,
                owner: $command->owner,
            );

            if (null === $financialModel) {
                throw new FinancialModelForUpdateTimeParamsNotFoundException("Модель '{$command->financialModelShortId}' не найдена");
            }

            $timeParams = $financialModel->getTimeParams();

            if (null === $timeParams) {
                throw new \LogicException('Финансовая модель не содержит обязательный блок временных параметров.');
            }

            $timeParams->update(
                investmentStartMonth: $command->investmentStartMonth,
                investmentDuration: $command->investmentDuration,
                commercialOperationDuration: $command->commercialOperationDuration,
                forecastStep: $command->forecastStep,
            );

            $this->financialModelRepository->save($financialModel);

            return new UpdateTimeParamsResult(
                investmentStartMonth: $timeParams->getInvestmentStartMonth()->toString(),
                investmentDurationMonths: $timeParams->getInvestmentDurationMonths(),
                commercialOperationDurationMonths: $timeParams->getCommercialOperationDurationMonths(),
                forecastStep: $timeParams->getForecastStep()->value,
            );
        });
    }
}
