<?php

declare(strict_types=1);

namespace App\Presentation\Web\Tab\Provider;

use App\Presentation\Web\Tab\FinancialModelTabDefinition;
use App\Presentation\Web\Tab\FinancialModelTabProviderInterface;

final readonly class PayrollTabProvider implements FinancialModelTabProviderInterface
{
    public function getDefinition(): FinancialModelTabDefinition
    {
        return new FinancialModelTabDefinition(
            key: 'payroll',
            label: 'ФОТ',
            description: 'Персонал и начисления',
            routeName: 'app_financial_model_edit',
            template: 'financial_model/tabs/_payroll.html.twig',
            order: 60,
        );
    }
}
