<?php

declare(strict_types=1);

namespace App\Application\Investments\AddInvestmentExpenseCategory;

use App\Application\FinancialModel\FinancialModelRepository;
use App\Application\Investments\InvestmentBlockRepository;
use App\Application\Shared\Transaction\TransactionalRunner;
use App\Entity\InvestmentBlock;
use App\Entity\InvestmentExpenseCategory;

final readonly class AddInvestmentExpenseCategoryHandler
{
    public function __construct(
        private FinancialModelRepository $financialModelRepository,
        private InvestmentBlockRepository $investmentBlockRepository,
        private TransactionalRunner $transactionalRunner,
    ) {
    }

    public function handle(AddInvestmentExpenseCategoryCommand $command): AddInvestmentExpenseCategoryResult
    {
        return $this->transactionalRunner->run(function () use ($command): AddInvestmentExpenseCategoryResult {
            $financialModel = $this->financialModelRepository->findOneByShortIdForOwner(
                shortId: $command->financialModelShortId,
                owner: $command->owner,
            );

            if (null === $financialModel) {
                throw new FinancialModelForInvestmentExpenseCategoryNotFoundException("Модель '{$command->financialModelShortId}' не найдена.");
            }

            if ($financialModel->isArchived()) {
                throw new ArchivedFinancialModelCannotBeChangedException("Модель '{$command->financialModelShortId}' находится в архиве.");
            }

            $investmentBlock = $this->investmentBlockRepository->findOneByFinancialModel($financialModel);

            if ($investmentBlock === null) {
                $investmentBlock = new InvestmentBlock($financialModel);
            }

            if ($command->type !== null) {
                $category = InvestmentExpenseCategory::fromPredefinedType(
                    investmentBlock: $investmentBlock,
                    type: $command->type,
                    relatedExpenses: $command->relatedExpenses,
                );
            } else {
                $category = InvestmentExpenseCategory::fromCustomTitle(
                    investmentBlock: $investmentBlock,
                    customTitle: (string) $command->customTitle,
                    relatedExpenses: $command->relatedExpenses,
                );
            }

            $this->investmentBlockRepository->save($investmentBlock);

            return new AddInvestmentExpenseCategoryResult(
                financialModelShortId: $command->financialModelShortId->toString(),
                title: $category->getTitle(),
                type: $category->getType()?->value,
                customTitle: $category->getCustomTitle(),
                relatedExpenses: $category->getRelatedExpenses(),
            );
        });
    }
}
