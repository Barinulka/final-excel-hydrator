<?php

declare(strict_types=1);

namespace App\Controller\Api\Investments;

use App\Application\Investments\AddInvestmentExpenseCategory\AddInvestmentExpenseCategoryHandler;
use App\Controller\Api\BaseApiAbstractController;
use App\Domain\Shared\ValueObject\ShortId;
use App\Entity\User;
use App\Presentation\Api\Mapper\Investments\AddInvestmentExpenseCategoryCommandMapper;
use App\Presentation\Api\Request\Investments\AddInvestmentExpenseCategoryApiRequest;
use App\Presentation\Api\Response\Investments\InvestmentExpenseCategoryApiResponseFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final class AddInvestmentExpenseCategoryController extends BaseApiAbstractController
{
    #[Route(
        path: '/api/models/{financialModelShortId}/initial-investments/categories',
        name: 'api.initial_investments.categories.add',
        requirements: ['financialModelShortId' => '[23456789abcdefghjkmnpqrstuvwxyz]{10}'],
        methods: ['POST'],
    )]
    public function __invoke(
        string $financialModelShortId,
        #[MapRequestPayload] AddInvestmentExpenseCategoryApiRequest $request,
        AddInvestmentExpenseCategoryCommandMapper $mapper,
        AddInvestmentExpenseCategoryHandler $handler,
        InvestmentExpenseCategoryApiResponseFactory $responseFactory,
    ): JsonResponse {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->json(['error' => 'unauthorized'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $command = $mapper->map(
            owner: $user,
            financialModelShortId: ShortId::fromString($financialModelShortId),
            request: $request,
        );

        $result = $handler->handle($command);

        return $this->json(
            data: $responseFactory->fromAddResult($result),
            status: JsonResponse::HTTP_CREATED,
        );
    }
}
