<?php

declare(strict_types=1);

namespace App\Application\ExcelExport\GetExcelExportsForModel;

final readonly class GetExcelExportsForModelResult
{
    /**
     * @param list<ExcelExportListItem> $exports
     */
    public function __construct(
        public array $exports,
    ) {
    }
}
