<?php

declare(strict_types=1);

namespace App\Controller\Api\ExcelExport;

use App\Application\ExcelExport\GetExcelExportsForModel\FinancialModelForExcelExportsNotFoundException;
use App\Application\ExcelExport\GetExcelExportsForModel\GetExcelExportsForModelHandler;
use App\Application\ExcelExport\GetExcelExportsForModel\GetExcelExportsForModelQuery;
use App\Controller\Api\BaseApiAbstractController;
use App\Domain\Shared\ValueObject\ShortId;
use App\Presentation\Api\Response\ExcelExport\ExcelExportApiResponseFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetExcelExportsForModelController extends BaseApiAbstractController
{
    #[Route(
        path: '/api/models/{financialModelShortId}/exports/excel',
        name: 'api.excel_export.list',
        requirements: [
            'financialModelShortId' => '[23456789abcdefghjkmnpqrstuvwxyz]{10}',
        ],
        methods: ['GET'],
    )]
    #[Route(
        path: '/api/projects/{projectShortId}/models/{financialModelShortId}/exports/excel',
        name: 'api.excel_export.list_legacy',
        requirements: [
            'projectShortId' => '[23456789abcdefghjkmnpqrstuvwxyz]{10}',
            'financialModelShortId' => '[23456789abcdefghjkmnpqrstuvwxyz]{10}',
        ],
        methods: ['GET'],
    )]
    public function __invoke(
        string $financialModelShortId,
        GetExcelExportsForModelHandler $handler,
        ExcelExportApiResponseFactory $responseFactory,
        ?string $projectShortId = null,
    ): JsonResponse {
        $owner = $this->getAuthorizedUser();

        try {
            $result = $handler->handle(new GetExcelExportsForModelQuery(
                owner: $owner,
                financialModelShortId: ShortId::fromString($financialModelShortId),
                projectShortId: null === $projectShortId ? null : ShortId::fromString($projectShortId),
            ));
        } catch (FinancialModelForExcelExportsNotFoundException) {
            return $this->json(['error' => 'not_found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($responseFactory->createList(
            result: $result,
            financialModelShortId: $financialModelShortId,
        ), Response::HTTP_OK);
    }
}
