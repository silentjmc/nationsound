<?php

namespace App\Controller\Api;

use App\Repository\FaqRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class FaqController extends AbstractController
{        
    #[Route('/api/faq', name: 'app_api_faq', methods: ['GET'])]
    public function getFaqList(FaqRepository $faqRepository, SerializerInterface $serializer): JsonResponse
    {
        $faqList = $faqRepository->findAllSortedByPosition();
        $jsonFaqList = $serializer->serialize($faqList, 'json',['groups' => 'getFaq']);
        return new JsonResponse(
            $jsonFaqList, Response::HTTP_OK, [], true);
    }      
}