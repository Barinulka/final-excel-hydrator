<?php

declare(strict_types=1);

namespace App\Presentation\Api\Response\Investments;

use App\Application\Investments\ListInvestmentExpenseCategories\InvestmentExpenseCategoryListItem;
use App\Application\Investments\ListInvestmentExpenseCategories\ListInvestmentExpenseCategoriesResult;

final readonly class InvestmentExpenseCategoryListApiResponseFactory
{
    /**
     * @return array{
     *     items: list<array{
     *         id: int,
     *         type: string|null,
     *         title: string,
     *         relatedExpenses: string|null
     *     }>
     * }
     */
    public function fromResult(ListInvestmentExpenseCategoriesResult $result): array
    {
        return [
            'items' => array_map(
                static fn (InvestmentExpenseCategoryListItem $item): array => [
                    'id' => $item->id,
                    'type' => $item->type,
                    'title' => $item->title,
                    'relatedExpenses' => $item->relatedExpenses,
                ],
                $result->items,
            ),
        ];
    }
}
