<?php

declare(strict_types=1);

namespace App\Application\ExcelExport\MarkExcelExportCompleted;

use App\Application\ExcelExport\ExcelExportRepository;
use App\Application\Shared\Transaction\TransactionalRunner;

final readonly class MarkExcelExportCompletedHandler
{
    public function __construct(
        private ExcelExportRepository $excelExportRepository,
        private TransactionalRunner $transactionalRunner,
    ) {
    }

    public function handle(MarkExcelExportCompletedCommand $command): MarkExcelExportCompletedResult
    {
        return $this->transactionalRunner->run(function () use ($command): MarkExcelExportCompletedResult {
            $excelExport = $this->excelExportRepository->findById($command->exportId);

            if (null === $excelExport) {
                throw new ExcelExportForCompletionNotFoundException("Excel export '{$command->exportId}' не найден.");
            }

            $excelExport->markCompleted($command->filePath);

            $this->excelExportRepository->save($excelExport);

            return new MarkExcelExportCompletedResult(
                exportId: $command->exportId,
                status: $excelExport->getStatus()->value,
                filePath: $excelExport->getFilePath(),
                completedAt: $excelExport->getCompletedAt()?->format(\DateTimeInterface::ATOM),
            );
        });
    }
}
