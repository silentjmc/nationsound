<?php

namespace App\Controller\Api;

use App\Repository\ArtistRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use App\Entity\Artist;

class ArtistController extends AbstractController
{            
    #[Route('/api/artistsList', name: 'app_api_artistsList', methods: ['GET'])]
    public function getArtistList(ArtistRepository $artistRepository, SerializerInterface $serializer): JsonResponse
    {
        $artistList = $artistRepository->findAll();
        // Filter to keep only artists with published events
        $artistsWithPublishedEvents = array_filter($artistList, function(Artist $artist) {
            $artist->publishedEventsLinked();
            return count($artist->getEvents()) > 0;
        });

        usort($artistsWithPublishedEvents, function(Artist $a, Artist $b) {
            return strcmp($a->getNameArtist(), $b->getNameArtist());
        });

        $jsonArtistList = $serializer->serialize(array_values($artistsWithPublishedEvents), 'json', ['groups' => 'getArtist']);
        return new JsonResponse($jsonArtistList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/artist/{id}', name: 'app_api_artist', methods: ['GET'])]
    public function getArtist(int $id, ArtistRepository $artistRepository, SerializerInterface $serializer): JsonResponse
    {
        $artist = $artistRepository->find($id);
        
        if ($artist) {
            // Filter published events
            $artist->publishedEventsLinked();
            // Return artist only if he has published events
            if (count($artist->getEvents()) === 0) {
                return new JsonResponse(['message' => 'Aucun événement publié pour cet artiste'], Response::HTTP_NOT_FOUND);
            }
            $jsonArtist = $serializer->serialize($artist, 'json', ['groups' => 'getArtist']);
            return new JsonResponse($jsonArtist, Response::HTTP_OK, [], true);
        } else {
            return new JsonResponse(['message' => 'Artiste non trouvé'], Response::HTTP_NOT_FOUND);
        }
    }
}
