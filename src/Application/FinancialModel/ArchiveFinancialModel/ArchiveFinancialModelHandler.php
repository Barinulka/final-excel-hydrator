<?php

declare(strict_types=1);

namespace App\Application\FinancialModel\ArchiveFinancialModel;

use App\Application\FinancialModel\FinancialModelRepository;
use App\Application\Shared\Transaction\TransactionalRunner;
use App\Entity\FinancialModel;

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
            $financialModel = $this->findFinancialModel($command);

            if (null === $financialModel) {
                throw new FinancialModelForArchiveNotFoundException("Модель '{$command->financialModelShortId}' не найдена.");
            }

            if ($financialModel->isArchived()) {
                throw new FinancialModelAlreadyArchivedException("Модель '{$command->financialModelShortId}' уже находится в архиве.");
            }

            $financialModel->archive();

            $this->financialModelRepository->save($financialModel);

            return new ArchiveFinancialModelResult(
                projectShortId: $this->projectShortId($financialModel),
                financialModelShortId: $command->financialModelShortId->toString(),
                status: $financialModel->getStatus()->value,
                isArchived: $financialModel->isArchived(),
                archivedAt: $financialModel->getArchivedAt()?->format(\DateTimeInterface::ATOM),
            );
        });
    }

    private function findFinancialModel(ArchiveFinancialModelCommand $command): ?FinancialModel
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
