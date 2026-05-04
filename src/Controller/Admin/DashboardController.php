<?php

namespace App\Controller\Admin;

use App\Enum\Status;
use App\Enum\UserRole;
use App\Repository\CustomerRepository;
use App\Repository\ProjectRepository;
use App\Repository\TaskRepository;
use App\Service\DashboardChartService;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;

#[AdminDashboard(routePath: '/{_locale}/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly DashboardChartService $chartService,
        private readonly ProjectRepository     $projectRepository,
        private readonly CustomerRepository    $customerRepository,
        private readonly TaskRepository        $taskRepository,
    )
    {}

    public function configureAssets(): Assets
    {
        return Assets::new()
            ->addAssetMapperEntry('app');
    }

    public function index(): Response
    {
        $projects = $this->projectRepository->findAll();
        $customers = $this->customerRepository->findAll();

        return $this->render('admin/dashboard.html.twig', [
            'totalProjects' => count($projects),
            'totalTasks' => $this->taskRepository->count([]),
            'projects' => $this->chartService->calculateProjectProgress($projects),
            'statusChart' => $this->chartService->createStatusChart($projects),
            'budgetChart' => $this->chartService->createBudgetChart($customers),
        ]);
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
