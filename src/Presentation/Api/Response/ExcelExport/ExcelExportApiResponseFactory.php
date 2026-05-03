<?php

declare(strict_types=1);

namespace App\Presentation\Api\Response\ExcelExport;

use App\Application\ExcelExport\CreateExcelExport\CreateExcelExportResult;
use App\Application\ExcelExport\GetExcelExportsForModel\ExcelExportListItem;
use App\Application\ExcelExport\GetExcelExportsForModel\GetExcelExportsForModelResult;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final readonly class ExcelExportApiResponseFactory
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function create(CreateExcelExportResult $result): array
    {
        return [
            'data' => [
                'export' => [
                    'id' => $result->exportId,
                    'financialModelShortId' => $result->financialModelShortId,
                    'status' => $result->status,
                ],
            ],
        ];
    }

    public function createList(
        GetExcelExportsForModelResult $result,
        string $financialModelShortId,
    ): array {
        $urlGenerator = $this->urlGenerator;

        return [
            'data' => [
                'exports' => array_map(
                    static fn (ExcelExportListItem $export): array => [
                        'id' => $export->id,
                        'status' => $export->status,
                        'filePath' => $export->filePath,
                        'downloadUrl' => self::createDownloadUrl(
                            urlGenerator: $urlGenerator,
                            export: $export,
                            financialModelShortId: $financialModelShortId,
                        ),
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

    private static function createDownloadUrl(
        UrlGeneratorInterface $urlGenerator,
        ExcelExportListItem $export,
        string $financialModelShortId,
    ): ?string {
        if ('completed' !== $export->status || null === $export->id) {
            return null;
        }

        return $urlGenerator->generate('api.excel_export.download', [
            'financialModelShortId' => $financialModelShortId,
            'exportId' => $export->id,
        ]);
    }
}
