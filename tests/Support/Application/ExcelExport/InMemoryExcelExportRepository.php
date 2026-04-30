<?php

declare(strict_types=1);

namespace App\Tests\Support\Application\ExcelExport;

use App\Application\ExcelExport\ExcelExportRepository;
use App\Entity\ExcelExport;
use App\Entity\FinancialModel;

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

    /**
     * @return list<ExcelExport>
     */
    public function findLatestForFinancialModel(FinancialModel $financialModel): array
    {
        $excelExports = array_filter(
            $this->savedExcelExports,
            static fn (ExcelExport $excelExport): bool => $excelExport->getFinancialModel() === $financialModel,
        );

        usort(
            $excelExports,
            static function (ExcelExport $left, ExcelExport $right): int {
                return ($right->getCreatedAt()?->getTimestamp() ?? 0)
                    <=> ($left->getCreatedAt()?->getTimestamp() ?? 0);
            },
        );

        return array_values($excelExports);
    }
}
