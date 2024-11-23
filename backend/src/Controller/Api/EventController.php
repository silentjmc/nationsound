<?php

namespace App\Controller\Api;

use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class EventController extends AbstractController
{
    /*
    #[Route('/api/faq', name: 'app_faq', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('api/faq/index.html.twig', [
            'controller_name' => 'FaqController',
        ]);
    }
        */
    /*
    #[Route('/api/faq', name: 'app_faq', methods: ['GET'])]
    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/Api/FaqController.php',
        ]);
    }
     */   
        
    #[Route('/api/event', name: 'app_api_event', methods: ['GET'])]
    public function getEventList(EventRepository $eventRepository, SerializerInterface $serializer): JsonResponse
    {
        //$eventList = $eventRepository->findAll();
        $eventList = $eventRepository->findBy(
            ['publish' => true]);
        $jsonEventList = $serializer->serialize($eventList, 'json',['groups' => 'getEvent']);
        return new JsonResponse(
            $jsonEventList, Response::HTTP_OK, [], true);
    }
            

}
