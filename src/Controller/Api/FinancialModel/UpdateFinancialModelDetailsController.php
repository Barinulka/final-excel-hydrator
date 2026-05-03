<?php

declare(strict_types=1);

namespace App\Controller\Api\FinancialModel;

use App\Application\FinancialModel\UpdateFinancialModelDetails\ArchivedFinancialModelCannotBeUpdatedDetailsException;
use App\Application\FinancialModel\UpdateFinancialModelDetails\FinancialModelForUpdateDetailsNotFoundException;
use App\Application\FinancialModel\UpdateFinancialModelDetails\UpdateFinancialModelDetailsHandler;
use App\Controller\Api\BaseApiAbstractController;
use App\Presentation\Api\Mapper\FinancialModel\UpdateFinancialModelDetailsCommandMapper;
use App\Presentation\Api\Request\FinancialModel\UpdateFinancialModelDetailsApiRequest;
use App\Presentation\Api\Response\ValidationErrorResponseFactory;
use JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class UpdateFinancialModelDetailsController extends BaseApiAbstractController
{
    #[Route(
        path: '/api/models/{financialModelShortId}/details',
        name: 'api.financial_model_details.update',
        requirements: [
            'financialModelShortId' => '[23456789abcdefghjkmnpqrstuvwxyz]{10}',
        ],
        methods: ['PATCH'],
    )]
    public function __invoke(
        string $financialModelShortId,
        Request $request,
        ValidatorInterface $validator,
        UpdateFinancialModelDetailsCommandMapper $commandMapper,
        UpdateFinancialModelDetailsHandler $handler,
        ValidationErrorResponseFactory $errorResponseFactory
    ): JsonResponse {
        $owner = $this->getAuthorizedUser();

        try {
            $requestData = $this->getJsonRequestData($request);
        } catch (JsonException) {
            return $this->json(['error' => 'invalid_json'], Response::HTTP_BAD_REQUEST);
        }

        $apiRequest = UpdateFinancialModelDetailsApiRequest::fromArray($requestData);

        $errors = $validator->validate($apiRequest);
        if (count($errors) > 0) {
            return $errorResponseFactory->create($errors);
        }

        $command = $commandMapper->map(
            owner: $owner,
            financialModelShortId: $financialModelShortId,
            apiRequest: $apiRequest,
        );

        try {
            $result = $handler->handle($command);
        } catch(FinancialModelForUpdateDetailsNotFoundException) {
            return $this->json(['error' => 'not_found'], Response::HTTP_NOT_FOUND);
        } catch(ArchivedFinancialModelCannotBeUpdatedDetailsException) {
            return $this->json(['error' => 'financial_model_archived'], Response::HTTP_BAD_REQUEST);
        }


        return $this->json([
            'data' => [
                'financialModelShortId' => $result->financialModelShortId,
                'title' => $result->title,
                'description' => $result->description,
            ],
        ], Response::HTTP_OK);
    }
}
