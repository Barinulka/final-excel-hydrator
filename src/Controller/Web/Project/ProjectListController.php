<?php

declare(strict_types=1);

namespace App\Controller\Web\Project;

use App\Controller\BaseAbstractController;
use App\Presentation\Web\Page\ProjectWorkspace\ProjectWorkspacePageBuilder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProjectListController extends BaseAbstractController
{
    #[Route(path: '/projects', name: 'app_projects', methods: ['GET'])]
    public function __invoke(ProjectWorkspacePageBuilder $pageBuilder): Response
    {
        $owner = $this->getAuthorizedUser();
        $page = $pageBuilder->buildWithoutSelectedProject($owner);

        if ($page->hasProjects()) {
            return $this->redirectToRoute('app_project_page', [
                'projectShortId' => $page->projectList->projects[0]->shortId,
            ]);
        }

        return $this->render('project/workspace.html.twig', [
            'page' => $page,
        ]);
    }
}
