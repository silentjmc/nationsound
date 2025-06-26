<?php

namespace App\Controller\Api;

use App\Repository\InformationRepository;
use App\Repository\InformationSectionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * InformationController is responsible for handling API requests related to information sections.
 * This controller provides an endpoint to retrieve a list of information sections.
 */
class InformationController extends AbstractController
{      
    /**
     * Retrieves a list of information sections.
     *
     * This method fetches all information sections sorted by their position
     * and returns them in JSON format.
     *
     * @param InformationSectionRepository $informationSectionRepository The repository to access information section data.
     * @param SerializerInterface $serializer The serializer to convert entities to JSON.
     * @return JsonResponse A JSON response containing the list of information sections.
     */
    #[Route('/api/information', name: 'app_information', methods: ['GET'])]
    public function getInformationList(InformationSectionRepository $informationSectionRepository, SerializerInterface $serializer): JsonResponse
    {
        // Fetch all information sections sorted by position
        $informationList = $informationSectionRepository->findAllSortedByPosition();
        // Serialize the information list to JSON format
        $jsonInformationList = $serializer->serialize($informationList, 'json',['groups' => 'getInformation']);
        return new JsonResponse(
            $jsonInformationList, Response::HTTP_OK, [], true);
    }  
}