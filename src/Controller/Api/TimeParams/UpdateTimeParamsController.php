<?php

declare(strict_types=1);

namespace App\Controller\Api\TimeParams;

use App\Application\TimeParams\UpdateTimeParams\FinancialModelForUpdateTimeParamsNotFoundException;
use App\Application\TimeParams\UpdateTimeParams\UpdateTimeParamsHandler;
use App\Controller\Api\BaseApiAbstractController;
use App\Presentation\Api\Mapper\TimeParams\UpdateTimeParamsCommandMapper;
use App\Presentation\Api\Request\TimeParams\UpdateTimeParamsApiRequest;
use App\Presentation\Api\Response\ValidationErrorResponseFactory;
use JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class UpdateTimeParamsController extends BaseApiAbstractController
{
    #[Route(
        path: '/api/projects/{projectShortId}/models/{financialModelShortId}/time-params',
        name: 'api.time_params.update',
        requirements: [
            'projectShortId' => '[23456789abcdefghjkmnpqrstuvwxyz]{10}',
            'financialModelShortId' => '[23456789abcdefghjkmnpqrstuvwxyz]{10}',
        ],
        methods: ['PUT']
    )]
    public function update(
        string $projectShortId,
        string $financialModelShortId,
        Request $request,
        ValidatorInterface $validator,
        ValidationErrorResponseFactory $errorResponseFactory,
        UpdateTimeParamsCommandMapper $commandMapper,
        UpdateTimeParamsHandler $handler,
    ): JsonResponse {
        $owner = $this->getAuthorizedUser();

        try {
            $requestData = $this->getJsonRequestData($request);
        } catch (JsonException) {
            return $this->json(['error' => 'invalid_json'], Response::HTTP_BAD_REQUEST);
        }

        $apiRequest = UpdateTimeParamsApiRequest::fromArray($requestData);

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
        } catch (FinancialModelForUpdateTimeParamsNotFoundException) {
            return $this->json(['error' => 'not_found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'data' => [
                'investmentStartMonth' => $result->investmentStartMonth,
                'investmentDurationMonths' => $result->investmentDurationMonths,
                'commercialOperationDurationMonths' => $result->commercialOperationDurationMonths,
                'forecastStep' => $result->forecastStep,
            ],
        ], Response::HTTP_OK);
    }
}
