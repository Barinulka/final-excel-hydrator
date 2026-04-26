<?php

declare(strict_types=1);

namespace App\Presentation\Web\Tab\Provider;

use App\Presentation\Web\Tab\FinancialModelTabDefinition;
use App\Presentation\Web\Tab\FinancialModelTabProviderInterface;

final readonly class CalculationsTabProvider implements FinancialModelTabProviderInterface
{
    public function getDefinition(): FinancialModelTabDefinition
    {
        return new FinancialModelTabDefinition(
            key: 'calculations',
            label: 'Расчеты',
            description: 'Preview и показатели',
            routeName: 'app_financial_model_edit',
            template: 'financial_model/tabs/_calculations.html.twig',
            order: 90,
        );
    }
}
