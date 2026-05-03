<?php

declare(strict_types=1);

namespace App\Presentation\Web\Page\FinancialModel;

use App\Application\TimeParams\GetTimeParamsForEdit\GetTimeParamsForEditHandler;
use App\Application\TimeParams\GetTimeParamsForEdit\GetTimeParamsForEditByModelQuery;
use App\Application\TimeParams\GetTimeParamsForEdit\GetTimeParamsForEditResult;
use App\Domain\Shared\ValueObject\ShortId;
use App\Entity\User;
use App\Presentation\Web\Tab\FinancialModelTabRegistry;

final readonly class FinancialModelPageBuilder
{
    public function __construct(
        private GetTimeParamsForEditHandler $getTimeParamsForEditHandler,
        private FinancialModelTabRegistry $tabRegistry,
    ) {
    }

    public function buildForModel(
        User $owner,
        ShortId $financialModelShortId,
        string $activeTabKey,
    ): FinancialModelPage {
        $timeParams = $this->getTimeParamsForEditHandler->handleByModel(
            new GetTimeParamsForEditByModelQuery(
                owner: $owner,
                financialModelShortId: $financialModelShortId,
            )
        );

        return $this->buildPageFromTimeParams($timeParams, $activeTabKey);
    }

    private function buildPageFromTimeParams(
        GetTimeParamsForEditResult $timeParams,
        string $activeTabKey,
    ): FinancialModelPage {
        $activeTab = $this->tabRegistry->get($activeTabKey);

        return new FinancialModelPage(
            pageTitle: sprintf('%s | %s', $timeParams->financialModelTitle, $activeTab->label),
            financialModelShortId: $timeParams->financialModelShortId,
            financialModelTitle: $timeParams->financialModelTitle,
            financialModelStatus: $timeParams->financialModelStatus,
            isFinancialModelArchived: $timeParams->isFinancialModelArchived,
            tabs: $this->tabRegistry->all(),
            activeTab: $activeTab,
            timeParams: $timeParams,
        );
    }
}
