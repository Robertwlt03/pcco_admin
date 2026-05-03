<?php

namespace App\Controller\Admin;

use App\Enum\UserRole;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/{_locale}/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('<img src="/images/pcco-logo.svg" style="height: 2.5rem;" alt="">')
            ->setLocales([
                'de' => '🇩🇪 Deutsch',
                'en' => '🇬🇧 English'
            ]);
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::section('Core Business');
        yield MenuItem::linkTo(CustomerCrudController::class, 'Customers', 'fa fa-address-book');
        yield MenuItem::linkTo(ProjectCrudController::class, 'Projects', 'fa fa-project-diagram');

        yield MenuItem::section('Organization');
        yield MenuItem::linkTo(TaskCrudController::class, 'Tasks', 'fa fa-tasks');

        yield MenuItem::section('System Administration')
            ->setPermission(UserRole::ADMIN->value);
        yield MenuItem::linkTo(UserCrudController::class, 'Users', 'fa fa-users')
            ->setPermission(UserRole::ADMIN->value);
    }
}
