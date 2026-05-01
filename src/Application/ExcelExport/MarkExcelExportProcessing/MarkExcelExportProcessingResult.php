<?php

declare(strict_types=1);

namespace App\Application\ExcelExport\MarkExcelExportProcessing;

final readonly class MarkExcelExportProcessingResult
{
    public function __construct(
        public int $exportId,
        public string $status,
        public ?string $startedAt,
    ) {
    }
}
