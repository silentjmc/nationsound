<?php

namespace App\Tests\Controller\Api;

use App\Tests\Fixtures\TestPartnerFixtures;
use App\Tests\Fixtures\TestPartnerTypeFixtures; // Dépendance pour TestPartnerFixtures
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class PartnersControllerTest extends WebTestCase
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

    public function testGetPartnersListReturnsOnlyPublishedPartners(): void
    {
        // Charger les fixtures. LiipTestFixturesBundle purge avant (redondant avec setUp mais sûr).
        // TestPartnerFixtures dépend de TestPartnerTypeFixtures, Liip devrait gérer l'ordre.
        $executor = $this->databaseTool->loadFixtures([
            TestPartnerTypeFixtures::class, // Charger les types en premier
            TestPartnerFixtures::class      // Puis les partenaires
        ]);

        /** @var \App\Entity\Partner $partner1 */
        $partner1 = $executor->getReferenceRepository()->getReference(TestPartnerFixtures::PARTNER_PUBLISHED_1_REF);
        /** @var \App\Entity\Partner $partner2 */
        $partner2 = $executor->getReferenceRepository()->getReference(TestPartnerFixtures::PARTNER_PUBLISHED_2_REF);

        $this->client->request('GET', '/api/partners');

        $this->assertResponseIsSuccessful('La requête API /api/partners a échoué.');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK, 'Le code de statut HTTP devrait être 200 OK.');
        $this->assertResponseHeaderSame('Content-Type', 'application/json', 'Le Content-Type devrait être application/json.');

        $responseContent = $this->client->getResponse()->getContent();
        $responseData = json_decode($responseContent, true);

        $this->assertNotNull($responseData, 'La réponse JSON décodée ne devrait pas être nulle.');
        $this->assertIsArray($responseData, 'La réponse JSON racine devrait être un tableau.');

        // On s'attend à 2 partenaires publiés (selon TestPartnerFixtures)
        // Le repository fait findBy(['publishPartner' => true]), pas de tri explicite, donc l'ordre peut varier.
        $this->assertCount(2, $responseData, sprintf(
            "L'API devrait retourner 2 partenaires publiés, %d reçus. Contenu: %s",
            is_array($responseData) ? count($responseData) : -1,
            $responseContent
        ));

        if (is_array($responseData) && count($responseData) === 2) {
            // Extraire les IDs pour une vérification indépendante de l'ordre
            $returnedIds = array_map(fn($p) => $p['idPartner'], $responseData);
            $expectedIds = [$partner1->getIdPartner(), $partner2->getIdPartner()];

            sort($returnedIds);
            sort($expectedIds);
            $this->assertEquals($expectedIds, $returnedIds, "Les IDs des partenaires publiés retournés ne correspondent pas.");

            // Vérifier que les partenaires retournés sont bien publiés
            foreach ($responseData as $partnerData) {
                $this->assertTrue($partnerData['publishPartner'], "Le partenaire '{$partnerData['namePartner']}' devrait être publié.");
                $this->assertArrayHasKey('idPartner', $partnerData);
                $this->assertArrayHasKey('namePartner', $partnerData);
                $this->assertArrayHasKey('typePartner', $partnerData); // Vérifier la présence du type
                $this->assertIsArray($partnerData['typePartner']);
                $this->assertArrayHasKey('titlePartnerType', $partnerData['typePartner']);
            }
        }
    }

    public function testGetPartnersListReturnsEmptyWhenNoPartnersArePublished(): void
    {
        // Charger seulement les types, mais pas de partenaire publié
        // Ou une fixture spécifique qui crée uniquement des partenaires non publiés.
        // Ici, on charge TestPartnerTypeFixtures, puis TestPartnerFixtures
        // mais on va s'assurer que notre assertion est sur une base où aucun n'est publié.
        // Pour ce test, il serait mieux d'avoir une fixture dédiée :
        // TestPartnersUnpublishedOnlyFixtures.php
        // Pour l'instant, on charge les fixtures, puis on dépublie manuellement pour le test.
        // C'est moins idéal que des fixtures dédiées mais illustre une technique.

        $executor = $this->databaseTool->loadFixtures([
            TestPartnerTypeFixtures::class,
            TestPartnerFixtures::class // Charge les 2 publiés et 1 non publié
        ]);

        // Dépublier manuellement les partenaires pour ce scénario de test
        $entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        /** @var \App\Entity\Partner $p1 */
        $p1 = $executor->getReferenceRepository()->getReference(TestPartnerFixtures::PARTNER_PUBLISHED_1_REF);
        $p1->setPublishPartner(false);
        $entityManager->persist($p1);
        /** @var \App\Entity\Partner $p2 */
        $p2 = $executor->getReferenceRepository()->getReference(TestPartnerFixtures::PARTNER_PUBLISHED_2_REF);
        $p2->setPublishPartner(false);
        $entityManager->persist($p2);
        $entityManager->flush();
        $entityManager->clear(); // Important après une modification manuelle

        $this->client->request('GET', '/api/partners');

        $this->assertResponseIsSuccessful();
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertCount(0, $responseData, 'Devrait retourner un tableau vide si aucun partenaire n\'est publié.');
    }

    public function testGetPartnersListReturnsEmptyWhenNoPartnersExist(): void
    {
        // La base est déjà purgée par setUp. On ne charge aucune fixture.
        $this->client->request('GET', '/api/partners');

        $this->assertResponseIsSuccessful();
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertCount(0, $responseData, 'Devrait retourner un tableau vide s\'il n\'y a pas de partenaires.');
    }


    protected function tearDown(): void
    {
        parent::tearDown();
        $this->databaseTool = null;
        $this->client = null;
    }
}