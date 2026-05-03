<?php

declare(strict_types=1);

namespace App\Application\FinancialModel\UpdateFinancialModelDetails;

use App\Application\FinancialModel\FinancialModelRepository;
use App\Application\Shared\Transaction\TransactionalRunner;
use App\Domain\FinancialModel\Enum\FinancialModelStatus;

final readonly class UpdateFinancialModelDetailsHandler
{
    public function __construct(
        private FinancialModelRepository $financialModelRepository,
        private TransactionalRunner $transactionalRunner,
    ) {
    }

    public function handle(UpdateFinancialModelDetailsCommand $command): UpdateFinancialModelDetailsResult
    {
        return $this->transactionalRunner->run(function () use ($command): UpdateFinancialModelDetailsResult {
            $financialModel = $this->financialModelRepository->findOneByShortIdForOwner(
                shortId: $command->financialModelShortId,
                owner: $command->owner
            );

            if (null === $financialModel) {
                throw new FinancialModelForUpdateDetailsNotFoundException("Модель '{$command->financialModelShortId}' не найдена.");
            }

            if (FinancialModelStatus::Archived === $financialModel->getStatus()) {
                throw new ArchivedFinancialModelCannotBeUpdatedDetailsException("Невозможно обновить модель, находящуюся в архиве.");
            }

            $financialModel->rename($command->title);
            $financialModel->changeDescription($command->description);

            $this->financialModelRepository->save($financialModel);

            return new UpdateFinancialModelDetailsResult(
                financialModelShortId: $command->financialModelShortId->toString(),
                title: $financialModel->getTitle(),
                description: $financialModel->getDescription(),
            );
        });
    }
}
