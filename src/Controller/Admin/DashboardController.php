<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Repository\HomePageSettingsRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
final class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig', [
            'homePageSettingsId' => HomePageSettingsRepository::SINGLETON_ID,
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('ИнвестОценка')
            ->renderContentMaximized();
    }

    public function configureMenuItems(): iterable
    {
        return [
            MenuItem::linkToDashboard('Панель управления', 'fa fa-home'),
            MenuItem::linkToRoute('Главная страница', 'fa fa-file-o', 'admin_home_page_settings_edit', [
                'entityId' => HomePageSettingsRepository::SINGLETON_ID,
            ]),
        ];
    }
}
