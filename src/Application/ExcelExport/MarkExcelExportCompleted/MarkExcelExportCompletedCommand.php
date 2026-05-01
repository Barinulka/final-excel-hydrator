<?php

declare(strict_types=1);

namespace App\Application\ExcelExport\MarkExcelExportCompleted;

final readonly class MarkExcelExportCompletedCommand
{
    public function __construct(
        public int $exportId,
        public string $filePath,
    ) {
    }
}
