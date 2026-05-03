<?php

declare(strict_types=1);

namespace App\Application\ExcelExport\CreateExcelExport;

use App\Application\Calculation\BuildFinancialModelCalculation\BuildFinancialModelCalculationHandler;
use App\Application\Calculation\BuildFinancialModelCalculation\BuildFinancialModelCalculationQuery;
use App\Application\Calculation\CalculationResultPayloadFactory;
use App\Application\ExcelExport\ExcelExportRepository;
use App\Application\FinancialModel\FinancialModelRepository;
use App\Application\Shared\Transaction\TransactionalRunner;
use App\Entity\ExcelExport;
use App\Entity\FinancialModel;
use LogicException;

final readonly class CreateExcelExportHandler
{
    public function __construct(
        private ExcelExportRepository $excelExportRepository,
        private FinancialModelRepository $financialModelRepository,
        private TransactionalRunner $transactionalRunner,
        private BuildFinancialModelCalculationHandler $calculationHandler,
        private CalculationResultPayloadFactory $calculationResultPayloadFactory,
    ) {
    }

    public function handle(CreateExcelExportCommand $command): CreateExcelExportResult
    {
        return $this->transactionalRunner->run(function () use ($command): CreateExcelExportResult {
            $financialModel = $this->findFinancialModel($command);

            if (null === $financialModel) {
                throw new FinancialModelForExcelExportNotFoundException("Модель '{$command->financialModelShortId}' не найдена.");
            }

            if ($financialModel->isArchived()) {
                throw new ArchivedFinancialModelCannotBeExportedException("Модель '{$command->financialModelShortId}' находится в архиве.");
            }

            $project = $financialModel->getProject();
            if (null === $project) {
                throw new LogicException('Финансовая модель не привязана к проекту.');
            }

            $calculationResult = $this->calculationHandler->handle(new BuildFinancialModelCalculationQuery(
                owner: $command->owner,
                financialModelShortId: $command->financialModelShortId,
                projectShortId: $command->projectShortId,
            ));

            $calculationResultPayload = $this->calculationResultPayloadFactory->create($calculationResult);

            $excelExport = ExcelExport::create(
                project: $project,
                financialModel: $financialModel,
                calculationResultPayload: $calculationResultPayload,
            );

            $this->excelExportRepository->save($excelExport);

            return new CreateExcelExportResult(
                exportId: null,
                projectShortId: (string) $project->getShortId(),
                financialModelShortId: $command->financialModelShortId->toString(),
                status: $excelExport->getStatus()->value,
            );
        });
    }

    private function findFinancialModel(CreateExcelExportCommand $command): ?FinancialModel
    {
        if (null === $command->projectShortId) {
            return $this->financialModelRepository->findOneByShortIdForOwner(
                shortId: $command->financialModelShortId,
                owner: $command->owner,
            );
        }

        return $this->financialModelRepository->findOneByShortIdForProjectAndOwner(
            financialModelShortId: $command->financialModelShortId,
            projectShortId: $command->projectShortId,
            owner: $command->owner,
        );
    }
}
