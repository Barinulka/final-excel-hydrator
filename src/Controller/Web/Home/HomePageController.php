<?php

declare(strict_types=1);

namespace App\Controller\Web\Home;

use App\Application\HomePage\GetHomePageSettings\GetHomePageSettingsHandler;
use App\Application\HomePage\GetHomePageSettings\GetHomePageSettingsQuery;
use App\Controller\BaseAbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomePageController extends BaseAbstractController
{
    #[Route(path: '/', name: 'app_home', methods: ['GET'])]
    public function __invoke(GetHomePageSettingsHandler $handler): Response
    {
        return $this->render('home/index.html.twig', [
            'page' => $handler->handle(new GetHomePageSettingsQuery()),
        ]);
    }
}
