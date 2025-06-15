<?php

namespace App\Tests\Controller\Api;

use App\Entity\Faq;
use App\Tests\Fixtures\TestFaqFixtures;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class FaqControllerTest extends WebTestCase
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

    // Test to ensure that the API returns a list of published FAQs sorted by position
    public function testGetFaqListReturnsOnlyPublishedFaqsAndSorted(): void
        {
        // Charger les fixtures : 2 FAQs publiées (pos 0, 1) et 1 non publiée (pos 2)
        $this->databaseTool->loadFixtures([
            TestFaqFixtures::class
        ]);

        $this->client->request('GET', '/api/faq');

        $this->assertResponseIsSuccessful('La requête API /api/faq a échoué.');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK, 'Le code de statut HTTP devrait être 200 OK.');
        $this->assertResponseHeaderSame('Content-Type', 'application/json', 'Le Content-Type devrait être application/json.');

        $responseContent = $this->client->getResponse()->getContent();
        $responseData = json_decode($responseContent, true);
        fwrite(STDERR, "Réponse API reçue : " . $responseContent . "\n");

        $this->assertNotNull($responseData, 'La réponse JSON décodée ne devrait pas être nulle.');
        $this->assertIsArray($responseData, 'La réponse JSON racine devrait être un tableau.');

        $this->assertCount(2, $responseData, sprintf(
            "L'API devrait retourner 2 FAQs publiées, %d reçues. Contenu reçu : %s",
            count($responseData),
            $responseContent // Affiche le JSON brut en cas d'échec du count
        ));

        // Vérifications plus détaillées si le compte est correct
        if (is_array($responseData) && count($responseData) === 2) {
            // FAQ 1 (Position 0)
            $faq1 = $responseData[0];
            $this->assertEquals('Question Publiée Un (Fixture Complète)', $faq1['question'], "La question de la première FAQ n'est pas correcte.");
            $this->assertEquals(0, $faq1['positionFaq'], "La position de la première FAQ n'est pas correcte.");
            $this->assertTrue($faq1['publishFaq'], "La première FAQ devrait être publiée.");
            $this->assertArrayHasKey('idFaq', $faq1, "La clé 'idFaq' manque pour la première FAQ.");
            $this->assertArrayHasKey('reponse', $faq1, "La clé 'reponse' manque pour la première FAQ.");

            // FAQ 2 (Position 1)
            $faq2 = $responseData[1];
            $this->assertEquals('Question Publiée Deux (Fixture Complète)', $faq2['question'], "La question de la deuxième FAQ n'est pas correcte.");
            $this->assertEquals(1, $faq2['positionFaq'], "La position de la deuxième FAQ n'est pas correcte.");
            $this->assertTrue($faq2['publishFaq'], "La deuxième FAQ devrait être publiée.");
            $this->assertArrayHasKey('idFaq', $faq2, "La clé 'idFaq' manque pour la deuxième FAQ.");
            $this->assertArrayHasKey('reponse', $faq2, "La clé 'reponse' manque pour la deuxième FAQ.");

            // S'assurer que la FAQ non publiée n'est PAS présente
            foreach ($responseData as $faqData) {
                $this->assertNotEquals('Question Non Publiée (Fixture Complète)', $faqData['question'], "La FAQ non publiée ne devrait pas être retournée par l'API.");
            }
        }
    }

    //Test to ensure that the API returns an empty list when no FAQs are published or exist
    public function testGetFaqListReturnsEmptyWhenNoFaqsArePublishedOrExist(): void
    {
        $this->client->request('GET', '/api/faq');
        $this->assertResponseIsSuccessful();
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertCount(0, $responseData, 'Devrait retourner un tableau vide si aucune fixture n\'est chargée (base purgée).');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->databaseTool = null;
        $this->client = null;
    }
}