<?php

declare(strict_types=1);

namespace App\Application\Investments\ListInvestmentExpenseCategories;

final readonly class InvestmentExpenseCategoryListItem
{
    public function __construct(
        public int $id,
        public ?string $type,
        public string $title,
        public ?string $relatedExpenses,
    ) {}
}
