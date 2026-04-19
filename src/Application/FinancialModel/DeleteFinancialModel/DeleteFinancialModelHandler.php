<?php

declare(strict_types=1);

namespace App\Application\FinancialModel\DeleteFinancialModel;

use App\Application\FinancialModel\FinancialModelRepository;
use App\Application\Shared\Transaction\TransactionalRunner;

final readonly class DeleteFinancialModelHandler
{
    public function __construct(
        private FinancialModelRepository $financialModelRepository,
        private TransactionalRunner $transactionalRunner,
    ) {
    }

    public function handle(DeleteFinancialModelCommand $command): DeleteFinancialModelResult
    {
        return $this->transactionalRunner->run(function () use ($command): DeleteFinancialModelResult {
            $financialModel = $this->financialModelRepository->findOneByShortIdForProjectAndOwner(
                financialModelShortId: $command->financialModelShortId,
                projectShortId: $command->projectShortId,
                owner: $command->owner,
            );

            if (null === $financialModel) {
                throw new FinancialModelForDeleteNotFoundException("Модель '{$command->financialModelShortId}' не найдена.");
            }

            if (!$financialModel->isArchived()) {
                throw new ActiveFinancialModelCannotBeDeletedException("Активную модель '{$command->financialModelShortId}' нельзя удалить напрямую.");
            }

            $this->financialModelRepository->remove($financialModel);

            return new DeleteFinancialModelResult(
                projectShortId: $command->projectShortId->toString(),
                financialModelShortId: $command->financialModelShortId->toString(),
                isDeleted: true,
            );
        });
    }
}
