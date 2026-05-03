<?php

declare(strict_types=1);

namespace App\Application\FinancialModel\DeleteFinancialModel;

use App\Application\FinancialModel\FinancialModelRepository;
use App\Application\Shared\Transaction\TransactionalRunner;
use App\Entity\FinancialModel;

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
            $financialModel = $this->findFinancialModel($command);

            if (null === $financialModel) {
                throw new FinancialModelForDeleteNotFoundException("Модель '{$command->financialModelShortId}' не найдена.");
            }

            if (!$financialModel->isArchived()) {
                throw new ActiveFinancialModelCannotBeDeletedException("Активную модель '{$command->financialModelShortId}' нельзя удалить напрямую.");
            }

            $this->financialModelRepository->remove($financialModel);

            return new DeleteFinancialModelResult(
                projectShortId: $this->projectShortId($financialModel),
                financialModelShortId: $command->financialModelShortId->toString(),
                isDeleted: true,
            );
        });
    }

    private function findFinancialModel(DeleteFinancialModelCommand $command): ?FinancialModel
    {
        if (null === $command->projectShortId) {
            return $this->financialModelRepository->findOneByShortIdForOwner(
                shortId: $command->financialModelShortId,
                owner: $command->owner,
            );
        }

        return $this->financialModelRepository->findOneByShortIdForProjectAndOwner(
            financialModelShortId: $command->financialModelShortId,
            projectShortId: $command->projectShortId,
            owner: $command->owner,
        );
    }

    private function projectShortId(FinancialModel $financialModel): string
    {
        return (string) $financialModel->getProject()?->getShortId();
    }
}
