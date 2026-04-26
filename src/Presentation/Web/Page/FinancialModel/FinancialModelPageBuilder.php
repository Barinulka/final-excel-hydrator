<?php

declare(strict_types=1);

namespace App\Presentation\Web\Page\FinancialModel;

use App\Application\TimeParams\GetTimeParamsForEdit\GetTimeParamsForEditHandler;
use App\Application\TimeParams\GetTimeParamsForEdit\GetTimeParamsForEditQuery;
use App\Domain\Shared\ValueObject\ShortId;
use App\Entity\User;
use App\Presentation\Web\Tab\FinancialModelTabRegistry;

final readonly class FinancialModelPageBuilder
{
    private const INPUT_PARAMS_TAB_KEY = 'input_params';

    public function __construct(
        private GetTimeParamsForEditHandler $getTimeParamsForEditHandler,
        private FinancialModelTabRegistry $tabRegistry,
    ) {
    }

    public function buildEditPage(
        User $owner,
        ShortId $projectShortId,
        ShortId $financialModelShortId,
        string $activeTabKey,
    ): FinancialModelPage {
        $timeParams = $this->getTimeParamsForEditHandler->handle(
            new GetTimeParamsForEditQuery(
                owner: $owner,
                projectShortId: $projectShortId,
                financialModelShortId: $financialModelShortId,
            )
        );

        $activeTab = $this->tabRegistry->get($activeTabKey);

        return new FinancialModelPage(
            pageTitle: sprintf('%s | %s', $timeParams->financialModelTitle, $activeTab->label),
            projectShortId: $timeParams->projectShortId,
            projectTitle: $timeParams->projectTitle,
            financialModelShortId: $timeParams->financialModelShortId,
            financialModelTitle: $timeParams->financialModelTitle,
            financialModelStatus: $timeParams->financialModelStatus,
            isFinancialModelArchived: $timeParams->isFinancialModelArchived,
            tabs: $this->tabRegistry->all(),
            activeTab: $activeTab,
            timeParams: $timeParams,
        );
    }

    public function buildTimeParamsPage(
        User $owner,
        ShortId $projectShortId,
        ShortId $financialModelShortId,
    ): FinancialModelPage {
        return $this->buildEditPage(
            owner: $owner,
            projectShortId: $projectShortId,
            financialModelShortId: $financialModelShortId,
            activeTabKey: self::INPUT_PARAMS_TAB_KEY,
        );
    }
}
