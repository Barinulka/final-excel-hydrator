<?php

declare(strict_types=1);

namespace App\Controller\Web\Project;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProjectListController extends AbstractController
{
    #[Route(path: '/projects', name: 'app_projects')]
    public function __invoke(): Response
    {
        return $this->render('project/list.html.twig');
    }
}
