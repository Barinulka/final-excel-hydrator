<?php

declare(strict_types=1);

namespace App\Presentation\Api\Response\FinancialModel;

use App\Application\FinancialModel\BuildFinancialModelSummary\BuildFinancialModelSummaryResult;

final readonly class FinancialModelSummaryApiResponseFactory
{
    public function create(BuildFinancialModelSummaryResult $result): array
    {
        return [
            'data' => [
                'project' => [
                    'shortId' => $result->projectShortId,
                    'title' => $result->projectTitle,
                ],
                'financialModel' => [
                    'shortId' => $result->financialModelShortId,
                    'title' => $result->financialModelTitle,
                    'status' => $result->financialModelStatus,
                    'isArchived' => $result->isFinancialModelArchived,
                    'amountDisplayFormat' => $result->amountDisplayFormat,
                ],
                'timeParams' => [
                    'investmentStartMonth' => $result->timeParams->investmentStartMonth,
                    'investmentDurationMonths' => $result->timeParams->investmentDurationMonths,
                    'commercialOperationDurationMonths' => $result->timeParams->commercialOperationDurationMonths,
                    'totalDurationMonths' => $result->timeParams->totalDurationMonths,
                    'forecastStep' => $result->timeParams->forecastStep,
                    'forecastStepLabel' => $result->timeParams->forecastStepLabel,
                ],
                'timeline' => [
                    'investmentStartDate' => $result->timeline->investmentStartDate,
                    'investmentEndDate' => $result->timeline->investmentEndDate,
                    'commercialOperationStartDate' => $result->timeline->commercialOperationStartDate,
                    'commercialOperationEndDate' => $result->timeline->commercialOperationEndDate,
                    'modelStartDate' => $result->timeline->modelStartDate,
                    'modelEndDate' => $result->timeline->modelEndDate,
                    'periodCount' => $result->timeline->periodCount,
                ],
                'warnings' => $result->warnings,
            ],
        ];
    }
}
