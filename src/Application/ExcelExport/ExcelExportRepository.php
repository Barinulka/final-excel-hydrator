<?php

declare(strict_types=1);

namespace App\Application\ExcelExport;

use App\Entity\ExcelExport;

interface ExcelExportRepository
{
    public function save(ExcelExport $excelExport): void;
}
