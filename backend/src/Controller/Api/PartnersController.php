<?php

namespace App\Controller\Api;

use App\Repository\PartnerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class PartnersController extends AbstractController
{  
    #[Route('/api/partners', name: 'app_partners', methods: ['GET'])]
    public function getPartnersList(PartnerRepository $partnersRepository, SerializerInterface $serializer): JsonResponse
    {
        $partnersList = $partnersRepository->findBy(
            ['publishPartner' => true]);
        $jsonPartnersList = $serializer->serialize($partnersList, 'json',['groups' => 'getPartners']);
        return new JsonResponse(
            $jsonPartnersList, Response::HTTP_OK, [], true);
    }
}