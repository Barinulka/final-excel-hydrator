<?php

declare(strict_types=1);

namespace App\Presentation\Web\Tab\Provider;

use App\Presentation\Web\Tab\FinancialModelTabDefinition;
use App\Presentation\Web\Tab\FinancialModelTabProviderInterface;

final readonly class CostOfSalesTabProvider implements FinancialModelTabProviderInterface
{
    public function getDefinition(): FinancialModelTabDefinition
    {
        return new FinancialModelTabDefinition(
            key: 'cost_of_sales',
            label: 'Себестоимость',
            description: 'Прямые расходы',
            routeName: 'app_financial_model_edit',
            template: 'financial_model/tabs/_cost_of_sales.html.twig',
            order: 40,
        );
    }
}
