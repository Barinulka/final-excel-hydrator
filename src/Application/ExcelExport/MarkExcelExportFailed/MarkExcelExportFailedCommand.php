<?php

declare(strict_types=1);

namespace App\Application\ExcelExport\MarkExcelExportFailed;

final readonly class MarkExcelExportFailedCommand
{
    public function __construct(
        public int $exportId,
        public string $errorMessage,
    ) {
    }
}
