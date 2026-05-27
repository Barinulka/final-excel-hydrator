<?php

declare(strict_types=1);

namespace App\Application\Investments\DeleteInvestmentExpenseCategory;

use App\Application\FinancialModel\FinancialModelRepository;
use App\Application\Investments\InvestmentBlockRepository;
use App\Application\Shared\Transaction\TransactionalRunner;

final readonly class DeleteInvestmentExpenseCategoryHandler
{
    public function __construct(
        private FinancialModelRepository $financialModelRepository,
        private InvestmentBlockRepository $investmentBlockRepository,
        private TransactionalRunner $transactionalRunner,
    ) {
    }

    public function handle(DeleteInvestmentExpenseCategoryCommand $command): DeleteInvestmentExpenseCategoryResult
    {
        return $this->transactionalRunner->run(function () use ($command): DeleteInvestmentExpenseCategoryResult {
            $financialModel = $this->financialModelRepository->findOneByShortIdForOwner(
                shortId: $command->financialModelShortId,
                owner: $command->owner,
            );

            if (null === $financialModel) {
                throw new FinancialModelForInvestmentExpenseCategoryDeleteNotFoundException(
                    "Модель '{$command->financialModelShortId}' не найдена."
                );
            }

            if ($financialModel->isArchived()) {
                throw new ArchivedFinancialModelCannotDeleteInvestmentExpenseCategoryException(
                    "Модель '{$command->financialModelShortId}' находится в архиве."
                );
            }

            $investmentBlock = $this->investmentBlockRepository->findOneByFinancialModel($financialModel);

            if (null === $investmentBlock) {
                throw new InvestmentExpenseCategoryForDeleteNotFoundException(
                    "Категория '{$command->categoryId}' не найдена."
                );
            }

            $category = $investmentBlock->findExpenseCategoryById($command->categoryId);

            if (null === $category) {
                throw new InvestmentExpenseCategoryForDeleteNotFoundException(
                    "Категория '{$command->categoryId}' не найдена."
                );
            }

            $investmentBlock->removeExpenseCategory($category);
            $this->investmentBlockRepository->save($investmentBlock);

            return new DeleteInvestmentExpenseCategoryResult(
                categoryId: $command->categoryId,
                isDeleted: true,
            );
        });
    }
}
