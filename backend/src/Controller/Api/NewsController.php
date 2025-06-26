<?php

namespace App\Controller\Api;

use App\Entity\News;
use App\Repository\NewsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * NewsController is responsible for handling API requests related to news.
 * This controller provides endpoints to retrieve a list of news articles,
 * news by ID, and the latest notification.
 */
class NewsController extends AbstractController
{    
    /**
     * Retrieves a list of published news articles.
     *
     * This method fetches all news articles that are marked as published
     * and returns them in JSON format.
     *
     * @param NewsRepository $newsRepository The repository to access news data.
     * @param SerializerInterface $serializer The serializer to convert entities to JSON.
     * @return JsonResponse A JSON response containing the list of published news articles.
     */
    #[Route('/api/news', name: 'app_api_news', methods: ['GET'])]
    public function getNewsList(NewsRepository $newsRepository, SerializerInterface $serializer): JsonResponse
    {
        // Fetch all news articles that are published
        $news = $newsRepository->findBy(['publishNews' => true], ['idNews' => 'DESC']);
        // Serialize the news list to JSON format
        $jsonNews = $serializer->serialize($news, 'json', ['groups' => ['getNews']]);
        return new JsonResponse($jsonNews, Response::HTTP_OK, [], true);
    }

    /**
     * Retrieves a specific news article by its ID.
     *
     * This method fetches a news article based on the provided ID
     * and returns it in JSON format.
     *
     * @param News $news The news entity to be serialized.
     * @param SerializerInterface $serializer The serializer to convert entities to JSON.
     * @return JsonResponse A JSON response containing the news article.
     */
    #[Route('api/news/{id}', name: 'app_api_news_by_id', methods: ['GET'])]
    public function getNewsById(News $news, SerializerInterface $serializer): JsonResponse
    {
        // serialize the news entity to JSON format
        $jsonNews = $serializer->serialize($news, 'json', ['groups' => ['getNews']]);
        return new JsonResponse($jsonNews, Response::HTTP_OK, [], true);
    }

    /**
     * Retrieves the latest active notification.
     *
     * This method fetches the most recent active notification
     * and returns it in JSON format.
     *
     * @param NewsRepository $newsRepository The repository to access news data.
     * @param SerializerInterface $serializer The serializer to convert entities to JSON.
     * @return JsonResponse A JSON response containing the latest notification or a 204 No Content response if none exists.
     */
    #[Route('api/latestNotification', name: 'app_api_latest_notification', methods: ['GET'])]
    public function getLatestNotification(NewsRepository $newsRepository, SerializerInterface $serializer): JsonResponse
    {
        // Fetch the latest active notification
        $news = $newsRepository->findLatestActiveNotification();
        // If no news is found, return a 204 No Content response
        if (!$news) {
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }
        // Serialize the news entity to JSON format
        $jsonNews = $serializer->serialize($news, 'json', ['groups' => ['getNews']]);
        return new JsonResponse($jsonNews, Response::HTTP_OK, [], true);
    }
}