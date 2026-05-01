<?php

declare(strict_types=1);

namespace App\Application\ExcelExport\GetPendingExcelExportsForProcessing;

final readonly class GetPendingExcelExportsForProcessingQuery
{
    public function __construct(
        public int $limit,
    ) {
    }
}
