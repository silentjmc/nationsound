<?php

namespace App\Controller\Api;

use App\Repository\InformationRepository;
use App\Repository\InformationSectionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class InformationController extends AbstractController
{      
    #[Route('/api/information', name: 'app_information', methods: ['GET'])]
    public function getInformationList(InformationSectionRepository $informationSectionRepository, SerializerInterface $serializer): JsonResponse
    {
        $informationList = $informationSectionRepository->findAllSortedByPosition();
        $jsonInformationList = $serializer->serialize($informationList, 'json',['groups' => 'getInformation']);
        return new JsonResponse(
            $jsonInformationList, Response::HTTP_OK, [], true);
    }  
}