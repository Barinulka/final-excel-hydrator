<?php

declare(strict_types=1);

namespace App\Application\Project\GetProjectPage;

/**
 * @param FinancialModelListItem[] $financialModels
 */
final readonly class GetProjectPageResult
{
    public function __construct(
        public string $projectShortId,
        public string $projectTitle,
        public ?string $projectDescription,
        public array $financialModels,
    ) {
    }
}
