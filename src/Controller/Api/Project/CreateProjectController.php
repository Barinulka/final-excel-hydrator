<?php

declare(strict_types=1);

namespace App\Controller\Api\Project;

use App\Application\Project\CreateProject\CreateProjectHandler;
use App\Application\Project\CreateProject\CreateProjectShortIdGenerationException;
use App\Controller\Api\BaseApiAbstractController;
use App\Presentation\Api\Mapper\Project\CreateProjectCommandMapper;
use App\Presentation\Api\Request\Project\CreateProjectApiRequest;
use App\Presentation\Api\Response\ValidationErrorResponseFactory;
use JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class CreateProjectController extends BaseApiAbstractController
{
    #[Route(path: '/api/projects', name: 'api.project.create', methods: ['POST'])]
    public function create(
        Request $request,
        ValidatorInterface $validator,
        CreateProjectCommandMapper $commandMapper,
        CreateProjectHandler $handler,
        ValidationErrorResponseFactory $errorResponseFactory,
    ): JsonResponse {
        $owner = $this->getAuthorizedUser();

        try {
            $requestData = $this->getJsonRequestData($request);
        } catch (JsonException) {
            return $this->json(['error' => 'invalid_json'], Response::HTTP_BAD_REQUEST);
        }

        $apiRequest = CreateProjectApiRequest::fromArray($requestData);

        $errors = $validator->validate($apiRequest);
        if (count($errors) > 0) {
            return $errorResponseFactory->create($errors);
        }

        $command = $commandMapper->map(
            owner: $owner,
            apiRequest: $apiRequest,
        );

        try {
            $result = $handler->handle($command);
        } catch (CreateProjectShortIdGenerationException) {
            return $this->json(['error' => 'short_id_generation_failed'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $redirectUrl = $this->generateUrl('app_project_page', [
            'projectShortId' => $result->projectShortId,
        ]);

        return $this->json([
            'data' => [
                'projectShortId' => $result->projectShortId,
                'title' => $result->title,
                'redirectUrl' => $redirectUrl,
            ]
        ], Response::HTTP_CREATED);
    }
}
