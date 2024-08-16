<?php

namespace App\Controller\Admin;

use App\Entity\Artist;
use App\Entity\Event;
use App\Entity\EventType;
use App\Entity\Location;
use App\Entity\LocationType;
use App\Entity\Partners;
use App\Entity\PartnerType;
use App\Entity\Role;
use App\Entity\User;

use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        // return parent::index();

        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        return $this->redirect($adminUrlGenerator->setController(UserCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirect('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        // return $this->render('some/path/my-dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Live Event Backend');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Administration', 'fa fa-home');
        yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-list', User::class);
        yield MenuItem::linkToCrud('Rôles', 'fas fa-list', Role::class);
        yield MenuItem::linkToCrud('Type de partenaires', 'fas fa-list', PartnerType::class);
        yield MenuItem::linkToCrud('Partenaires', 'fas fa-list', Partners::class);
        yield MenuItem::linkToCrud('Artistes', 'fas fa-list', Artist::class);
        yield MenuItem::linkToCrud('Types d\'événements', 'fas fa-list', EventType::class);
        yield MenuItem::linkToCrud('Types de lieux', 'fas fa-list', LocationType::class);
        yield MenuItem::linkToCrud('Lieux', 'fas fa-list', Location::class);
    }
}
