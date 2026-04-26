<?php

declare(strict_types=1);

namespace App\Presentation\Web\Tab\Provider;

use App\Presentation\Web\Tab\FinancialModelTabDefinition;
use App\Presentation\Web\Tab\FinancialModelTabProviderInterface;

final readonly class ExportTabProvider implements FinancialModelTabProviderInterface
{
    public function getDefinition(): FinancialModelTabDefinition
    {
        return new FinancialModelTabDefinition(
            key: 'export',
            label: 'Экспорт',
            description: 'Excel и файлы',
            routeName: 'app_financial_model_edit',
            template: 'financial_model/tabs/_export.html.twig',
            order: 110,
        );
    }
}
