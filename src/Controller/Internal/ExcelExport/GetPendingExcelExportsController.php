<?php

declare(strict_types=1);

namespace App\Controller\Internal\ExcelExport;

use App\Application\ExcelExport\GetPendingExcelExportsForProcessing\GetPendingExcelExportsForProcessingHandler;
use App\Application\ExcelExport\GetPendingExcelExportsForProcessing\GetPendingExcelExportsForProcessingQuery;
use App\Presentation\Internal\Response\ExcelExport\ExcelExportWorkerResponseFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetPendingExcelExportsController
{
    #[Route(
        path: '/internal/excel-exports/pending',
        name: 'internal.excel_export.pending',
        methods: ['GET'],
    )]
    public function __invoke(
        Request $request,
        GetPendingExcelExportsForProcessingHandler $handler,
        ExcelExportWorkerResponseFactory $responseFactory,
    ): JsonResponse {
        $result = $handler->handle(new GetPendingExcelExportsForProcessingQuery(
            limit: $request->query->getInt('limit', 10),
        ));

        return new JsonResponse($responseFactory->createPendingList($result), Response::HTTP_OK);
    }
}
