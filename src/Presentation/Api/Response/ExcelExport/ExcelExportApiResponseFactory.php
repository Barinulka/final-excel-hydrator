<?php

declare(strict_types=1);

namespace App\Presentation\Api\Response\ExcelExport;

use App\Application\ExcelExport\CreateExcelExport\CreateExcelExportResult;
use App\Application\ExcelExport\GetExcelExportsForModel\ExcelExportListItem;
use App\Application\ExcelExport\GetExcelExportsForModel\GetExcelExportsForModelResult;

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

    public function createList(GetExcelExportsForModelResult $result): array
    {
        return [
            'data' => [
                'exports' => array_map(
                    static fn (ExcelExportListItem $export): array => [
                        'id' => $export->id,
                        'status' => $export->status,
                        'filePath' => $export->filePath,
                        'errorMessage' => $export->errorMessage,
                        'createdAt' => $export->createdAt,
                        'startedAt' => $export->startedAt,
                        'completedAt' => $export->completedAt,
                        'failedAt' => $export->failedAt,
                    ],
                    $result->exports,
                ),
            ],
        ];
    }
}
