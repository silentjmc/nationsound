<?php

namespace App\Controller;

use App\Repository\FaqRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FaqController extends AbstractController
{
    #[Route('/faq', name: 'app_faq_index')]
    public function index(): Response
    {
        return $this->render('faq/index.html.twig', [
            'controller_name' => 'FaqController',
        ]);
    }
}
