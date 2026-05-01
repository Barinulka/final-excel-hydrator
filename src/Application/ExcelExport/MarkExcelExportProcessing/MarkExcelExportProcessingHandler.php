<?php

declare(strict_types=1);

namespace App\Application\ExcelExport\MarkExcelExportProcessing;

use App\Application\ExcelExport\ExcelExportRepository;
use App\Application\Shared\Transaction\TransactionalRunner;

final readonly class MarkExcelExportProcessingHandler
{
    public function __construct(
        private ExcelExportRepository $excelExportRepository,
        private TransactionalRunner $transactionalRunner,
    ) {
    }

    public function handle(MarkExcelExportProcessingCommand $command): MarkExcelExportProcessingResult
    {
        return $this->transactionalRunner->run(function () use ($command): MarkExcelExportProcessingResult {
            $excelExport = $this->excelExportRepository->findById($command->exportId);

            if (null === $excelExport) {
                throw new ExcelExportForProcessingNotFoundException("Excel export '{$command->exportId}' не найден.");
            }

            $excelExport->markProcessing();

            $this->excelExportRepository->save($excelExport);

            return new MarkExcelExportProcessingResult(
                exportId: $command->exportId,
                status: $excelExport->getStatus()->value,
                startedAt: $excelExport->getStartedAt()?->format(\DateTimeInterface::ATOM),
            );
        });
    }
}
