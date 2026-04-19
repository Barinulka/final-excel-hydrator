<?php

declare(strict_types=1);

namespace App\Controller\Api\Project;

use App\Application\Project\UpdateProjectDetails\ArchivedProjectCannotBeUpdatedException;
use App\Application\Project\UpdateProjectDetails\ProjectForUpdateNotFoundException;
use App\Application\Project\UpdateProjectDetails\UpdateProjectDetailsHandler;
use App\Controller\Api\BaseApiAbstractController;
use App\Presentation\Api\Mapper\Project\UpdateProjectDetailsCommandMapper;
use App\Presentation\Api\Request\Project\UpdateProjectDetailsApiRequest;
use App\Presentation\Api\Response\ValidationErrorResponseFactory;
use JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class UpdateProjectDetailsController extends BaseApiAbstractController
{
    #[Route(
        path: '/api/projects/{projectShortId}',
        name: 'api.project.update_details',
        requirements: [
            'projectShortId' => '[23456789abcdefghjkmnpqrstuvwxyz]{10}',
        ],
        methods: ['PATCH'],
    )]
    public function __invoke(
        string $projectShortId,
        Request $request,
        ValidatorInterface $validator,
        UpdateProjectDetailsCommandMapper $commandMapper,
        UpdateProjectDetailsHandler $handler,
        ValidationErrorResponseFactory $errorResponseFactory,
    ): JsonResponse {
        $owner = $this->getAuthorizedUser();

        try {
            $requestData = $this->getJsonRequestData($request);
        } catch (JsonException) {
            return $this->json(['error' => 'invalid_json'], Response::HTTP_BAD_REQUEST);
        }

        $apiRequest = UpdateProjectDetailsApiRequest::fromArray($requestData);

        $errors = $validator->validate($apiRequest);
        if (count($errors) > 0) {
            return $errorResponseFactory->create($errors);
        }

        $command = $commandMapper->map(
            owner: $owner,
            projectShortId: $projectShortId,
            apiRequest: $apiRequest,
        );

        try {
            $result = $handler->handle($command);
        } catch (ProjectForUpdateNotFoundException) {
            return $this->json(['error' => 'not_found'], Response::HTTP_NOT_FOUND);
        } catch (ArchivedProjectCannotBeUpdatedException) {
            return $this->json(['error' => 'project_archived'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json([
            'data' => [
                'projectShortId' => $result->projectShortId,
                'title' => $result->title,
                'description' => $result->description,
            ],
        ], Response::HTTP_OK);
    }
}
