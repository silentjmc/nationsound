<?php

namespace App\Tests\Controller\Api;

// Fixtures nécessaires
use App\Tests\Fixtures\TestArtistFixtures;
use App\Tests\Fixtures\TestEventDateFixtures;
use App\Tests\Fixtures\TestEventFixtures;
use App\Tests\Fixtures\TestEventLocationFixtures;
use App\Tests\Fixtures\TestEventTypeFixtures;
use App\Tests\Fixtures\TestLocationTypeFixtures; // Dépendance de EventLocation

use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;

class EventControllerTest extends WebTestCase
{
    protected ?AbstractDatabaseTool $databaseTool = null;
    protected ?KernelBrowser $client = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();

        if (null === $this->databaseTool) {
            $this->databaseTool = $this->client->getContainer()->get(DatabaseToolCollection::class)->get();
            $this->databaseTool->setDatabaseCacheEnabled(false);
        }
        // Purge database before each test to ensure a clean state
        $this->databaseTool->loadFixtures([]);
    }

    public function testGetEventListReturnsOnlyPublishedEvents(): void
    {
        // Charger toutes les fixtures nécessaires pour créer des événements
        $this->databaseTool->loadFixtures([
            TestLocationTypeFixtures::class, // Base pour EventLocation
            TestEventLocationFixtures::class,
            TestEventTypeFixtures::class,
            TestEventDateFixtures::class,
            TestArtistFixtures::class,
            TestEventFixtures::class // Cette fixture crée 2 événements publiés et 1 non publié
        ]);

        $this->client->request('GET', '/api/event');

        $this->assertResponseIsSuccessful('La requête API /api/event a échoué.');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseContent = $this->client->getResponse()->getContent();
        $responseData = json_decode($responseContent, true);

        $this->assertNotNull($responseData, 'La réponse JSON décodée ne devrait pas être nulle.');
        $this->assertIsArray($responseData, 'La réponse JSON racine devrait être un tableau.');

        // TestEventFixtures crée 2 événements publiés.
        // EventRepository::findBy(['publishEvent' => true]) devrait les retourner.
        $this->assertCount(2, $responseData, sprintf(
            "L'API devrait retourner 2 événements publiés, %d reçus. Contenu: %s",
            is_array($responseData) ? count($responseData) : -1,
            $responseContent
        ));

        if (is_array($responseData) && count($responseData) === 2) {
            // Vérifier que tous les événements retournés sont bien publiés
            foreach ($responseData as $eventData) {
                $this->assertTrue($eventData['publishEvent'], "L'événement ID {$eventData['idEvent']} devrait être publié.");
                $this->assertArrayHasKey('artist', $eventData, "L'événement ID {$eventData['idEvent']} devrait avoir un artiste.");
                // Vous pouvez ajouter plus d'assertions sur le contenu de chaque événement
                // par exemple, vérifier que les artistes/lieux sont ceux attendus si vous récupérez les références.
            }
        }
    }

    public function testGetEventListReturnsEmptyWhenNoEventsArePublished(): void
    {
        // Charger les dépendances, mais pas TestEventFixtures,
        // ou une fixture spécifique qui ne crée que des événements non publiés.
        // Pour cet exemple, on charge seulement les dépendances, donc aucun Event n'est créé.
        $this->databaseTool->loadFixtures([
            TestLocationTypeFixtures::class,
            TestEventLocationFixtures::class,
            TestEventTypeFixtures::class,
            TestEventDateFixtures::class,
            TestArtistFixtures::class,
            // Pas de TestEventFixtures ici, ou une fixture qui ne crée que des Events non publiés
        ]);

        $this->client->request('GET', '/api/event');

        $this->assertResponseIsSuccessful();
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertCount(0, $responseData, 'Devrait retourner un tableau vide si aucun événement n\'est publié.');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->databaseTool = null;
        $this->client = null;
    }
}