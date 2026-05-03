<?php

declare(strict_types=1);

namespace App\Controller\Api\FinancialModel;

use App\Application\FinancialModel\ArchiveFinancialModel\ArchiveFinancialModelCommand;
use App\Application\FinancialModel\ArchiveFinancialModel\ArchiveFinancialModelHandler;
use App\Application\FinancialModel\ArchiveFinancialModel\FinancialModelAlreadyArchivedException;
use App\Application\FinancialModel\ArchiveFinancialModel\FinancialModelForArchiveNotFoundException;
use App\Controller\Api\BaseApiAbstractController;
use App\Domain\Shared\ValueObject\ShortId;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ArchiveFinancialModelController extends BaseApiAbstractController
{
    #[Route(
        path: '/api/models/{financialModelShortId}/archive',
        name: 'api.financial_model.archive',
        requirements: [
            'financialModelShortId' => '[23456789abcdefghjkmnpqrstuvwxyz]{10}',
        ],
        methods: ['PATCH'],
    )]
    #[Route(
        path: '/api/projects/{projectShortId}/models/{financialModelShortId}/archive',
        name: 'api.financial_model.archive_legacy',
        requirements: [
            'projectShortId' => '[23456789abcdefghjkmnpqrstuvwxyz]{10}',
            'financialModelShortId' => '[23456789abcdefghjkmnpqrstuvwxyz]{10}',
        ],
        methods: ['PATCH'],
    )]
    public function __invoke(
        string $financialModelShortId,
        ArchiveFinancialModelHandler $handler,
        ?string $projectShortId = null,
    ): JsonResponse {
        $owner = $this->getAuthorizedUser();

        $command = new ArchiveFinancialModelCommand(
            owner: $owner,
            financialModelShortId: ShortId::fromString($financialModelShortId),
            projectShortId: null === $projectShortId ? null : ShortId::fromString($projectShortId),
        );

        try {
            $result = $handler->handle($command);
        } catch (FinancialModelForArchiveNotFoundException) {
            return $this->json(['error' => 'not_found'], Response::HTTP_NOT_FOUND);
        } catch (FinancialModelAlreadyArchivedException) {
            return $this->json(['error' => 'financial_model_already_archived'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json([
            'data' => [
                'financialModelShortId' => $result->financialModelShortId,
                'status' => $result->status,
                'isArchived' => $result->isArchived,
                'archivedAt' => $result->archivedAt,
            ]
        ], Response::HTTP_OK);
    }
}
