<?php

declare(strict_types=1);

namespace App\Application\Investments\ListInvestmentExpenseCategories;

use App\Application\FinancialModel\FinancialModelRepository;
use App\Application\Investments\InvestmentBlockRepository;
use App\Entity\InvestmentExpenseCategory;
use LogicException;

final readonly class ListInvestmentExpenseCategoriesHandler
{
    public function __construct(
        private FinancialModelRepository $financialModelRepository,
        private InvestmentBlockRepository $investmentBlockRepository,
    ) {
    }

    public function handle(ListInvestmentExpenseCategoriesQuery $query): ListInvestmentExpenseCategoriesResult
    {
        $financialModel = $this->financialModelRepository->findOneByShortIdForOwner(
            shortId: $query->financialModelShortId,
            owner: $query->owner,
        );

        if (null === $financialModel) {
            throw new FinancialModelForInvestmentExpenseCategoriesNotFoundException(
                "Модель '{$query->financialModelShortId}' не найдена."
            );
        }

        $investmentBlock = $this->investmentBlockRepository->findOneByFinancialModel($financialModel);

        if (null === $investmentBlock) {
            return new ListInvestmentExpenseCategoriesResult(items: []);
        }

        $items = array_map(
            $this->createItem(...),
            $investmentBlock->getExpenseCategories()->toArray(),
        );

        return new ListInvestmentExpenseCategoriesResult(items: $items);
    }

    private function createItem(InvestmentExpenseCategory $category): InvestmentExpenseCategoryListItem
    {
        $id = $category->getId();

        if (null === $id) {
            throw new LogicException('Investment expense category must have id for list output.');
        }

        return new InvestmentExpenseCategoryListItem(
            id: $id,
            type: $category->getType()?->value,
            title: $category->getTitle(),
            relatedExpenses: $category->getRelatedExpenses(),
        );
    }
}
