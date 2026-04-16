<?php

declare(strict_types=1);

namespace App\Application\Project\GetProjectPage;

use App\Application\FinancialModel\FinancialModelRepository;
use App\Application\Project\ProjectRepository;
use App\Domain\FinancialModel\Enum\FinancialModelStatus;

final readonly class GetProjectPageHandler
{

    public function __construct(
        private ProjectRepository $projectRepository,
        private FinancialModelRepository $financialModelRepository,
    ) {
    }

    public function handle(GetProjectPageQuery $projectPageQuery): GetProjectPageResult
    {
        $project = $this->projectRepository->findOneByShortIdForOwner($projectPageQuery->projectShortId, $projectPageQuery->owner);

        if (null === $project) {
            throw new ProjectForPageNotFoundException("Проект '{$projectPageQuery->projectShortId}' не найден");
        }

        $financialModels = $this->financialModelRepository->findAllForProject($project);

        return new GetProjectPageResult(
            projectShortId: $project->getShortId(),
            projectTitle: $project->getTitle(),
            projectDescription: $project->getDescription(),
            financialModels: $this->prepareFinancialModelsListItems($financialModels),
        );
    }

    private function prepareFinancialModelsListItems(array $financialModels): array
    {
        if (empty($financialModels)) {
            return [];
        }

        $result = [];

        foreach ($financialModels as $financialModel) {
            $result[] = new FinancialModelListItem(
                shortId: $financialModel->getShortId(),
                title: $financialModel->getTitle(),
                versionNumber: $financialModel->getVersionNumber(),
                status: $financialModel->getStatus()->value,
                isArchived: $financialModel->getStatus() === FinancialModelStatus::Archived,
            );
        }

        return $result;
    }
}
