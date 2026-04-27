<?php

declare(strict_types=1);

namespace App\Tests\Support\Application\ExcelExport;

use App\Application\ExcelExport\ExcelExportRepository;
use App\Entity\ExcelExport;

final class InMemoryExcelExportRepository implements ExcelExportRepository
{
    /**
     * @var ExcelExport[]
     */
    public array $savedExcelExports = [];

    public function save(ExcelExport $excelExport): void
    {
        foreach ($this->savedExcelExports as $savedExcelExport) {
            if ($savedExcelExport === $excelExport) {
                return;
            }
        }

        $this->savedExcelExports[] = $excelExport;
    }
}
