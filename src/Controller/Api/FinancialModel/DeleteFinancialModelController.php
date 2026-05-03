<?php

declare(strict_types=1);

namespace App\Controller\Api\FinancialModel;

use App\Application\FinancialModel\DeleteFinancialModel\ActiveFinancialModelCannotBeDeletedException;
use App\Application\FinancialModel\DeleteFinancialModel\DeleteFinancialModelCommand;
use App\Application\FinancialModel\DeleteFinancialModel\DeleteFinancialModelHandler;
use App\Application\FinancialModel\DeleteFinancialModel\FinancialModelForDeleteNotFoundException;
use App\Controller\Api\BaseApiAbstractController;
use App\Domain\Shared\ValueObject\ShortId;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DeleteFinancialModelController extends BaseApiAbstractController
{
    #[Route(
        path: '/api/models/{financialModelShortId}',
        name: 'api.financial_model.delete',
        requirements: [
            'financialModelShortId' => '[23456789abcdefghjkmnpqrstuvwxyz]{10}',
        ],
        methods: ['DELETE'],
    )]
    #[Route(
        path: '/api/projects/{projectShortId}/models/{financialModelShortId}',
        name: 'api.financial_model.delete_legacy',
        requirements: [
            'projectShortId' => '[23456789abcdefghjkmnpqrstuvwxyz]{10}',
            'financialModelShortId' => '[23456789abcdefghjkmnpqrstuvwxyz]{10}',
        ],
        methods: ['DELETE'],
    )]
    public function __invoke(
        string $financialModelShortId,
        DeleteFinancialModelHandler $handler,
        ?string $projectShortId = null,
    ): JsonResponse {
        $owner = $this->getAuthorizedUser();

        $command = new DeleteFinancialModelCommand(
            owner: $owner,
            financialModelShortId: ShortId::fromString($financialModelShortId),
            projectShortId: null === $projectShortId ? null : ShortId::fromString($projectShortId),
        );

        try {
            $result = $handler->handle($command);
        } catch (FinancialModelForDeleteNotFoundException) {
            return $this->json(['error' => 'not_found'], Response::HTTP_NOT_FOUND);
        } catch (ActiveFinancialModelCannotBeDeletedException) {
            return $this->json(['error' => 'active_financial_model_cannot_be_deleted'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json([
            'data' => [
                'financialModelShortId' => $result->financialModelShortId,
                'isDeleted' => $result->isDeleted,
            ],
        ], Response::HTTP_OK);
    }
}
