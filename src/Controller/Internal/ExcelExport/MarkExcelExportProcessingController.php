<?php

declare(strict_types=1);

namespace App\Controller\Internal\ExcelExport;

use App\Application\ExcelExport\MarkExcelExportProcessing\ExcelExportForProcessingNotFoundException;
use App\Application\ExcelExport\MarkExcelExportProcessing\MarkExcelExportProcessingCommand;
use App\Application\ExcelExport\MarkExcelExportProcessing\MarkExcelExportProcessingHandler;
use App\Presentation\Internal\Response\ExcelExport\ExcelExportWorkerResponseFactory;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MarkExcelExportProcessingController
{
    #[Route(
        path: '/internal/excel-exports/{exportId}/processing',
        name: 'internal.excel_export.mark_processing',
        requirements: ['exportId' => '\d+'],
        methods: ['POST'],
    )]
    public function __invoke(
        int $exportId,
        MarkExcelExportProcessingHandler $handler,
        ExcelExportWorkerResponseFactory $responseFactory,
    ): JsonResponse {
        try {
            $result = $handler->handle(new MarkExcelExportProcessingCommand(
                exportId: $exportId,
            ));
        } catch (ExcelExportForProcessingNotFoundException) {
            return new JsonResponse(['error' => 'not_found'], Response::HTTP_NOT_FOUND);
        } catch (InvalidArgumentException) {
            return new JsonResponse(['error' => 'invalid_state'], Response::HTTP_CONFLICT);
        }

        return new JsonResponse($responseFactory->createProcessing($result), Response::HTTP_OK);
    }
}
