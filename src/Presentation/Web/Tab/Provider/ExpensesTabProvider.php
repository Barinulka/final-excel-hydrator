<?php

declare(strict_types=1);

namespace App\Presentation\Web\Tab\Provider;

use App\Presentation\Web\Tab\FinancialModelTabDefinition;
use App\Presentation\Web\Tab\FinancialModelTabProviderInterface;

final readonly class ExpensesTabProvider implements FinancialModelTabProviderInterface
{
    public function getDefinition(): FinancialModelTabDefinition
    {
        return new FinancialModelTabDefinition(
            key: 'expenses',
            label: 'Затраты',
            description: 'Операционные расходы',
            routeName: 'app_financial_model_edit',
            template: 'financial_model/tabs/_expenses.html.twig',
            order: 50,
        );
    }
}
