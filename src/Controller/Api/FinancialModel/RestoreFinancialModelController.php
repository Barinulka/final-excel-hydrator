<?php

declare(strict_types=1);

namespace App\Controller\Api\FinancialModel;

use App\Application\FinancialModel\RestoreFinancialModel\FinancialModelAlreadyActiveException;
use App\Application\FinancialModel\RestoreFinancialModel\FinancialModelForRestoreNotFoundException;
use App\Application\FinancialModel\RestoreFinancialModel\RestoreFinancialModelCommand;
use App\Application\FinancialModel\RestoreFinancialModel\RestoreFinancialModelHandler;
use App\Controller\Api\BaseApiAbstractController;
use App\Domain\Shared\ValueObject\ShortId;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RestoreFinancialModelController extends BaseApiAbstractController
{
    #[Route(
        path: '/api/models/{financialModelShortId}/restore',
        name: 'api.financial_model.restore',
        requirements: [
            'financialModelShortId' => '[23456789abcdefghjkmnpqrstuvwxyz]{10}',
        ],
        methods: ['PATCH'],
    )]
    #[Route(
        path: '/api/projects/{projectShortId}/models/{financialModelShortId}/restore',
        name: 'api.financial_model.restore_legacy',
        requirements: [
            'projectShortId' => '[23456789abcdefghjkmnpqrstuvwxyz]{10}',
            'financialModelShortId' => '[23456789abcdefghjkmnpqrstuvwxyz]{10}',
        ],
        methods: ['PATCH'],
    )]
    public function __invoke(
        string $financialModelShortId,
        RestoreFinancialModelHandler $handler,
        ?string $projectShortId = null,
    ): JsonResponse {
        $owner = $this->getAuthorizedUser();

        $command = new RestoreFinancialModelCommand(
            owner: $owner,
            financialModelShortId: ShortId::fromString($financialModelShortId),
            projectShortId: null === $projectShortId ? null : ShortId::fromString($projectShortId),
        );

        try {
            $result = $handler->handle($command);
        } catch (FinancialModelForRestoreNotFoundException) {
            return $this->json(['error' => 'not_found'], Response::HTTP_NOT_FOUND);
        } catch (FinancialModelAlreadyActiveException) {
            return $this->json(['error' => 'financial_model_already_active'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json([
            'data' => [
                'financialModelShortId' => $result->financialModelShortId,
                'status' => $result->status,
                'isArchived' => $result->isArchived,
                'archivedAt' => $result->archivedAt,
            ],
        ], Response::HTTP_OK);
    }
}
