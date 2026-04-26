<?php

declare(strict_types=1);

namespace App\Presentation\Web\Tab\Provider;

use App\Presentation\Web\Tab\FinancialModelTabDefinition;
use App\Presentation\Web\Tab\FinancialModelTabProviderInterface;

final readonly class SettingsTabProvider implements FinancialModelTabProviderInterface
{
    public function getDefinition(): FinancialModelTabDefinition
    {
        return new FinancialModelTabDefinition(
            key: 'settings',
            label: 'Настройки',
            description: 'Налоги и допущения',
            routeName: 'app_financial_model_edit',
            template: 'financial_model/tabs/_settings.html.twig',
            order: 80,
        );
    }
}
