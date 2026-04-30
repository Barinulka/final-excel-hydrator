<?php

declare(strict_types=1);

namespace App\Application\ExcelExport\GetExcelExportsForModel;

use App\Application\ExcelExport\ExcelExportRepository;
use App\Application\FinancialModel\FinancialModelRepository;
use App\Entity\ExcelExport;

final readonly class GetExcelExportsForModelHandler
{
    public function __construct(
        private FinancialModelRepository $financialModelRepository,
        private ExcelExportRepository $excelExportRepository,
    ) {
    }

    public function handle(GetExcelExportsForModelQuery $query): GetExcelExportsForModelResult
    {
        $financialModel = $this->financialModelRepository->findOneByShortIdForProjectAndOwner(
            financialModelShortId: $query->financialModelShortId,
            projectShortId: $query->projectShortId,
            owner: $query->owner,
        );

        if (null === $financialModel) {
            throw new FinancialModelForExcelExportsNotFoundException("Модель не найдена");
        }

        $exports = $this->excelExportRepository->findLatestForFinancialModel($financialModel);

        return new GetExcelExportsForModelResult(
            exports: $this->prepareExportsList($exports),
        );
    }

    /**
     * @param list<ExcelExport> $exports
     *
     * @return list<ExcelExportListItem>
     */
    private function prepareExportsList(array $exports): array
    {
        return array_map(static function (ExcelExport $export): ExcelExportListItem {
            return new ExcelExportListItem(
                id: $export->getId(),
                status: $export->getStatus()->value,
                filePath: $export->getFilePath(),
                errorMessage: $export->getErrorMessage(),
                createdAt: $export->getCreatedAt()?->format(\DateTimeInterface::ATOM),
                startedAt: $export->getStartedAt()?->format(\DateTimeInterface::ATOM),
                completedAt: $export->getCompletedAt()?->format(\DateTimeInterface::ATOM),
                failedAt: $export->getFailedAt()?->format(\DateTimeInterface::ATOM),
            );
        }, $exports);
    }
}
