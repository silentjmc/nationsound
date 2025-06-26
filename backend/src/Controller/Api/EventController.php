<?php

namespace App\Controller\Api;

use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * EventController is responsible for handling API requests related to events.
 * This controller provides an endpoint to retrieve a list of events that are published.
 */
class EventController extends AbstractController
{   
    /**
     * Retrieves a list of published events.
     *
     * This method fetches all events that are marked as published
     * and returns them in JSON format.
     *
     * @param EventRepository $eventRepository The repository to access event data.
     * @param SerializerInterface $serializer The serializer to convert entities to JSON.
     * @return JsonResponse A JSON response containing the list of published events.
     */    
    #[Route('/api/event', name: 'app_api_event', methods: ['GET'])]
    public function getEventList(EventRepository $eventRepository, SerializerInterface $serializer): JsonResponse
    {
        // Fetch all events that are published
        $eventList = $eventRepository->findBy(['publishEvent' => true]);
        // Serialize the event list to JSON format
        $jsonEventList = $serializer->serialize($eventList, 'json',['groups' => 'getEvent']);
        return new JsonResponse(
            $jsonEventList, Response::HTTP_OK, [], true);
    }
}
