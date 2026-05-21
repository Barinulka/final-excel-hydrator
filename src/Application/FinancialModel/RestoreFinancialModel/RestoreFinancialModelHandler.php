<?php

declare(strict_types=1);

namespace App\Application\FinancialModel\RestoreFinancialModel;

use App\Application\FinancialModel\FinancialModelRepository;
use App\Application\Shared\Transaction\TransactionalRunner;

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
            $financialModel = $this->financialModelRepository->findOneByShortIdForOwner(
                shortId: $command->financialModelShortId,
                owner: $command->owner,
            );

            if (null === $financialModel) {
                throw new FinancialModelForRestoreNotFoundException("Модель '{$command->financialModelShortId}' не найдена.");
            }

            if ($financialModel->isActive()) {
                throw new FinancialModelAlreadyActiveException("Модель '{$command->financialModelShortId}' уже активна.");
            }

            $financialModel->restore();

            $this->financialModelRepository->save($financialModel);

            return new RestoreFinancialModelResult(
                financialModelShortId: $command->financialModelShortId->toString(),
                status: $financialModel->getStatus()->value,
                isArchived: $financialModel->isArchived(),
                archivedAt: $financialModel->getArchivedAt()?->format(\DateTimeInterface::ATOM),
            );
        });
    }
}
