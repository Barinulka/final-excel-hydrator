<?php

declare(strict_types=1);

namespace App\Controller\Internal\ExcelExport;

use App\Application\ExcelExport\MarkExcelExportFailed\ExcelExportForFailureNotFoundException;
use App\Application\ExcelExport\MarkExcelExportFailed\MarkExcelExportFailedCommand;
use App\Application\ExcelExport\MarkExcelExportFailed\MarkExcelExportFailedHandler;
use App\Presentation\Internal\Response\ExcelExport\ExcelExportWorkerResponseFactory;
use InvalidArgumentException;
use JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MarkExcelExportFailedController
{
    #[Route(
        path: '/internal/excel-exports/{exportId}/failed',
        name: 'internal.excel_export.mark_failed',
        requirements: ['exportId' => '\d+'],
        methods: ['POST'],
    )]
    public function __invoke(
        int $exportId,
        Request $request,
        MarkExcelExportFailedHandler $handler,
        ExcelExportWorkerResponseFactory $responseFactory,
    ): JsonResponse {
        $payload = $this->readJsonPayload($request);
        $errorMessage = $payload['errorMessage'] ?? null;

        if (!is_string($errorMessage) || trim($errorMessage) === '') {
            return new JsonResponse(['error' => 'invalid_payload'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $result = $handler->handle(new MarkExcelExportFailedCommand(
                exportId: $exportId,
                errorMessage: $errorMessage,
            ));
        } catch (ExcelExportForFailureNotFoundException) {
            return new JsonResponse(['error' => 'not_found'], Response::HTTP_NOT_FOUND);
        } catch (InvalidArgumentException) {
            return new JsonResponse(['error' => 'invalid_state'], Response::HTTP_CONFLICT);
        }

        return new JsonResponse($responseFactory->createFailed($result), Response::HTTP_OK);
    }

    private function readJsonPayload(Request $request): array
    {
        try {
            $payload = json_decode($request->getContent(), true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return [];
        }

        return is_array($payload) ? $payload : [];
    }
}
