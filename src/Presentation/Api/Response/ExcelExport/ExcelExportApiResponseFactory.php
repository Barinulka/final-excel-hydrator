<?php

declare(strict_types=1);

namespace App\Presentation\Api\Response\ExcelExport;

use App\Application\ExcelExport\CreateExcelExport\CreateExcelExportResult;

final readonly class ExcelExportApiResponseFactory
{
    public function create(CreateExcelExportResult $result): array
    {
        return [
            'data' => [
                'export' => [
                    'id' => $result->exportId,
                    'projectShortId' => $result->projectShortId,
                    'financialModelShortId' => $result->financialModelShortId,
                    'status' => $result->status,
                ],
            ],
        ];
    }
}
