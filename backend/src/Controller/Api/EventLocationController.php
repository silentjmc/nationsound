<?php

namespace App\Controller\Api;

use App\Repository\EventLocationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * EventLocationController is responsible for handling API requests related to event locations.
 * This controller provides an endpoint to retrieve a list of event locations that are published.
 */
class EventLocationController extends AbstractController
{   
    /**
     * Retrieves a list of published event locations.
     *
     * This method fetches all event locations that are marked as published
     * and returns them in JSON format.
     *
     * @param EventLocationRepository $eventLocationRepository The repository to access event location data.
     * @param SerializerInterface $serializer The serializer to convert entities to JSON.
     * @return JsonResponse A JSON response containing the list of published event locations.
     */     
    #[Route('/api/eventLocation', name: 'app_eventLocation', methods: ['GET'])]
    public function getEventLocationList(EventLocationRepository $eventLocationRepository, SerializerInterface $serializer): JsonResponse
    {
        // Fetch all event locations that are published
        $eventLocationList = $eventLocationRepository->findBy(['publishEventLocation' => true]);
        // Serialize the event location list to JSON format  
        $jsonEventLocationList = $serializer->serialize($eventLocationList, 'json',['groups' => 'getEventLocation']);
        return new JsonResponse(
            $jsonEventLocationList, Response::HTTP_OK, [], true);
    }
}