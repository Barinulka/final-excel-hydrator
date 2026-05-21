<?php

declare(strict_types=1);

namespace App\Controller\Api\FinancialModel;

use App\Application\Calculation\BuildFinancialModelCalculation\BuildFinancialModelCalculationHandler;
use App\Application\Calculation\BuildFinancialModelCalculation\BuildFinancialModelCalculationQuery;
use App\Application\Calculation\BuildFinancialModelCalculation\FinancialModelForCalculationNotFoundException;
use App\Controller\Api\BaseApiAbstractController;
use App\Domain\Shared\ValueObject\ShortId;
use App\Presentation\Api\Response\Calculation\CalculationResultApiResponseFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PreviewFinancialModelController extends BaseApiAbstractController
{
    #[Route(
        path: '/api/models/{financialModelShortId}/preview',
        name: 'api.financial_model.preview',
        requirements: [
            'financialModelShortId' => '[23456789abcdefghjkmnpqrstuvwxyz]{10}',
        ],
        methods: ['POST'],
    )]
    public function __invoke(
        string $financialModelShortId,
        BuildFinancialModelCalculationHandler $handler,
        CalculationResultApiResponseFactory $responseFactory,
    ): JsonResponse {
        $owner = $this->getAuthorizedUser();

        try {
            $result = $handler->handle(new BuildFinancialModelCalculationQuery(
                owner: $owner,
                financialModelShortId: ShortId::fromString($financialModelShortId),
            ));
        } catch (FinancialModelForCalculationNotFoundException) {
            return $this->json(['error' => 'not_found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($responseFactory->create($result), Response::HTTP_OK);
    }
}
