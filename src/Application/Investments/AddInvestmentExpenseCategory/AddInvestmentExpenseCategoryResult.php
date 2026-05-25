<?php

declare(strict_types=1);

namespace App\Application\Investments\AddInvestmentExpenseCategory;

final readonly class AddInvestmentExpenseCategoryResult
{
    public function __construct(
        public string $financialModelShortId,
        public string $title,
        public ?string $type,
        public ?string $customTitle,
        public ?string $relatedExpenses,
    ) {
    }
}
