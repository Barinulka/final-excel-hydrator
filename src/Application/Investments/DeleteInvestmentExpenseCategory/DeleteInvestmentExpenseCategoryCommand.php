<?php

declare(strict_types=1);

namespace App\Application\Investments\DeleteInvestmentExpenseCategory;

use App\Domain\Shared\ValueObject\ShortId;
use App\Entity\User;

final readonly class DeleteInvestmentExpenseCategoryCommand
{
    public function __construct(
        public User $owner,
        public ShortId $financialModelShortId,
        public int $categoryId,
    ) {
    }
}
