<?php

declare(strict_types=1);

namespace App\Application\Investments\DeleteInvestmentExpenseCategory;

final readonly class DeleteInvestmentExpenseCategoryResult
{
    public function __construct(
        public int $categoryId,
        public bool $isDeleted,
    ) {
    }
}
