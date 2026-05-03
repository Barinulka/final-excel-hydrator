<?php

declare(strict_types=1);

namespace App\Tests\Presentation\Api\Response\ExcelExport;

use App\Application\ExcelExport\CreateExcelExport\CreateExcelExportResult;
use App\Application\ExcelExport\GetExcelExportsForModel\ExcelExportListItem;
use App\Application\ExcelExport\GetExcelExportsForModel\GetExcelExportsForModelResult;
use App\Presentation\Api\Response\ExcelExport\ExcelExportApiResponseFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ExcelExportApiResponseFactoryTest extends TestCase
{
    public function testCreatesExcelExportApiResponse(): void
    {
        $factory = new ExcelExportApiResponseFactory($this->createStub(UrlGeneratorInterface::class));

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
                    'financialModelShortId' => 'ab23456789',
                    'status' => 'pending',
                ],
            ],
        ], $response);
    }

    public function testAllowsNullExportId(): void
    {
        $factory = new ExcelExportApiResponseFactory($this->createStub(UrlGeneratorInterface::class));

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
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator
            ->expects(self::once())
            ->method('generate')
            ->with('api.excel_export.download', [
                'financialModelShortId' => 'ab23456789',
                'exportId' => 15,
            ])
            ->willReturn('/api/models/ab23456789/exports/excel/15/download');

        $factory = new ExcelExportApiResponseFactory($urlGenerator);

        $response = $factory->createList(
            result: new GetExcelExportsForModelResult(
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
                    new ExcelExportListItem(
                        id: 16,
                        status: 'pending',
                        filePath: null,
                        errorMessage: null,
                        createdAt: '2026-04-21T10:03:00+00:00',
                        startedAt: null,
                        completedAt: null,
                        failedAt: null,
                    ),
                ],
            ),
            financialModelShortId: 'ab23456789',
        );

        self::assertSame([
            'data' => [
                'exports' => [
                    [
                        'id' => 15,
                        'status' => 'completed',
                        'filePath' => '/exports/model.xlsx',
                        'downloadUrl' => '/api/models/ab23456789/exports/excel/15/download',
                        'errorMessage' => null,
                        'createdAt' => '2026-04-21T10:00:00+00:00',
                        'startedAt' => '2026-04-21T10:01:00+00:00',
                        'completedAt' => '2026-04-21T10:02:00+00:00',
                        'failedAt' => null,
                    ],
                    [
                        'id' => 16,
                        'status' => 'pending',
                        'filePath' => null,
                        'downloadUrl' => null,
                        'errorMessage' => null,
                        'createdAt' => '2026-04-21T10:03:00+00:00',
                        'startedAt' => null,
                        'completedAt' => null,
                        'failedAt' => null,
                    ],
                ],
            ],
        ], $response);
    }
}
