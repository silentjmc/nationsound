<?php

namespace App\Controller\Api;

use App\Repository\ArtistRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

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
            
    #[Route('/api/artist', name: 'app_api_artist', methods: ['GET'])]
    public function getArtistList(ArtistRepository $artistRepository, SerializerInterface $serializer): JsonResponse
    {
        $artistList = $artistRepository->findAll();
        
        // Filtrer les événements publiés pour chaque artiste
        foreach ($artistList as $artist) {
            $artist->publishedEventsLinked();
        }

        $jsonArtistList = $serializer->serialize($artistList, 'json', ['groups' => 'getArtist']);
        return new JsonResponse($jsonArtistList, Response::HTTP_OK, [], true);
    }
}
