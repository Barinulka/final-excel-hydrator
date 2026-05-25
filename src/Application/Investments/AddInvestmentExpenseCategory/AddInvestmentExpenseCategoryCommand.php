<?php

declare(strict_types=1);

namespace App\Application\Investments\AddInvestmentExpenseCategory;

use App\Domain\Investments\Enum\InvestmentExpenseCategoryType;
use App\Domain\Shared\ValueObject\ShortId;
use App\Entity\User;

final readonly class AddInvestmentExpenseCategoryCommand
{
    public function __construct(
        public User $owner,
        public ShortId $financialModelShortId,
        public ?InvestmentExpenseCategoryType $type,
        public ?string $customTitle,
        public ?string $relatedExpenses,
    ) {
    }
}
