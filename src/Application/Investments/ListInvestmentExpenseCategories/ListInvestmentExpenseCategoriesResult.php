<?php

declare(strict_types=1);

namespace App\Application\Investments\ListInvestmentExpenseCategories;

final readonly class ListInvestmentExpenseCategoriesResult
{
    /** @param InvestmentExpenseCategoryListItem[] $items */
    public function __construct(
        public array $items,
    ) {}
}
