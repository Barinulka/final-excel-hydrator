<?php

declare(strict_types=1);

namespace App\Presentation\Web\Tab\Provider;

use App\Presentation\Web\Tab\FinancialModelTabDefinition;
use App\Presentation\Web\Tab\FinancialModelTabProviderInterface;

final readonly class InputParamsTabProvider implements FinancialModelTabProviderInterface
{
    public function getDefinition(): FinancialModelTabDefinition
    {
        return new FinancialModelTabDefinition(
            key: 'input_params',
            label: 'Входные параметры',
            description: 'Сроки и базовые настройки',
            routeName: 'app_financial_model_edit',
            template: 'financial_model/tabs/_time_params.html.twig',
            order: 10,
        );
    }
}
