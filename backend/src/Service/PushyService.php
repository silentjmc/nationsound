<?php
namespace App\Service;

use App\Entity\News;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Exception;

class PushyService
{
    private $client;
    private $apiKey;
    private $logger;

    public function __construct(
        HttpClientInterface $client, 
        string $apiKey,
        LoggerInterface $logger
    ) {
        $this->client = $client;
        $this->apiKey = $apiKey;
        $this->logger = $logger;
    }
    public function sendNotification(array $data)
    {
        $payload = [
            'to' => '/topics/news',  // Envoie à tous les utilisateurs abonnés au sujet 'news'
            'notification' => [
                'title' => $data['title'],
                'body' => $data['content'],
                'badge' => 1,
            ],
            'data' => [
                'title' => $data['title'],
                'message' => $data['content'],
                'type' => $data['type'] ?? 'info'
            ]
        ];

        $this->logger->info('Sending Pushy notification', ['payload' => $payload]);

        $response = $this->client->request('POST', 'https://api.pushy.me/push?api_key=' . $this->apiKey, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->apiKey
            ],
            'json' => $payload
        ]);

        $statusCode = $response->getStatusCode();
        $responseData = $response->toArray(false);

        $this->logger->info('Pushy response', ['status_code' => $statusCode, 'response' => $responseData]);

        if ($statusCode !== 200) {
            throw new \Exception('Failed to send notification: ' . json_encode($responseData));
        }

        return $responseData;
    }

    
}