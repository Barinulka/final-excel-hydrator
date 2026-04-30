<?php

declare(strict_types=1);

namespace App\Application\ExcelExport\GetExcelExportsForModel;

final readonly class ExcelExportListItem
{
    public function __construct(
        public ?int $id,
        public string $status,
        public ?string $filePath,
        public ?string $errorMessage,
        public ?string $createdAt,
        public ?string $startedAt,
        public ?string $completedAt,
        public ?string $failedAt,
    ) {
    }
}
