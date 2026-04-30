<?php

declare(strict_types=1);

namespace App\Application\ExcelExport;

use App\Entity\ExcelExport;
use App\Entity\FinancialModel;

interface ExcelExportRepository
{
    public function save(ExcelExport $excelExport): void;

    /**
     * @return list<ExcelExport>
     */
    public function findLatestForFinancialModel(FinancialModel $financialModel): array;
}
