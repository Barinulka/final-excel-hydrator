<?php

declare(strict_types=1);

namespace App\Controller\Api\FinancialModel;

use App\Application\FinancialModel\RenameFinancialModel\ArchivedFinancialModelCannotBeRenamedException;
use App\Application\FinancialModel\RenameFinancialModel\FinancialModelForRenameNotFoundException;
use App\Application\FinancialModel\RenameFinancialModel\RenameFinancialModelHandler;
use App\Controller\Api\BaseApiAbstractController;
use App\Presentation\Api\Mapper\FinancialModel\RenameFinancialModelCommandMapper;
use App\Presentation\Api\Request\FinancialModel\RenameFinancialModelApiRequest;
use App\Presentation\Api\Response\ValidationErrorResponseFactory;
use JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class RenameFinancialModelController extends BaseApiAbstractController
{
    #[Route(
        path: '/api/projects/{projectShortId}/models/{financialModelShortId}/title',
        name: 'api.financial_model.rename',
        requirements: [
            'projectShortId' => '[23456789abcdefghjkmnpqrstuvwxyz]{10}',
            'financialModelShortId' => '[23456789abcdefghjkmnpqrstuvwxyz]{10}',
        ],
        methods: ['PATCH'],
    )]
    public function __invoke(
        string $projectShortId,
        string $financialModelShortId,
        Request $request,
        ValidatorInterface $validator,
        RenameFinancialModelCommandMapper $commandMapper,
        RenameFinancialModelHandler $handler,
        ValidationErrorResponseFactory $errorResponseFactory,
    ): JsonResponse {
        $owner = $this->getAuthorizedUser();

        try {
            $requestData = $this->getJsonRequestData($request);
        } catch (JsonException) {
            return $this->json(['error' => 'invalid_json'], Response::HTTP_BAD_REQUEST);
        }

        $apiRequest = RenameFinancialModelApiRequest::fromArray($requestData);

        $errors = $validator->validate($apiRequest);
        if (count($errors) > 0) {
            return $errorResponseFactory->create($errors);
        }

        $command = $commandMapper->map(
            owner: $owner,
            projectShortId: $projectShortId,
            financialModelShortId: $financialModelShortId,
            apiRequest: $apiRequest,
        );

        try {
            $result = $handler->handle($command);
        } catch (FinancialModelForRenameNotFoundException) {
            return $this->json(['error' => 'not_found'], Response::HTTP_NOT_FOUND);
        } catch (ArchivedFinancialModelCannotBeRenamedException) {
            return $this->json(['error' => 'financial_model_archived'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json([
            'data' => [
                'projectShortId' => $result->projectShortId,
                'financialModelShortId' => $result->financialModelShortId,
                'title' => $result->title,
            ],
        ], Response::HTTP_OK);
    }
}
