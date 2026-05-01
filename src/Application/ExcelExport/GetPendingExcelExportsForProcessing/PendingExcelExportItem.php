<?php

declare(strict_types=1);

namespace App\Application\ExcelExport\GetPendingExcelExportsForProcessing;

final readonly class PendingExcelExportItem
{
    public function __construct(
        public int $id,
        public string $status,
        public array $calculationResultPayload,
        public ?string $createdAt,
    ) {
    }
}
