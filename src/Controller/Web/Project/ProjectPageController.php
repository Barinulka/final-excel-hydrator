<?php

declare(strict_types=1);

namespace App\Controller\Web\Project;

use App\Application\Project\GetProjectPage\ProjectForPageNotFoundException;
use App\Controller\BaseAbstractController;
use App\Domain\Shared\ValueObject\ShortId;
use App\Presentation\Web\Page\ProjectWorkspace\ProjectWorkspacePage;
use App\Presentation\Web\Page\ProjectWorkspace\ProjectWorkspacePageBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProjectPageController extends BaseAbstractController
{
    #[Route(
        path: '/projects/{projectShortId}',
        name: 'app_project_page',
        requirements: [
            'projectShortId' => '[23456789abcdefghjkmnpqrstuvwxyz]{10}',
        ],
        methods: ['GET'],
    )]
    public function __invoke(
        string $projectShortId,
        Request $request,
        ProjectWorkspacePageBuilder $pageBuilder,
    ): Response
    {
        $owner = $this->getAuthorizedUser();

        try {
            $page = $pageBuilder->buildForSelectedProject($owner, ShortId::fromString($projectShortId));
        } catch (ProjectForPageNotFoundException) {
            throw $this->createNotFoundException('Проект не найден.');
        }

        return $this->renderWorkspace($request, $page);
    }

    private function renderWorkspace(Request $request, ProjectWorkspacePage $page): Response
    {
        if ('project_workspace' === $request->headers->get('Turbo-Frame')) {
            return $this->render('project/blocks/_project_workspace_frame.html.twig', [
                'page' => $page,
            ]);
        }

        return $this->render('project/workspace.html.twig', [
            'page' => $page,
        ]);
    }
}
