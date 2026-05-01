<?php

declare(strict_types=1);

namespace App\Application\ExcelExport\GetPendingExcelExportsForProcessing;

use App\Application\ExcelExport\ExcelExportRepository;
use App\Entity\ExcelExport;
use LogicException;

final readonly class GetPendingExcelExportsForProcessingHandler
{
    public function __construct(
        private ExcelExportRepository $excelExportRepository,
    ) {
    }

    public function handle(GetPendingExcelExportsForProcessingQuery $query): GetPendingExcelExportsForProcessingResult
    {
        $exports = $this->excelExportRepository->findPendingForProcessing($query->limit);

        return new GetPendingExcelExportsForProcessingResult(
            exports: array_map($this->createItem(...), $exports),
        );
    }

    private function createItem(ExcelExport $excelExport): PendingExcelExportItem
    {
        $id = $excelExport->getId();

        if (null === $id) {
            throw new LogicException('Excel export for processing must have id.');
        }

        return new PendingExcelExportItem(
            id: $id,
            status: $excelExport->getStatus()->value,
            calculationResultPayload: $excelExport->getCalculationResultPayload(),
            createdAt: $excelExport->getCreatedAt()?->format(\DateTimeInterface::ATOM),
        );
    }
}
