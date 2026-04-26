<?php

declare(strict_types=1);

namespace App\Presentation\Web\Tab\Provider;

use App\Presentation\Web\Tab\FinancialModelTabDefinition;
use App\Presentation\Web\Tab\FinancialModelTabProviderInterface;

final readonly class InitialInvestmentsTabProvider implements FinancialModelTabProviderInterface
{
    public function getDefinition(): FinancialModelTabDefinition
    {
        return new FinancialModelTabDefinition(
            key: 'initial_investments',
            label: 'Первоначальные инвестиции',
            description: 'Инвестиции до запуска',
            routeName: 'app_financial_model_edit',
            template: 'financial_model/tabs/_initial_investments.html.twig',
            order: 20,
        );
    }
}
