<?php

declare(strict_types=1);

namespace App\Application\FinancialModel\ArchiveFinancialModel;

use App\Application\FinancialModel\FinancialModelRepository;
use App\Application\Shared\Transaction\TransactionalRunner;

final readonly class ArchiveFinancialModelHandler
{
    public function __construct(
        private FinancialModelRepository $financialModelRepository,
        private TransactionalRunner $transactionalRunner,
    ) {
    }

    public function handle(ArchiveFinancialModelCommand $command): ArchiveFinancialModelResult
    {
        return $this->transactionalRunner->run(function () use ($command): ArchiveFinancialModelResult {
            $financialModel = $this->financialModelRepository->findOneByShortIdForProjectAndOwner(
                financialModelShortId: $command->financialModelShortId,
                projectShortId: $command->projectShortId,
                owner: $command->owner
            );

            if (null === $financialModel) {
                throw new FinancialModelForArchiveNotFoundException("Модель '{$command->financialModelShortId}' не найдена.");
            }

            if ($financialModel->isArchived()) {
                throw new FinancialModelAlreadyArchivedException("Модель '{$command->financialModelShortId}' уже находится в архиве.");
            }

            $financialModel->archive();

            $this->financialModelRepository->save($financialModel);

            return new ArchiveFinancialModelResult(
                projectShortId: $command->projectShortId->toString(),
                financialModelShortId: $command->financialModelShortId->toString(),
                status: $financialModel->getStatus()->value,
                isArchived: $financialModel->isArchived(),
            );
        });
    }
}
