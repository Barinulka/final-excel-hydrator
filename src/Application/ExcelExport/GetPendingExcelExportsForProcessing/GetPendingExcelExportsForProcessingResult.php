<?php

declare(strict_types=1);

namespace App\Application\ExcelExport\GetPendingExcelExportsForProcessing;

final readonly class GetPendingExcelExportsForProcessingResult
{
    /**
     * @param list<PendingExcelExportItem> $exports
     */
    public function __construct(
        public array $exports,
    ) {
    }
}
