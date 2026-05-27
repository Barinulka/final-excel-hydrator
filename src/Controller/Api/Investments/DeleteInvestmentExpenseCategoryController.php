<?php

declare(strict_types=1);

namespace App\Controller\Api\Investments;

use App\Application\Investments\DeleteInvestmentExpenseCategory\ArchivedFinancialModelCannotDeleteInvestmentExpenseCategoryException;
use App\Application\Investments\DeleteInvestmentExpenseCategory\DeleteInvestmentExpenseCategoryCommand;
use App\Application\Investments\DeleteInvestmentExpenseCategory\DeleteInvestmentExpenseCategoryHandler;
use App\Application\Investments\DeleteInvestmentExpenseCategory\FinancialModelForInvestmentExpenseCategoryDeleteNotFoundException;
use App\Application\Investments\DeleteInvestmentExpenseCategory\InvestmentExpenseCategoryForDeleteNotFoundException;
use App\Controller\Api\BaseApiAbstractController;
use App\Domain\Shared\ValueObject\ShortId;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class DeleteInvestmentExpenseCategoryController extends BaseApiAbstractController
{
    #[Route(
        '/api/models/{financialModelShortId}/initial-investments/categories/{categoryId}',
        name: 'api.initial_investments.categories.delete',
        requirements: [
            'financialModelShortId' => '[23456789abcdefghjkmnpqrstuvwxyz]{10}',
            'categoryId' => '\d+',
        ],
        methods: ['DELETE'],
    )]
    public function __invoke(
        string $financialModelShortId,
        int $categoryId,
        DeleteInvestmentExpenseCategoryHandler $handler,
    ): JsonResponse {
        try {
            $result = $handler->handle(new DeleteInvestmentExpenseCategoryCommand(
                owner: $this->getAuthorizedUser(),
                financialModelShortId: ShortId::fromString($financialModelShortId),
                categoryId: $categoryId,
            ));

            return $this->json([
                'data' => [
                    'id' => $result->categoryId,
                    'isDeleted' => $result->isDeleted,
                ],
            ]);
        } catch (FinancialModelForInvestmentExpenseCategoryDeleteNotFoundException) {
            return $this->json(['error' => 'financial_model_not_found'], JsonResponse::HTTP_NOT_FOUND);
        } catch (InvestmentExpenseCategoryForDeleteNotFoundException) {
            return $this->json(['error' => 'investment_expense_category_not_found'], JsonResponse::HTTP_NOT_FOUND);
        } catch (ArchivedFinancialModelCannotDeleteInvestmentExpenseCategoryException) {
            return $this->json(['error' => 'archived_financial_model_cannot_be_changed'], JsonResponse::HTTP_BAD_REQUEST);
        }
    }
}
