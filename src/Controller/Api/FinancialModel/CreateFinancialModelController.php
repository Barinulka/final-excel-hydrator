<?php

declare(strict_types=1);

namespace App\Controller\Api\FinancialModel;

use App\Application\FinancialModel\CreateFinancialModel\ArchivedProjectCannotCreateFinancialModelException;
use App\Application\FinancialModel\CreateFinancialModel\CreateFinancialModelHandler;
use App\Application\FinancialModel\CreateFinancialModel\CreateFinancialModelShortIdGenerationException;
use App\Application\FinancialModel\CreateFinancialModel\ProjectForFinancialModelNotFoundException;
use App\Controller\Api\BaseApiAbstractController;
use App\Presentation\Api\Mapper\FinancialModel\CreateFinancialModelCommandMapper;
use App\Presentation\Api\Request\FinancialModel\CreateFinancialModelApiRequest;
use App\Presentation\Api\Response\ValidationErrorResponseFactory;
use JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class CreateFinancialModelController extends BaseApiAbstractController
{
    #[Route(
        path: '/api/projects/{projectShortId}/models',
        name: 'api.financial_model.create',
        requirements: [
            'projectShortId' => '[23456789abcdefghjkmnpqrstuvwxyz]{10}',
        ],
        methods: ['POST'],
    )]
    public function create(
        string $projectShortId,
        Request $request,
        ValidatorInterface $validator,
        ValidationErrorResponseFactory $errorResponseFactory,
        CreateFinancialModelCommandMapper $commandMapper,
        CreateFinancialModelHandler $handler,
    ): JsonResponse {
        $owner = $this->getAuthorizedUser();

        try {
            $requestData = $this->getJsonRequestData($request);
        } catch (JsonException) {
            return $this->json(['error' => 'invalid_json'], Response::HTTP_BAD_REQUEST);
        }

        $apiRequest = CreateFinancialModelApiRequest::fromArray($requestData);

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
        } catch (ProjectForFinancialModelNotFoundException) {
            return $this->json(['error' => 'not_found'], Response::HTTP_NOT_FOUND);
        } catch (ArchivedProjectCannotCreateFinancialModelException) {
            return $this->json(['error' => 'project_archived'], Response::HTTP_BAD_REQUEST);
        } catch (CreateFinancialModelShortIdGenerationException) {
            return $this->json(['error' => 'short_id_generation_failed'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $redirectUrl = $this->generateUrl('app_financial_model_edit', [
            'projectShortId' => $result->projectShortId,
            'financialModelShortId' => $result->financialModelShortId,
            'tabKey' => 'input_params',
        ]);

        return $this->json([
            'data' => [
                'projectShortId' => $result->projectShortId,
                'financialModelShortId' => $result->financialModelShortId,
                'title' => $result->title,
                'versionNumber' => $result->versionNumber,
                'redirectUrl' => $redirectUrl,
            ],
        ], Response::HTTP_CREATED);
    }
}
