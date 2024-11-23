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
      /*  
    #[Route('/api/artist', name: 'app_api_artist', methods: ['GET'])]
    public function getArtistList(ArtistRepository $artistRepository, SerializerInterface $serializer): JsonResponse
    {
        //$eventList = $eventRepository->findAll();
        $artistList = $artistRepository->findAll();
        $jsonArtistList = $serializer->serialize($artistList, 'json',['groups' => 'getArtist']);
        return new JsonResponse(
            $jsonArtistList, Response::HTTP_OK, [], true);
    }*/
            
    #[Route('/api/artistsList', name: 'app_api_artistsList', methods: ['GET'])]
    public function getArtistList(ArtistRepository $artistRepository, SerializerInterface $serializer): JsonResponse
    {
        $artistList = $artistRepository->findAll();


        // Filtrer pour ne garder que les artistes avec des événements publiés
        $artistsWithPublishedEvents = array_filter($artistList, function(Artist $artist) {
            // Filtrer les événements publiés
            $artist->publishedEventsLinked();
            // Ne garder que les artistes qui ont au moins un événement publié
            return count($artist->getEvents()) > 0;
        });

        $jsonArtistList = $serializer->serialize(array_values($artistsWithPublishedEvents), 'json', ['groups' => 'getArtist']);
        return new JsonResponse($jsonArtistList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/artist/{id}', name: 'app_api_artist', methods: ['GET'])]
    public function getArtist(int $id, ArtistRepository $artistRepository, SerializerInterface $serializer): JsonResponse
    {
        $artist = $artistRepository->find($id);
        
        if ($artist) {
            // Filtrer les événements publiés
            $artist->publishedEventsLinked();

            // Ne retourner l'artiste que s'il a des événements publiés
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
