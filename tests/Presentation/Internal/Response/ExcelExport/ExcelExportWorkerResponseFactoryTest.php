<?php

declare(strict_types=1);

namespace App\Tests\Presentation\Internal\Response\ExcelExport;

use App\Application\ExcelExport\GetPendingExcelExportsForProcessing\GetPendingExcelExportsForProcessingResult;
use App\Application\ExcelExport\GetPendingExcelExportsForProcessing\PendingExcelExportItem;
use App\Application\ExcelExport\MarkExcelExportCompleted\MarkExcelExportCompletedResult;
use App\Application\ExcelExport\MarkExcelExportFailed\MarkExcelExportFailedResult;
use App\Application\ExcelExport\MarkExcelExportProcessing\MarkExcelExportProcessingResult;
use App\Presentation\Internal\Response\ExcelExport\ExcelExportWorkerResponseFactory;
use PHPUnit\Framework\TestCase;

final class ExcelExportWorkerResponseFactoryTest extends TestCase
{
    public function testCreatesPendingListResponse(): void
    {
        $factory = new ExcelExportWorkerResponseFactory();

        $response = $factory->createPendingList(new GetPendingExcelExportsForProcessingResult(
            exports: [
                new PendingExcelExportItem(
                    id: 15,
                    status: 'pending',
                    calculationResultPayload: [
                        'tables' => [],
                        'metrics' => [],
                        'warnings' => [],
                    ],
                    createdAt: '2026-05-01T10:00:00+00:00',
                ),
            ],
        ));

        self::assertSame([
            'data' => [
                'exports' => [
                    [
                        'id' => 15,
                        'status' => 'pending',
                        'calculationResultPayload' => [
                            'tables' => [],
                            'metrics' => [],
                            'warnings' => [],
                        ],
                        'createdAt' => '2026-05-01T10:00:00+00:00',
                    ],
                ],
            ],
        ], $response);
    }

    public function testCreatesProcessingResponse(): void
    {
        $factory = new ExcelExportWorkerResponseFactory();

        $response = $factory->createProcessing(new MarkExcelExportProcessingResult(
            exportId: 15,
            status: 'processing',
            startedAt: '2026-05-01T10:01:00+00:00',
        ));

        self::assertSame([
            'data' => [
                'export' => [
                    'id' => 15,
                    'status' => 'processing',
                    'startedAt' => '2026-05-01T10:01:00+00:00',
                ],
            ],
        ], $response);
    }

    public function testCreatesCompletedResponse(): void
    {
        $factory = new ExcelExportWorkerResponseFactory();

        $response = $factory->createCompleted(new MarkExcelExportCompletedResult(
            exportId: 15,
            status: 'completed',
            filePath: 'exports/model.xlsx',
            completedAt: '2026-05-01T10:02:00+00:00',
        ));

        self::assertSame([
            'data' => [
                'export' => [
                    'id' => 15,
                    'status' => 'completed',
                    'filePath' => 'exports/model.xlsx',
                    'completedAt' => '2026-05-01T10:02:00+00:00',
                ],
            ],
        ], $response);
    }

    public function testCreatesFailedResponse(): void
    {
        $factory = new ExcelExportWorkerResponseFactory();

        $response = $factory->createFailed(new MarkExcelExportFailedResult(
            exportId: 15,
            status: 'failed',
            errorMessage: 'Go worker timeout',
            failedAt: '2026-05-01T10:02:00+00:00',
        ));

        self::assertSame([
            'data' => [
                'export' => [
                    'id' => 15,
                    'status' => 'failed',
                    'errorMessage' => 'Go worker timeout',
                    'failedAt' => '2026-05-01T10:02:00+00:00',
                ],
            ],
        ], $response);
    }
}
