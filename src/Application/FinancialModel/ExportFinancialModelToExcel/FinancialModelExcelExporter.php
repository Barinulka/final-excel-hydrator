<?php

declare(strict_types=1);

namespace App\Application\FinancialModel\ExportFinancialModelToExcel;

use App\Entity\FinancialModel;

interface FinancialModelExcelExporter
{
    public function export(FinancialModel $financialModel): ExportFinancialModelToExcelResult;
}
