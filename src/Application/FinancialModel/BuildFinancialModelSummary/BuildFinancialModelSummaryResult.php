<?php

declare(strict_types=1);

namespace App\Application\FinancialModel\BuildFinancialModelSummary;

final readonly class BuildFinancialModelSummaryResult
{
    /**
     * @param string[] $warnings
     */
    public function __construct(
        public string $projectShortId,
        public string $projectTitle,

        public string $financialModelShortId,
        public string $financialModelTitle,
        public string $financialModelStatus,
        public bool $isFinancialModelArchived,
        public string $amountDisplayFormat,

        public TimeParamsSummary $timeParams,
        public TimelineSummary $timeline,
        public array $warnings,
    ) {
    }
}
