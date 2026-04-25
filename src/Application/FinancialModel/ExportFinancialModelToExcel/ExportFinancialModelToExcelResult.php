<?php

declare(strict_types=1);

namespace App\Application\FinancialModel\ExportFinancialModelToExcel;

final readonly class ExportFinancialModelToExcelResult
{
    public function __construct(
        public string $filename,
        public string $downloadName,
    ) {
    }
}
