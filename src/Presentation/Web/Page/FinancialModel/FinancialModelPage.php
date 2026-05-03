<?php

declare(strict_types=1);

namespace App\Presentation\Web\Page\FinancialModel;

use App\Application\TimeParams\GetTimeParamsForEdit\GetTimeParamsForEditResult;
use App\Presentation\Web\Tab\FinancialModelTabDefinition;

/**
 * @param FinancialModelTabDefinition[] $tabs
 */
final readonly class FinancialModelPage
{
    public function __construct(
        public string $pageTitle,
        public string $financialModelShortId,
        public string $financialModelTitle,
        public string $financialModelStatus,
        public bool $isFinancialModelArchived,
        public array $tabs,
        public FinancialModelTabDefinition $activeTab,
        public GetTimeParamsForEditResult $timeParams,
    ) {
    }
}
