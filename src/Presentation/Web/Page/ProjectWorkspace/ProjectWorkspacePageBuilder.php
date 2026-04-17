<?php

declare(strict_types=1);

namespace App\Presentation\Web\Page\ProjectWorkspace;

use App\Application\Project\GetProjectList\GetProjectListHandler;
use App\Application\Project\GetProjectList\GetProjectListQuery;
use App\Application\Project\GetProjectPage\GetProjectPageHandler;
use App\Application\Project\GetProjectPage\GetProjectPageQuery;
use App\Domain\Shared\ValueObject\ShortId;
use App\Entity\User;

final readonly class ProjectWorkspacePageBuilder
{
    public function __construct(
        private GetProjectListHandler $projectListHandler,
        private GetProjectPageHandler $projectPageHandler,
    ) {
    }

    public function buildWithoutSelectedProject(User $owner): ProjectWorkspacePage
    {
        $projectList = $this->projectListHandler->handle(new GetProjectListQuery($owner));

        return new ProjectWorkspacePage(
            projectList: $projectList,
            selectedProject: null,
            selectedProjectShortId: null,
        );
    }

    public function buildForSelectedProject(User $owner, ShortId $projectShortId): ProjectWorkspacePage
    {
        $projectList = $this->projectListHandler->handle(new GetProjectListQuery($owner));
        $selectedProject = $this->projectPageHandler->handle(new GetProjectPageQuery($owner, $projectShortId));

        return new ProjectWorkspacePage(
            projectList: $projectList,
            selectedProject: $selectedProject,
            selectedProjectShortId: $selectedProject->projectShortId,
        );
    }
}
