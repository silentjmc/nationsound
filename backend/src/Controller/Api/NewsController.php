<?php

namespace App\Controller\Api;

use App\Entity\News;
use App\Repository\NewsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class NewsController extends AbstractController
{    
    #[Route('/api/news', name: 'app_api_news', methods: ['GET'])]
    public function getNewsList(NewsRepository $newsRepository, SerializerInterface $serializer): JsonResponse
    {
        $news = $newsRepository->findBy(['publishNews' => true], ['idNews' => 'DESC']);
        $jsonNews = $serializer->serialize($news, 'json', ['groups' => ['getNews']]);
        return new JsonResponse($jsonNews, 200, [], true);
    }

    #[Route('api/news/{id}', name: 'app_api_news_by_id', methods: ['GET'])]
    public function getNewsById(News $news, SerializerInterface $serializer): JsonResponse
    {
        $jsonNews = $serializer->serialize($news, 'json', ['groups' => ['getNews']]);
        return new JsonResponse($jsonNews, 200, [], true);
    }

    #[Route('api/latestNotification', name: 'app_api_latest_notification', methods: ['GET'])]
    public function getLatestNotification(NewsRepository $newsRepository, SerializerInterface $serializer): JsonResponse
    {
        $news = $newsRepository->findLatestActiveNotification();
        if (!$news) {
            return new JsonResponse(null, 204);
        }
        $jsonNews = $serializer->serialize($news, 'json', ['groups' => ['getNews']]);
        return new JsonResponse($jsonNews, 200, [], true);
    }
}
