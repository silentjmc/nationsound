<?php

namespace App\Controller;

use App\Repository\InformationSectionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class InformationSectionController extends AbstractController
{
    #[Route('/informationsection', name: 'app_information_section')]
    public function index(): Response
    {
        return $this->render('information_section/index.html.twig', [
            'controller_name' => 'InformationSectionController',
        ]);
    }
}
