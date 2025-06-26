<?php

namespace App\Controller\Api;

use App\Repository\PartnerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * PartnersController is responsible for handling API requests related to partners.
 * This controller provides an endpoint to retrieve a list of partners that are published.
 */
class PartnersController extends AbstractController
{  
    /**
     * Retrieves a list of published partners.
     *
     * This method fetches all partners that are marked as published
     * and returns them in JSON format.
     *
     * @param PartnerRepository $partnersRepository The repository to access partner data.
     * @param SerializerInterface $serializer The serializer to convert entities to JSON.
     * @return JsonResponse A JSON response containing the list of published partners.
     */
    #[Route('/api/partners', name: 'app_partners', methods: ['GET'])]
    public function getPartnersList(PartnerRepository $partnersRepository, SerializerInterface $serializer): JsonResponse
    {
        // Fetch all partners that are published
        $partnersList = $partnersRepository->findBy(
            ['publishPartner' => true]);

        // Serialize the partners list to JSON format
        $jsonPartnersList = $serializer->serialize($partnersList, 'json',['groups' => 'getPartners']);
        return new JsonResponse(
            $jsonPartnersList, Response::HTTP_OK, [], true);
    }
}