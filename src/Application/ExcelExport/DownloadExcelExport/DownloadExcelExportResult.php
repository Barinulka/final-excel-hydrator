<?php

declare(strict_types=1);

namespace App\Application\ExcelExport\DownloadExcelExport;

final readonly class DownloadExcelExportResult
{
    public function __construct(
        public string $absolutePath,
        public string $downloadName,
    ) {
    }
}
