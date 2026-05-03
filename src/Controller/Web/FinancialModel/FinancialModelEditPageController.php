<?php

declare(strict_types=1);

namespace App\Controller\Web\FinancialModel;

use App\Application\TimeParams\GetTimeParamsForEdit\TimeParamsForEditNotFoundException;
use App\Controller\BaseAbstractController;
use App\Domain\Shared\ValueObject\ShortId;
use App\Presentation\Web\Page\FinancialModel\FinancialModelPageBuilder;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FinancialModelEditPageController extends BaseAbstractController
{
    #[Route(
        path: '/models/{financialModelShortId}/edit/{tabKey}',
        name: 'app_financial_model_edit',
        requirements: [
            'financialModelShortId' => '[23456789abcdefghjkmnpqrstuvwxyz]{10}',
            'tabKey' => '[a-z0-9_]+',
        ],
        methods: ['GET'],
    )]
    public function __invoke(
        string $financialModelShortId,
        string $tabKey,
        FinancialModelPageBuilder $pageBuilder,
    ): Response {
        try {
            $page = $pageBuilder->buildForModel(
                owner: $this->getAuthorizedUser(),
                financialModelShortId: ShortId::fromString($financialModelShortId),
                activeTabKey: $tabKey,
            );
        } catch (TimeParamsForEditNotFoundException) {
            throw $this->createNotFoundException('Финансовая модель не найдена.');
        } catch (\InvalidArgumentException) {
            throw $this->createNotFoundException('Вкладка финансовой модели не найдена.');
        }

        return $this->render('financial_model/page.html.twig', [
            'page' => $page,
        ]);
    }

    #[Route(
        path: '/projects/{projectShortId}/models/{financialModelShortId}/edit/{tabKey}',
        name: 'app_financial_model_edit_legacy',
        requirements: [
            'projectShortId' => '[23456789abcdefghjkmnpqrstuvwxyz]{10}',
            'financialModelShortId' => '[23456789abcdefghjkmnpqrstuvwxyz]{10}',
            'tabKey' => '[a-z0-9_]+',
        ],
        methods: ['GET'],
    )]
    public function legacy(
        string $financialModelShortId,
        string $tabKey,
    ): RedirectResponse {
        return $this->redirectToRoute('app_financial_model_edit', [
            'financialModelShortId' => $financialModelShortId,
            'tabKey' => $tabKey,
        ]);
    }
}
