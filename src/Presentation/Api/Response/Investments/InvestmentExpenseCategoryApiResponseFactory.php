<?php

declare(strict_types=1);

namespace App\Presentation\Api\Response\Investments;

use App\Application\Investments\AddInvestmentExpenseCategory\AddInvestmentExpenseCategoryResult;

final readonly class InvestmentExpenseCategoryApiResponseFactory
{
    /**
     * @return array{
     *     financialModelShortId: string,
     *     title: string,
     *     type: string|null,
     *     customTitle: string|null,
     *     relatedExpenses: string|null
     * }
     */
    public function fromAddResult(AddInvestmentExpenseCategoryResult $result): array
    {
        return [
            'financialModelShortId' => $result->financialModelShortId,
            'title' => $result->title,
            'type' => $result->type,
            'customTitle' => $result->customTitle,
            'relatedExpenses' => $result->relatedExpenses,
        ];
    }
}
