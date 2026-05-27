<?php

declare(strict_types=1);

namespace App\Application\Investments\ListInvestmentExpenseCategories;

use App\Domain\Shared\ValueObject\ShortId;
use App\Entity\User;

final readonly class ListInvestmentExpenseCategoriesQuery
{
    public function __construct(
        public User $owner,
        public ShortId $financialModelShortId,
    ) {}
}
