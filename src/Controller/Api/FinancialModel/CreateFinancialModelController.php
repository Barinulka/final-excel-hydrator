<?php

declare(strict_types=1);

namespace App\Controller\Api\FinancialModel;

use App\Application\FinancialModel\CreateFinancialModel\CreateFinancialModelHandler;
use App\Application\FinancialModel\CreateFinancialModel\CreateFinancialModelShortIdGenerationException;
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
        path: '/api/models',
        name: 'api.financial_model.create',
        methods: ['POST'],
    )]
    public function create(
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
            apiRequest: $apiRequest,
        );

        try {
            $result = $handler->handle($command);
        } catch (CreateFinancialModelShortIdGenerationException) {
            return $this->json(['error' => 'short_id_generation_failed'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $redirectUrl = $this->generateUrl('app_financial_model_edit', [
            'financialModelShortId' => $result->financialModelShortId,
            'tabKey' => 'input_params',
        ]);

        return $this->json([
            'data' => [
                'financialModelShortId' => $result->financialModelShortId,
                'title' => $result->title,
                'versionNumber' => $result->versionNumber,
                'redirectUrl' => $redirectUrl,
            ],
        ], Response::HTTP_CREATED);
    }
}
