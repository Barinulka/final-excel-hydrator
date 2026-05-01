<?php

declare(strict_types=1);

namespace App\Controller\Internal\ExcelExport;

use App\Application\ExcelExport\MarkExcelExportCompleted\ExcelExportForCompletionNotFoundException;
use App\Application\ExcelExport\MarkExcelExportCompleted\MarkExcelExportCompletedCommand;
use App\Application\ExcelExport\MarkExcelExportCompleted\MarkExcelExportCompletedHandler;
use App\Presentation\Internal\Response\ExcelExport\ExcelExportWorkerResponseFactory;
use InvalidArgumentException;
use JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MarkExcelExportCompletedController
{
    #[Route(
        path: '/internal/excel-exports/{exportId}/completed',
        name: 'internal.excel_export.mark_completed',
        requirements: ['exportId' => '\d+'],
        methods: ['POST'],
    )]
    public function __invoke(
        int $exportId,
        Request $request,
        MarkExcelExportCompletedHandler $handler,
        ExcelExportWorkerResponseFactory $responseFactory,
    ): JsonResponse {
        $payload = $this->readJsonPayload($request);
        $filePath = $payload['filePath'] ?? null;

        if (!is_string($filePath) || trim($filePath) === '') {
            return new JsonResponse(['error' => 'invalid_payload'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $result = $handler->handle(new MarkExcelExportCompletedCommand(
                exportId: $exportId,
                filePath: $filePath,
            ));
        } catch (ExcelExportForCompletionNotFoundException) {
            return new JsonResponse(['error' => 'not_found'], Response::HTTP_NOT_FOUND);
        } catch (InvalidArgumentException) {
            return new JsonResponse(['error' => 'invalid_state'], Response::HTTP_CONFLICT);
        }

        return new JsonResponse($responseFactory->createCompleted($result), Response::HTTP_OK);
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
