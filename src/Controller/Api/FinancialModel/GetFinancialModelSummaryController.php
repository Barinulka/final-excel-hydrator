<?php

declare(strict_types=1);

namespace App\Controller\Api\FinancialModel;

use App\Application\FinancialModel\BuildFinancialModelSummary\BuildFinancialModelSummaryHandler;
use App\Application\FinancialModel\BuildFinancialModelSummary\BuildFinancialModelSummaryQuery;
use App\Application\FinancialModel\BuildFinancialModelSummary\FinancialModelSummaryNotFoundException;
use App\Controller\Api\BaseApiAbstractController;
use App\Domain\Shared\ValueObject\ShortId;
use App\Presentation\Api\Response\FinancialModel\FinancialModelSummaryApiResponseFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetFinancialModelSummaryController extends BaseApiAbstractController
{
    #[Route(
        path: '/api/models/{financialModelShortId}/summary',
        name: 'api.financial_model.summary',
        requirements: [
            'financialModelShortId' => '[23456789abcdefghjkmnpqrstuvwxyz]{10}',
        ],
        methods: ['GET'],
    )]
    public function __invoke(
        string $financialModelShortId,
        BuildFinancialModelSummaryHandler $handler,
        FinancialModelSummaryApiResponseFactory $responseFactory,
    ): JsonResponse {
        $owner = $this->getAuthorizedUser();

        try {
            $result = $handler->handle(new BuildFinancialModelSummaryQuery(
                owner: $owner,
                financialModelShortId: ShortId::fromString($financialModelShortId),
            ));
        } catch (FinancialModelSummaryNotFoundException) {
            return $this->json(['error' => 'not_found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($responseFactory->create($result), Response::HTTP_OK);
    }
}
