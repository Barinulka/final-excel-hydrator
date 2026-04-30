<?php

declare(strict_types=1);

namespace App\Tests\Presentation\Api\Response\ExcelExport;

use App\Application\ExcelExport\CreateExcelExport\CreateExcelExportResult;
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
}
