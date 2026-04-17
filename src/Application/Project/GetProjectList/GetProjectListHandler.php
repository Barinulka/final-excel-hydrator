<?php

declare(strict_types=1);

namespace App\Application\Project\GetProjectList;

use App\Application\Project\ProjectRepository;
use App\Domain\Project\Enum\ProjectStatus;
use App\Entity\Project;

final readonly class GetProjectListHandler
{
    public function __construct(
        private ProjectRepository $projectRepository,
    ) {
    }

    public function handle(GetProjectListQuery $query): GetProjectListResult
    {
        $projects = $this->projectRepository->findAllForOwner($query->owner);

        return new GetProjectListResult(
            projects: $this->prepareProjectListItems($projects),
        );
    }

    private function prepareProjectListItems(array $projects): array
    {
        if (empty($projects)) {
            return [];
        }

        return array_map(static function (Project $project): ProjectListItem {
            return new ProjectListItem(
                shortId: $project->getShortId(),
                title: $project->getTitle(),
                description: $project->getDescription() ?? null,
                status: $project->getStatus()->value,
                isArchived: $project->getStatus() === ProjectStatus::Archived,
            );
        }, $projects);
    }
}
