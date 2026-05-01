<?php

declare(strict_types=1);

namespace App\Application\ExcelExport\MarkExcelExportFailed;

use App\Application\ExcelExport\ExcelExportRepository;
use App\Application\Shared\Transaction\TransactionalRunner;

final readonly class MarkExcelExportFailedHandler
{
    public function __construct(
        private ExcelExportRepository $excelExportRepository,
        private TransactionalRunner $transactionalRunner,
    ) {
    }

    public function handle(MarkExcelExportFailedCommand $command): MarkExcelExportFailedResult
    {
        return $this->transactionalRunner->run(function () use ($command): MarkExcelExportFailedResult {
            $excelExport = $this->excelExportRepository->findById($command->exportId);

            if (null === $excelExport) {
                throw new ExcelExportForFailureNotFoundException("Excel export '{$command->exportId}' не найден.");
            }

            $excelExport->markFailed($command->errorMessage);

            $this->excelExportRepository->save($excelExport);

            return new MarkExcelExportFailedResult(
                exportId: $command->exportId,
                status: $excelExport->getStatus()->value,
                errorMessage: $excelExport->getErrorMessage(),
                failedAt: $excelExport->getFailedAt()?->format(\DateTimeInterface::ATOM),
            );
        });
    }
}
