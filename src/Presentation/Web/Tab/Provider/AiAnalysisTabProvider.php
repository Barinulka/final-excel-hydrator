<?php

declare(strict_types=1);

namespace App\Presentation\Web\Tab\Provider;

use App\Presentation\Web\Tab\FinancialModelTabDefinition;
use App\Presentation\Web\Tab\FinancialModelTabProviderInterface;

final readonly class AiAnalysisTabProvider implements FinancialModelTabProviderInterface
{
    public function getDefinition(): FinancialModelTabDefinition
    {
        return new FinancialModelTabDefinition(
            key: 'ai_analysis',
            label: 'AI-анализ',
            description: 'Выводы и рекомендации',
            routeName: 'app_financial_model_edit',
            template: 'financial_model/tabs/_ai_analysis.html.twig',
            order: 100,
        );
    }
}
