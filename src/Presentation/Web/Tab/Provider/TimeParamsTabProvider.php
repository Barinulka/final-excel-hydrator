<?php

declare(strict_types=1);

namespace App\Presentation\Web\Tab\Provider;

use App\Presentation\Web\Tab\FinancialModelTabDefinition;
use App\Presentation\Web\Tab\FinancialModelTabProviderInterface;

final readonly class TimeParamsTabProvider implements FinancialModelTabProviderInterface
{
    public function getDefinition(): FinancialModelTabDefinition
    {
        return new FinancialModelTabDefinition(
            key: 'time_params',
            label: 'Временные параметры',
            description: 'Период и шаг',
            routeName: 'app_financial_model_time_params',
            template: 'financial_model/tabs/_time_params.html.twig',
            order: 10,
        );
    }
}
