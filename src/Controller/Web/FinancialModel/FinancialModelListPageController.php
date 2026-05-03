<?php

declare(strict_types=1);

namespace App\Controller\Web\FinancialModel;

use App\Controller\BaseAbstractController;
use App\Presentation\Web\Page\FinancialModel\FinancialModelListPageBuilder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FinancialModelListPageController extends BaseAbstractController
{
    #[Route(path: '/models', name: 'app_models', methods: ['GET'])]
    public function __invoke(FinancialModelListPageBuilder $pageBuilder): Response
    {
        $owner = $this->getAuthorizedUser();

        return $this->render('financial_model/list.html.twig', [
            'page' => $pageBuilder->build($owner),
        ]);
    }
}
