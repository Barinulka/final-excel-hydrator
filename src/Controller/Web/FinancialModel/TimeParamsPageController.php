<?php

declare(strict_types=1);

namespace App\Controller\Web\FinancialModel;

use App\Controller\BaseAbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;

final class TimeParamsPageController extends BaseAbstractController
{
    #[Route(
        path: '/models/{financialModelShortId}/time-params',
        name: 'app_financial_model_time_params',
        requirements: [
            'financialModelShortId' => '[23456789abcdefghjkmnpqrstuvwxyz]{10}',
        ],
        methods: ['GET'],
    )]
    public function __invoke(
        string $financialModelShortId,
    ): RedirectResponse {
        return $this->redirectToRoute('app_financial_model_edit', [
            'financialModelShortId' => $financialModelShortId,
            'tabKey' => 'input_params',
        ]);
    }
}
