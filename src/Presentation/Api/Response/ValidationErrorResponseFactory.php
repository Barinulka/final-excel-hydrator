<?php

declare(strict_types=1);

namespace App\Presentation\Api\Response;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\ConstraintViolationListInterface;

final class ValidationErrorResponseFactory
{
    /**
     * @param ConstraintViolationListInterface $errors
     *
     * @return JsonResponse
     */
    public function create(ConstraintViolationListInterface $errors): JsonResponse
    {
        $response = [
            'error' => 'validation_failed',
            'fields' => [],
        ];

        foreach ($errors as $error) {
            $response['fields'][$error->getPropertyPath()][] = $error->getMessage();
        }

        return new JsonResponse($response, 422);
    }
}
