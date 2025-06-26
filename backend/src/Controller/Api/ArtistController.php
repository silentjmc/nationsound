<?php

namespace App\Controller\Api;

use App\Repository\ArtistRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use App\Entity\Artist;

/**
 * ArtistController is responsible for handling API requests related to artists.
 * This controller provides endpoints to retrieve a list of artists and details of a specific artist.
 */
class ArtistController extends AbstractController
{      
    /**
     * Retrieves a list of artists with published events.
     *
     * This method fetches all artists and filters them to include only those
     * who have published events linked to them. The list is sorted by artist name.
     *
     * @param ArtistRepository $artistRepository The repository to access artist data.
     * @param SerializerInterface $serializer The serializer to convert entities to JSON.
     * @return JsonResponse A JSON response containing the list of artists with published events.
     */      
    #[Route('/api/artistsList', name: 'app_api_artistsList', methods: ['GET'])]
    public function getArtistList(ArtistRepository $artistRepository, SerializerInterface $serializer): JsonResponse
    {
        // Fetch all artists
        $artistList = $artistRepository->findAll();
        // Filter to keep only artists with published events
        $artistsWithPublishedEvents = array_filter($artistList, function(Artist $artist) {
            $artist->publishedEventsLinked();
            return count($artist->getEvents()) > 0;
        });

        // Sort artists by name
        usort($artistsWithPublishedEvents, function(Artist $a, Artist $b) {
            return strcmp($a->getNameArtist(), $b->getNameArtist());
        });

        // Serialize the filtered and sorted artist list to JSON format
        $jsonArtistList = $serializer->serialize(array_values($artistsWithPublishedEvents), 'json', ['groups' => 'getArtist']);
        return new JsonResponse($jsonArtistList, Response::HTTP_OK, [], true);
    }

    /**
     * Retrieves details of a specific artist by ID.
     *
     * This method fetches an artist by its ID and checks if the artist has published events.
     * If the artist has no published events, a 404 response is returned.
     *
     * @param int $id The ID of the artist to retrieve.
     * @param ArtistRepository $artistRepository The repository to access artist data.
     * @param SerializerInterface $serializer The serializer to convert entities to JSON.
     * @return JsonResponse A JSON response containing the artist details or an error message.
     */
    #[Route('/api/artist/{id}', name: 'app_api_artist', methods: ['GET'])]
    public function getArtist(int $id, ArtistRepository $artistRepository, SerializerInterface $serializer): JsonResponse
    {
        // Find the artist by ID
        $artist = $artistRepository->find($id);
        
        // Check if the artist exists
        if ($artist) {
            // Filter published events
            $artist->publishedEventsLinked();
            // Return artist only if he has published events
            if (count($artist->getEvents()) === 0) {
                return new JsonResponse(['message' => 'Aucun événement publié pour cet artiste'], Response::HTTP_NOT_FOUND);
            }
            // Serialize the artist entity to JSON format
            $jsonArtist = $serializer->serialize($artist, 'json', ['groups' => 'getArtist']);
            return new JsonResponse($jsonArtist, Response::HTTP_OK, [], true);
        } else {
            return new JsonResponse(['message' => 'Artiste non trouvé'], Response::HTTP_NOT_FOUND);
        }
    }
}
