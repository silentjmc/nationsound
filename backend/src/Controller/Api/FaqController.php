<?php

namespace App\Controller\Api;

use App\Repository\FaqRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * FaqController is responsible for handling API requests related to frequently asked questions (FAQ).
 * This controller provides an endpoint to retrieve a list of FAQs sorted by their position.
 */
class FaqController extends AbstractController
{   
    /**
     * Retrieves a list of FAQs sorted by their position.
     *
     * This method fetches all FAQs from the repository and returns them in JSON format.
     *
     * @param FaqRepository $faqRepository The repository to access FAQ data.
     * @param SerializerInterface $serializer The serializer to convert entities to JSON.
     * @return JsonResponse A JSON response containing the list of FAQs.
     */     
    #[Route('/api/faq', name: 'app_api_faq', methods: ['GET'])]
    public function getFaqList(FaqRepository $faqRepository, SerializerInterface $serializer): JsonResponse
    {
        // Fetch all FAQs sorted by position
        $faqList = $faqRepository->findAllSortedByPosition();
        // Serialize the FAQ list to JSON format
        $jsonFaqList = $serializer->serialize($faqList, 'json',['groups' => 'getFaq']);
        return new JsonResponse(
            $jsonFaqList, Response::HTTP_OK, [], true);
    }      
}