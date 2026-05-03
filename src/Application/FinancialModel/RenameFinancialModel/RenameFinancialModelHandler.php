<?php

declare(strict_types=1);

namespace App\Application\FinancialModel\RenameFinancialModel;

use App\Application\FinancialModel\FinancialModelRepository;
use App\Application\Shared\Transaction\TransactionalRunner;
use App\Domain\FinancialModel\Enum\FinancialModelStatus;
use App\Entity\FinancialModel;

final readonly class RenameFinancialModelHandler
{
    public function __construct(
        private FinancialModelRepository $financialModelRepository,
        private TransactionalRunner $transactionalRunner,
    ) {
    }

    public function handle(RenameFinancialModelCommand $command): RenameFinancialModelResult
    {
        return $this->transactionalRunner->run(function () use ($command): RenameFinancialModelResult {
            $financialModel = $this->findFinancialModel($command);

            if (null === $financialModel) {
                throw new FinancialModelForRenameNotFoundException("Модель '{$command->financialModelShortId}' не найдена.");
            }

            if (FinancialModelStatus::Archived === $financialModel->getStatus()) {
                throw new ArchivedFinancialModelCannotBeRenamedException("Невозможно переименовать модель, находящуюся в архиве.");
            }

            $financialModel->rename($command->title);

            $this->financialModelRepository->save($financialModel);

            return new RenameFinancialModelResult(
                projectShortId: $this->projectShortId($financialModel),
                financialModelShortId: $command->financialModelShortId->toString(),
                title: $financialModel->getTitle(),
            );
        });
    }

    private function findFinancialModel(RenameFinancialModelCommand $command): ?FinancialModel
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
