<?php

declare(strict_types=1);

namespace App\Application\ExcelExport\MarkExcelExportProcessing;

final readonly class MarkExcelExportProcessingCommand
{
    public function __construct(
        public int $exportId,
    ) {
    }
}
