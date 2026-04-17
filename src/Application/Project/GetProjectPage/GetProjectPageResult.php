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

    public function financialModelCount(): int
    {
        return count($this->financialModels);
    }

    public function activeFinancialModelCount(): int
    {
        return count(array_filter(
            $this->financialModels,
            static fn (FinancialModelListItem $financialModel): bool => !$financialModel->isArchived,
        ));
    }

    public function archivedFinancialModelCount(): int
    {
        return count(array_filter(
            $this->financialModels,
            static fn (FinancialModelListItem $financialModel): bool => $financialModel->isArchived,
        ));
    }
}
