<?php

declare(strict_types=1);

namespace App\Application\FinancialModel\RenameFinancialModel;

use App\Application\FinancialModel\FinancialModelRepository;
use App\Application\Shared\Transaction\TransactionalRunner;
use App\Domain\FinancialModel\Enum\FinancialModelStatus;

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
            $financialModel = $this->financialModelRepository->findOneByShortIdForProjectAndOwner(
                financialModelShortId: $command->financialModelShortId,
                projectShortId: $command->projectShortId,
                owner: $command->owner,
            );

            if (null === $financialModel) {
                throw new FinancialModelForRenameNotFoundException("Модель '{$command->financialModelShortId}' не найдена.");
            }

            if (FinancialModelStatus::Archived === $financialModel->getStatus()) {
                throw new ArchivedFinancialModelCannotBeRenamedException("Невозможно переименовать модель, находящуюся в архиве.");
            }

            $financialModel->rename($command->title);

            $this->financialModelRepository->save($financialModel);

            return new RenameFinancialModelResult(
                projectShortId: $command->projectShortId->toString(),
                financialModelShortId: $command->financialModelShortId->toString(),
                title: $financialModel->getTitle(),
            );
        });
    }
}
