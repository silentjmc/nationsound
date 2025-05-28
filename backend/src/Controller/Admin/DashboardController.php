<?php

namespace App\Controller\Admin;

use App\Entity\Artist;
use App\Entity\EntityHistory;
use App\Entity\Event;
use App\Entity\EventDate;
use App\Entity\EventLocation;
use App\Entity\EventType;
use App\Entity\Faq;
use App\Entity\Information;
use App\Entity\InformationSection;
use App\Entity\LocationType;
use App\Entity\News;
use App\Entity\Partner;
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
    #[Route('/', name: 'admin')]
    public function index(): Response
    {
        // return parent::index();

        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        //$this->denyAccessUnlessGranted('ROLE_ADMIN');

        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        //return $this->redirect($adminUrlGenerator->setController(UserCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirect('...');
        // }

        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirect($adminUrlGenerator->setController(UserCrudController::class)->generateUrl());
        } elseif ($this->isGranted('ROLE_MARKETING')) {
            return $this->redirect($adminUrlGenerator->setController(NewsCrudController::class)->generateUrl());
        } elseif ($this->isGranted('ROLE_COMMERCIAL')) {
            return $this->redirect($adminUrlGenerator->setController(PartnerCrudController::class)->generateUrl());
        } elseif ($this->isGranted('ROLE_REDACTEUR')) {
            return $this->redirect($adminUrlGenerator->setController(InformationCrudController::class)->generateUrl());
        }
        
        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        // return $this->render('some/path/my-dashboard.html.twig');
        return $this->redirectToRoute('app_login');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('<img src="./assets/logo_ns_rect_txtw.png" class="img-fluid d-block mx-auto" style="max-width:100px; width:100%;">')
            ->renderContentMaximized()
            ;
    }

    public function configureMenuItems(): iterable
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            yield MenuItem::section('Administration', 'fa fa-home');
            yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-users', User::class);
            yield MenuItem::linkToCrud('Roles', 'fas fa-user-tag', Role::class);
        }

        if ($this->isGranted('ROLE_MARKETING')) {
            yield MenuItem::section('Festival','fa fa-star');
            yield MenuItem::linkToCrud('Dates du festival','fas fa-calendar-day', EventDate::class);
        }

        if ($this->isGranted('ROLE_COMMERCIAL')) {
            yield MenuItem::section('Partenaires','fa fa-handshake');
            yield MenuItem::linkToCrud('Type de partenaires', 'fas fa-tags', PartnerType::class);
            yield MenuItem::linkToCrud('Partenaires', 'fas fa-building', Partner::class);
        }

        if ($this->isGranted('ROLE_MARKETING') || $this->isGranted('ROLE_REDACTEUR')) {
            yield MenuItem::section('Artistes','fa fa-music');
            yield MenuItem::linkToCrud('Artistes', 'fas fa-user', Artist::class);
        }

        if ($this->isGranted('ROLE_MARKETING')) {
            yield MenuItem::section('Évènements','fa fa-calendar');
            yield MenuItem::linkToCrud('Types d\'évènements', 'fas fa-icons', EventType::class);
            yield MenuItem::linkToCrud('Évènements', 'fas fa-clock', Event::class);
        }

        if ($this->isGranted('ROLE_COMMERCIAL')) {
            yield MenuItem::section('Lieux','fa fa-map');
            yield MenuItem::linkToCrud('Types de lieux', 'fas fa-mountain-city', LocationType::class);
            yield MenuItem::linkToCrud('Lieux', 'fas fa-location-dot', EventLocation::class);
        }

        if ($this->isGranted('ROLE_MARKETING') || $this->isGranted('ROLE_REDACTEUR')) {
            yield MenuItem::section('Informations générales','fa fa-lightbulb');
        }
        
        if ($this->isGranted('ROLE_MARKETING')) {
            yield MenuItem::linkToCrud('Section d\'informations','fa fa-receipt', InformationSection::class);
        }

        if ($this->isGranted('ROLE_MARKETING') || $this->isGranted('ROLE_REDACTEUR')) {
            yield MenuItem::linkToCrud('Informations','fa fa-info-circle', Information::class);
            yield MenuItem::linkToCrud('FAQ','fa fa-question-circle', Faq::class);
            yield MenuItem::linkToCrud('Actualités','fa fa-bell', News::class);
        }

        if ($this->isGranted('ROLE_ADMIN')) {
            yield MenuItem::section('HISTORIQUE','fa fa-database');
            yield MenuItem::linkToCrud('Historiques','fa fa-box-archive', EntityHistory::class);
        }
    }
}
