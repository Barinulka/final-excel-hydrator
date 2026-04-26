<?php

declare(strict_types=1);

namespace App\Presentation\Web\Tab\Provider;

use App\Presentation\Web\Tab\FinancialModelTabDefinition;
use App\Presentation\Web\Tab\FinancialModelTabProviderInterface;

final readonly class WorkingCapitalTabProvider implements FinancialModelTabProviderInterface
{
    public function getDefinition(): FinancialModelTabDefinition
    {
        return new FinancialModelTabDefinition(
            key: 'working_capital',
            label: 'Оборотный капитал',
            description: 'Запасы и расчеты',
            routeName: 'app_financial_model_edit',
            template: 'financial_model/tabs/_working_capital.html.twig',
            order: 70,
        );
    }
}
