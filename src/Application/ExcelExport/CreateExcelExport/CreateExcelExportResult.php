<?php

declare(strict_types=1);

namespace App\Application\ExcelExport\CreateExcelExport;

final readonly class CreateExcelExportResult
{
    public function __construct(
        public ?int $exportId,
        public string $projectShortId,
        public string $financialModelShortId,
        public string $status,
    ) {
    }
}
