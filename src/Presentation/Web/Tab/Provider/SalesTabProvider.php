<?php

declare(strict_types=1);

namespace App\Presentation\Web\Tab\Provider;

use App\Presentation\Web\Tab\FinancialModelTabDefinition;
use App\Presentation\Web\Tab\FinancialModelTabProviderInterface;

final readonly class SalesTabProvider implements FinancialModelTabProviderInterface
{
    public function getDefinition(): FinancialModelTabDefinition
    {
        return new FinancialModelTabDefinition(
            key: 'sales',
            label: 'Продажи',
            description: 'Объемы и цены',
            routeName: 'app_financial_model_edit',
            template: 'financial_model/tabs/_sales.html.twig',
            order: 30,
        );
    }
}
