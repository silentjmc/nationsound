<?php

namespace App\Controller\Api;

use App\Repository\EventLocationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class EventLocationController extends AbstractController
{        
    #[Route('/api/eventLocation', name: 'app_eventLocation', methods: ['GET'])]
    public function getEventLocationList(EventLocationRepository $eventLocationRepository, SerializerInterface $serializer): JsonResponse
    {
        $eventLocationList = $eventLocationRepository->findBy(
            ['publishEventLocation' => true]);  
        $jsonEventLocationList = $serializer->serialize($eventLocationList, 'json',['groups' => 'getEventLocation']);
        return new JsonResponse(
            $jsonEventLocationList, Response::HTTP_OK, [], true);
    }
}