<?php

declare(strict_types=1);

namespace App\Application\FinancialModel\RestoreFinancialModel;

use App\Application\FinancialModel\FinancialModelRepository;
use App\Application\Shared\Transaction\TransactionalRunner;
use App\Entity\FinancialModel;

final readonly class RestoreFinancialModelHandler
{
    public function __construct(
        private FinancialModelRepository $financialModelRepository,
        private TransactionalRunner $transactionalRunner,
    ) {
    }

    public function handle(RestoreFinancialModelCommand $command): RestoreFinancialModelResult
    {
        return $this->transactionalRunner->run(function () use ($command): RestoreFinancialModelResult {
            $financialModel = $this->findFinancialModel($command);

            if (null === $financialModel) {
                throw new FinancialModelForRestoreNotFoundException("Модель '{$command->financialModelShortId}' не найдена.");
            }

            if ($financialModel->isActive()) {
                throw new FinancialModelAlreadyActiveException("Модель '{$command->financialModelShortId}' уже активна.");
            }

            $financialModel->restore();

            $this->financialModelRepository->save($financialModel);

            return new RestoreFinancialModelResult(
                projectShortId: $this->projectShortId($financialModel),
                financialModelShortId: $command->financialModelShortId->toString(),
                status: $financialModel->getStatus()->value,
                isArchived: $financialModel->isArchived(),
                archivedAt: $financialModel->getArchivedAt()?->format(\DateTimeInterface::ATOM),
            );
        });
    }

    private function findFinancialModel(RestoreFinancialModelCommand $command): ?FinancialModel
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
