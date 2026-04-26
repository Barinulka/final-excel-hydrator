<?php

declare(strict_types=1);

namespace App\Controller\Api\Project;

use App\Application\Project\ArchiveProject\ArchiveProjectCommand;
use App\Application\Project\ArchiveProject\ArchiveProjectHandler;
use App\Application\Project\ArchiveProject\ProjectAlreadyArchivedException;
use App\Application\Project\ArchiveProject\ProjectForArchiveNotFoundException;
use App\Controller\Api\BaseApiAbstractController;
use App\Domain\Shared\ValueObject\ShortId;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ArchiveProjectController extends BaseApiAbstractController
{
    #[Route(
        path: '/api/projects/{projectShortId}/archive',
        name: 'api.project.archive',
        requirements: [
            'projectShortId' => '[23456789abcdefghjkmnpqrstuvwxyz]{10}',
        ],
        methods: ['PATCH'],
    )]
    public function __invoke(
        string $projectShortId,
        ArchiveProjectHandler $handler,
    ): JsonResponse {
        $owner = $this->getAuthorizedUser();

        $command = new ArchiveProjectCommand(
            owner: $owner,
            projectShortId: ShortId::fromString($projectShortId),
        );

        try {
            $result = $handler->handle($command);
        } catch (ProjectForArchiveNotFoundException) {
            return $this->json(['error' => 'not_found'], Response::HTTP_NOT_FOUND);
        } catch (ProjectAlreadyArchivedException) {
            return $this->json(['error' => 'project_already_archived'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json([
            'data' => [
                'projectShortId' => $result->projectShortId,
                'status' => $result->status,
                'isArchived' => $result->isArchived,
                'archivedAt' => $result->archivedAt,
            ]
        ], Response::HTTP_OK);
    }
}
