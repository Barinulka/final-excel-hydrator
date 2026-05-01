<?php

declare(strict_types=1);

namespace App\Application\ExcelExport\DownloadExcelExport;

use App\Application\ExcelExport\ExcelExportRepository;
use App\Application\ExcelExport\Storage\ExcelExportStorage;
use App\Application\ExcelExport\Storage\ExcelExportStorageFileNotFoundException;
use App\Application\ExcelExport\Storage\InvalidExcelExportStoragePathException;
use App\Application\FinancialModel\FinancialModelRepository;

final readonly class DownloadExcelExportHandler
{
    public function __construct(
        private FinancialModelRepository $financialModelRepository,
        private ExcelExportRepository $excelExportRepository,
        private ExcelExportStorage $excelExportStorage,
    ) {
    }

    public function handle(DownloadExcelExportQuery $query): DownloadExcelExportResult
    {
        $financialModel = $this->financialModelRepository->findOneByShortIdForProjectAndOwner(
            financialModelShortId: $query->financialModelShortId,
            projectShortId: $query->projectShortId,
            owner: $query->owner,
        );

        if (null === $financialModel) {
            throw new ExcelExportForDownloadNotFoundException('Excel export не найден.');
        }

        $excelExport = $this->excelExportRepository->findById($query->exportId);

        if (null === $excelExport || $excelExport->getFinancialModel() !== $financialModel) {
            throw new ExcelExportForDownloadNotFoundException('Excel export не найден.');
        }

        if (!$excelExport->isCompleted()) {
            throw new ExcelExportFileNotReadyException('Excel export-файл еще не готов.');
        }

        $filePath = $excelExport->getFilePath();
        if (null === $filePath || '' === trim($filePath)) {
            throw new ExcelExportFileNotReadyException('Excel export завершен без filePath.');
        }

        try {
            $absolutePath = $this->excelExportStorage->resolveExistingFile($filePath);
        } catch (ExcelExportStorageFileNotFoundException | InvalidExcelExportStoragePathException $exception) {
            throw new ExcelExportFileNotFoundException($exception->getMessage(), previous: $exception);
        }

        return new DownloadExcelExportResult(
            absolutePath: $absolutePath,
            downloadName: sprintf('financial-model-%s-export-%d.xlsx', $query->financialModelShortId->toString(), $query->exportId),
        );
    }
}
