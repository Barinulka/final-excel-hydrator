<?php

declare(strict_types=1);

namespace App\Controller\Web\FinancialModel;

use App\Application\TimeParams\GetTimeParamsForEdit\TimeParamsForEditNotFoundException;
use App\Controller\BaseAbstractController;
use App\Domain\Shared\ValueObject\ShortId;
use App\Presentation\Web\Page\FinancialModel\FinancialModelPageBuilder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TimeParamsPageController extends BaseAbstractController
{
    #[Route(
        path: '/projects/{projectShortId}/models/{financialModelShortId}/time-params',
        name: 'app_financial_model_time_params',
        requirements: [
            'projectShortId' => '[23456789abcdefghjkmnpqrstuvwxyz]{10}',
            'financialModelShortId' => '[23456789abcdefghjkmnpqrstuvwxyz]{10}',
        ],
        methods: ['GET'],
    )]
    public function __invoke(
        string $projectShortId,
        string $financialModelShortId,
        FinancialModelPageBuilder $pageBuilder,
    ): Response {
        try {
            $page = $pageBuilder->buildTimeParamsPage(
                owner: $this->getAuthorizedUser(),
                projectShortId: ShortId::fromString($projectShortId),
                financialModelShortId: ShortId::fromString($financialModelShortId),
            );
        } catch (TimeParamsForEditNotFoundException) {
            throw $this->createNotFoundException('Финансовая модель не найдена.');
        }

        return $this->render('financial_model/page.html.twig', [
            'page' => $page,
        ]);
    }
}
