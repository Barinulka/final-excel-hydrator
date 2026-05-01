<?php

declare(strict_types=1);

namespace App\Presentation\Internal\Response\ExcelExport;

use App\Application\ExcelExport\GetPendingExcelExportsForProcessing\GetPendingExcelExportsForProcessingResult;
use App\Application\ExcelExport\GetPendingExcelExportsForProcessing\PendingExcelExportItem;
use App\Application\ExcelExport\MarkExcelExportCompleted\MarkExcelExportCompletedResult;
use App\Application\ExcelExport\MarkExcelExportFailed\MarkExcelExportFailedResult;
use App\Application\ExcelExport\MarkExcelExportProcessing\MarkExcelExportProcessingResult;

final readonly class ExcelExportWorkerResponseFactory
{
    public function createPendingList(GetPendingExcelExportsForProcessingResult $result): array
    {
        return [
            'data' => [
                'exports' => array_map(
                    static fn (PendingExcelExportItem $export): array => [
                        'id' => $export->id,
                        'status' => $export->status,
                        'calculationResultPayload' => $export->calculationResultPayload,
                        'createdAt' => $export->createdAt,
                    ],
                    $result->exports,
                ),
            ],
        ];
    }

    public function createProcessing(MarkExcelExportProcessingResult $result): array
    {
        return [
            'data' => [
                'export' => [
                    'id' => $result->exportId,
                    'status' => $result->status,
                    'startedAt' => $result->startedAt,
                ],
            ],
        ];
    }

    public function createCompleted(MarkExcelExportCompletedResult $result): array
    {
        return [
            'data' => [
                'export' => [
                    'id' => $result->exportId,
                    'status' => $result->status,
                    'filePath' => $result->filePath,
                    'completedAt' => $result->completedAt,
                ],
            ],
        ];
    }

    public function createFailed(MarkExcelExportFailedResult $result): array
    {
        return [
            'data' => [
                'export' => [
                    'id' => $result->exportId,
                    'status' => $result->status,
                    'errorMessage' => $result->errorMessage,
                    'failedAt' => $result->failedAt,
                ],
            ],
        ];
    }
}
