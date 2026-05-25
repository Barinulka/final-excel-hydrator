<?php

declare(strict_types=1);

namespace App\Presentation\Api\Mapper\Investments;

use App\Application\Investments\AddInvestmentExpenseCategory\AddInvestmentExpenseCategoryCommand;
use App\Domain\Investments\Enum\InvestmentExpenseCategoryType;
use App\Domain\Shared\ValueObject\ShortId;
use App\Entity\User;
use App\Presentation\Api\Request\Investments\AddInvestmentExpenseCategoryApiRequest;
use InvalidArgumentException;

final readonly class AddInvestmentExpenseCategoryCommandMapper
{
    public function map(
        User $owner,
        ShortId $financialModelShortId,
        AddInvestmentExpenseCategoryApiRequest $request,
    ): AddInvestmentExpenseCategoryCommand {
        $type = null;

        if ($request->type !== null) {
            $type = InvestmentExpenseCategoryType::tryFrom($request->type);

            if ($type === null) {
                throw new InvalidArgumentException('Недопустимый тип категории инвестиций.');
            }
        }

        return new AddInvestmentExpenseCategoryCommand(
            owner: $owner,
            financialModelShortId: $financialModelShortId,
            type: $type,
            customTitle: $request->customTitle,
            relatedExpenses: $request->relatedExpenses,
        );
    }
}
