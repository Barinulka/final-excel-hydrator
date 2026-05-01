<?php

declare(strict_types=1);

namespace App\Application\ExcelExport\MarkExcelExportCompleted;

final readonly class MarkExcelExportCompletedResult
{
    public function __construct(
        public int $exportId,
        public string $status,
        public ?string $filePath,
        public ?string $completedAt,
    ) {
    }
}
