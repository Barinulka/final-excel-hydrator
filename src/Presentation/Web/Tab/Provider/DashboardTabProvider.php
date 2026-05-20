<?php

declare(strict_types=1);

namespace App\Presentation\Web\Tab\Provider;

use App\Presentation\Web\Tab\FinancialModelTabDefinition;
use App\Presentation\Web\Tab\FinancialModelTabProviderInterface;

final readonly class DashboardTabProvider implements FinancialModelTabProviderInterface
{
    public function getDefinition(): FinancialModelTabDefinition
    {
        return new FinancialModelTabDefinition(
            key: 'dashboard',
            label: 'Панель управления',
            description: 'Управление моделью и краткая сводка',
            routeName: 'app_financial_model_edit',
            template: 'financial_model/tabs/_dashboard.html.twig',
            order: 0,
            showInSidebar: false,
        );

    }
}
