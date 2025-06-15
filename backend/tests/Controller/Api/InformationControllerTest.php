<?php

namespace App\Tests\Controller\Api;

use App\Tests\Fixtures\TestInformationFixtures;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;

class InformationControllerTest extends WebTestCase
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
        $this->databaseTool->loadFixtures([]);;
    }

    public function testGetInformationListReturnsSectionsSortedByPosition(): void
    {
        // Charger les fixtures
        $this->databaseTool->loadFixtures([
            TestInformationFixtures::class
        ]);

        $this->client->request('GET', '/api/information');

        $this->assertResponseIsSuccessful('La requête API /api/information a échoué.');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseContent = $this->client->getResponse()->getContent();
        $responseData = json_decode($responseContent, true);

        $this->assertNotNull($responseData, 'La réponse JSON décodée ne devrait pas être nulle.');
        $this->assertIsArray($responseData, 'La réponse JSON racine devrait être un tableau.');

        // TestInformationFixtures crée 2 sections.
        // La méthode InformationSectionRepository::findAllSortedByPosition()
        // ne semble pas filtrer par un quelconque statut de publication pour les sections elles-mêmes.
        // Donc, on s'attend à 2 sections.
        $this->assertCount(2, $responseData, sprintf(
            "L'API devrait retourner 2 sections d'information, %d reçues. Contenu: %s",
            is_array($responseData) ? count($responseData) : -1,
            $responseContent
        ));

        if (is_array($responseData) && count($responseData) === 2) {
            // Section 1 (Pratique Test, position 0)
            $section1 = $responseData[0];
            $this->assertEquals('Pratique Test', $section1['sectionLabel']);
            $this->assertEquals(0, $section1['positionInformationSection']);
            $this->assertArrayHasKey('information', $section1, "La section devrait avoir une collection 'information'.");
            $this->assertIsArray($section1['information'], "La collection 'information' devrait être un tableau.");

            // Vérifier les informations DANS la section 1
            // Si votre serializer et le groupe 'getInformation' ne retournent que les 'Information' publiées
            $publishedInfoInSection1 = array_filter($section1['information'], fn($info) => $info['publishInformation']);
            $this->assertCount(2, $publishedInfoInSection1, "La section 'Pratique Test' devrait contenir 2 informations publiées.");
            if(count($publishedInfoInSection1) === 2) {
                $this->assertEquals('Horaires d\'ouverture Test', $publishedInfoInSection1[0]['titleInformation']);
                $this->assertEquals('Tarifs et Billetterie Test', $publishedInfoInSection1[1]['titleInformation']);
            }


            // Section 2 (Acces Test, position 1)
            $section2 = $responseData[1];
            $this->assertEquals('Acces Test', $section2['sectionLabel']);
            $this->assertEquals(1, $section2['positionInformationSection']);
            $this->assertArrayHasKey('information', $section2);
            $this->assertIsArray($section2['information']);

            // Vérifier les informations DANS la section 2
            $publishedInfoInSection2 = array_filter($section2['information'], fn($info) => $info['publishInformation']);
            $this->assertCount(1, $publishedInfoInSection2, "La section 'Acces Test' devrait contenir 1 information publiée.");
            if(count($publishedInfoInSection2) === 1) {
                $this->assertEquals('Parking Test', $publishedInfoInSection2[0]['titleInformation']);
            }

            // S'assurer que l'information non publiée n'est pas dans la section 2 (si filtrage)
            $foundUnpublished = false;
            foreach ($section2['information'] as $infoData) {
                if ($infoData['titleInformation'] === 'Transports en commun (Non Publié)') {
                    $foundUnpublished = true;
                    // Si elle est trouvée, elle devrait avoir publishInformation = false
                    $this->assertFalse($infoData['publishInformation'], "L'information sur les transports devrait être marquée non publiée.");
                }
            }
            // Si votre API/Serializer est censé EXCLURE les informations non publiées de la collection,
            // alors $foundUnpublished devrait être false.
            // Si elle les inclut mais avec le flag 'publishInformation: false', alors $foundUnpublished peut être true.
            // Adaptez cette assertion à votre comportement attendu.
            // Pour cet exemple, je suppose que le serializer INCLUT tout mais avec le flag correct.
            // Si le serializer EXCLUT, alors la vérification $publishedInfoInSection2 suffit.
        }
    }

    public function testGetInformationListReturnsEmptyWhenNoSectionsExist(): void
    {
        $this->databaseTool->loadFixtures([]); // Purge la base

        $this->client->request('GET', '/api/information');

        $this->assertResponseIsSuccessful();
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertCount(0, $responseData, 'Devrait retourner un tableau vide s\'il n\'y a pas de sections.');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->databaseTool = null;
        $this->client = null;
    }
}