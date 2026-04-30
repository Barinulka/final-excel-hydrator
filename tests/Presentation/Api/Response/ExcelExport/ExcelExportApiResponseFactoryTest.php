<?php

declare(strict_types=1);

namespace App\Tests\Presentation\Api\Response\ExcelExport;

use App\Application\ExcelExport\CreateExcelExport\CreateExcelExportResult;
use App\Application\ExcelExport\GetExcelExportsForModel\ExcelExportListItem;
use App\Application\ExcelExport\GetExcelExportsForModel\GetExcelExportsForModelResult;
use App\Presentation\Api\Response\ExcelExport\ExcelExportApiResponseFactory;
use PHPUnit\Framework\TestCase;

final class ExcelExportApiResponseFactoryTest extends TestCase
{
    public function testCreatesExcelExportApiResponse(): void
    {
        $factory = new ExcelExportApiResponseFactory();

        $response = $factory->create(new CreateExcelExportResult(
            exportId: 15,
            projectShortId: '23456789ab',
            financialModelShortId: 'ab23456789',
            status: 'pending',
        ));

        self::assertSame([
            'data' => [
                'export' => [
                    'id' => 15,
                    'projectShortId' => '23456789ab',
                    'financialModelShortId' => 'ab23456789',
                    'status' => 'pending',
                ],
            ],
        ], $response);
    }

    public function testAllowsNullExportId(): void
    {
        $factory = new ExcelExportApiResponseFactory();

        $response = $factory->create(new CreateExcelExportResult(
            exportId: null,
            projectShortId: '23456789ab',
            financialModelShortId: 'ab23456789',
            status: 'pending',
        ));

        self::assertNull($response['data']['export']['id']);
    }

    public function testCreatesExcelExportListApiResponse(): void
    {
        $factory = new ExcelExportApiResponseFactory();

        $response = $factory->createList(new GetExcelExportsForModelResult(
            exports: [
                new ExcelExportListItem(
                    id: 15,
                    status: 'completed',
                    filePath: '/exports/model.xlsx',
                    errorMessage: null,
                    createdAt: '2026-04-21T10:00:00+00:00',
                    startedAt: '2026-04-21T10:01:00+00:00',
                    completedAt: '2026-04-21T10:02:00+00:00',
                    failedAt: null,
                ),
            ],
        ));

        self::assertSame([
            'data' => [
                'exports' => [
                    [
                        'id' => 15,
                        'status' => 'completed',
                        'filePath' => '/exports/model.xlsx',
                        'errorMessage' => null,
                        'createdAt' => '2026-04-21T10:00:00+00:00',
                        'startedAt' => '2026-04-21T10:01:00+00:00',
                        'completedAt' => '2026-04-21T10:02:00+00:00',
                        'failedAt' => null,
                    ],
                ],
            ],
        ], $response);
    }
}
