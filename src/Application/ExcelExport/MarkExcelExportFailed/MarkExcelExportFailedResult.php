<?php

declare(strict_types=1);

namespace App\Application\ExcelExport\MarkExcelExportFailed;

final readonly class MarkExcelExportFailedResult
{
    public function __construct(
        public int $exportId,
        public string $status,
        public ?string $errorMessage,
        public ?string $failedAt,
    ) {
    }
}
