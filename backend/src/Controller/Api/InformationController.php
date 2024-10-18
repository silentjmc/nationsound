<?php

namespace App\Controller\Api;

use App\Repository\InformationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class InformationController extends AbstractController
{
    /*
    #[Route('/api/information', name: 'app_information', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('api/faq/index.html.twig', [
            'controller_name' => 'FaqController',
        ]);
    }
        */
    /*
    #[Route('/api/information', name: 'app_information', methods: ['GET'])]
    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/Api/FaqController.php',
        ]);
    }
     */   
        
    #[Route('/api/information', name: 'app_information', methods: ['GET'])]
    public function getInformationList(InformationRepository $informationRepository, SerializerInterface $serializer): JsonResponse
    {
        $informationList = $informationRepository->findAll();
        $jsonInformationList = $serializer->serialize($informationList, 'json',['groups' => 'getInformation']);
        return new JsonResponse(
            $jsonInformationList, Response::HTTP_OK, [], true);
    }
            

}
