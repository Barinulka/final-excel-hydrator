<?php

declare(strict_types=1);

namespace App\Controller\Api\Investments;

use App\Application\Investments\ListInvestmentExpenseCategories\FinancialModelForInvestmentExpenseCategoriesNotFoundException;
use App\Application\Investments\ListInvestmentExpenseCategories\ListInvestmentExpenseCategoriesHandler;
use App\Application\Investments\ListInvestmentExpenseCategories\ListInvestmentExpenseCategoriesQuery;
use App\Controller\Api\BaseApiAbstractController;
use App\Domain\Shared\ValueObject\ShortId;
use App\Presentation\Api\Response\Investments\InvestmentExpenseCategoryListApiResponseFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ListInvestmentExpenseCategoriesController extends BaseApiAbstractController
{
    #[Route(
        '/api/models/{financialModelShortId}/initial-investments/categories',
        name: 'api.initial_investments.categories.list',
        requirements: ['financialModelShortId' => '[23456789abcdefghjkmnpqrstuvwxyz]{10}'],
        methods: ['GET'],
    )]
    public function __invoke(
        string $financialModelShortId,
        ListInvestmentExpenseCategoriesHandler $handler,
        InvestmentExpenseCategoryListApiResponseFactory $responseFactory,
    ): JsonResponse {
        try {
            $result = $handler->handle(new ListInvestmentExpenseCategoriesQuery(
                owner: $this->getAuthorizedUser(),
                financialModelShortId: ShortId::fromString($financialModelShortId),
            ));

            return $this->json($responseFactory->fromResult($result));
        } catch (FinancialModelForInvestmentExpenseCategoriesNotFoundException) {
            return $this->json([
                'error' => 'financial_model_not_found',
            ], JsonResponse::HTTP_NOT_FOUND);
        }
    }
}
