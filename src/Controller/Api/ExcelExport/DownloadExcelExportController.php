<?php

declare(strict_types=1);

namespace App\Controller\Api\ExcelExport;

use App\Application\ExcelExport\DownloadExcelExport\DownloadExcelExportHandler;
use App\Application\ExcelExport\DownloadExcelExport\DownloadExcelExportQuery;
use App\Application\ExcelExport\DownloadExcelExport\ExcelExportFileNotFoundException;
use App\Application\ExcelExport\DownloadExcelExport\ExcelExportFileNotReadyException;
use App\Application\ExcelExport\DownloadExcelExport\ExcelExportForDownloadNotFoundException;
use App\Controller\Api\BaseApiAbstractController;
use App\Domain\Shared\ValueObject\ShortId;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

final class DownloadExcelExportController extends BaseApiAbstractController
{
    #[Route(
        path: '/api/models/{financialModelShortId}/exports/excel/{exportId}/download',
        name: 'api.excel_export.download',
        requirements: [
            'financialModelShortId' => '[23456789abcdefghjkmnpqrstuvwxyz]{10}',
            'exportId' => '\d+',
        ],
        methods: ['GET'],
    )]
    #[Route(
        path: '/api/projects/{projectShortId}/models/{financialModelShortId}/exports/excel/{exportId}/download',
        name: 'api.excel_export.download_legacy',
        requirements: [
            'projectShortId' => '[23456789abcdefghjkmnpqrstuvwxyz]{10}',
            'financialModelShortId' => '[23456789abcdefghjkmnpqrstuvwxyz]{10}',
            'exportId' => '\d+',
        ],
        methods: ['GET'],
    )]
    public function __invoke(
        string $financialModelShortId,
        int $exportId,
        DownloadExcelExportHandler $handler,
        ?string $projectShortId = null,
    ): BinaryFileResponse|JsonResponse {
        $owner = $this->getAuthorizedUser();

        try {
            $result = $handler->handle(new DownloadExcelExportQuery(
                owner: $owner,
                financialModelShortId: ShortId::fromString($financialModelShortId),
                exportId: $exportId,
                projectShortId: null === $projectShortId ? null : ShortId::fromString($projectShortId),
            ));
        } catch (ExcelExportForDownloadNotFoundException) {
            return $this->json(['error' => 'not_found'], Response::HTTP_NOT_FOUND);
        } catch (ExcelExportFileNotReadyException) {
            return $this->json(['error' => 'file_not_ready'], Response::HTTP_CONFLICT);
        } catch (ExcelExportFileNotFoundException) {
            return $this->json(['error' => 'file_not_found'], Response::HTTP_NOT_FOUND);
        }

        $response = new BinaryFileResponse($result->absolutePath);
        $response->setContentDisposition(
            disposition: ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            filename: $result->downloadName,
        );

        return $response;
    }
}
