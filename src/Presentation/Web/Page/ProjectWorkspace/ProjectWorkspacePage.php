<?php

declare(strict_types=1);

namespace App\Presentation\Web\Page\ProjectWorkspace;

use App\Application\Project\GetProjectList\GetProjectListResult;
use App\Application\Project\GetProjectList\ProjectListItem;
use App\Application\Project\GetProjectPage\GetProjectPageResult;

final readonly class ProjectWorkspacePage
{
    public function __construct(
        public GetProjectListResult $projectList,
        public ?GetProjectPageResult $selectedProject,
        public ?string $selectedProjectShortId,
    ) {
    }

    public function hasProjects(): bool
    {
        return [] !== $this->projectList->projects;
    }

    public function selectedProjectListItem(): ?ProjectListItem
    {
        if (null === $this->selectedProjectShortId) {
            return null;
        }

        foreach ($this->projectList->projects as $project) {
            if ($project->shortId === $this->selectedProjectShortId) {
                return $project;
            }
        }

        return null;
    }
}
