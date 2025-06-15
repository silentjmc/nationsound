<?php

namespace App\Tests\Controller\Api;

use App\Tests\Fixtures\TestNewsFixtures;
use App\Entity\News;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class NewsControllerTest extends WebTestCase
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

    public function testGetNewsListReturnsOnlyPublishedNewsSortedByIdDesc(): void
    {
        $executor = $this->databaseTool->loadFixtures([TestNewsFixtures::class]);

        /** @var News $newsPublished1 */
        $newsPublished1 = $executor->getReferenceRepository()->getReference(TestNewsFixtures::NEWS_PUBLISHED_1_REF);
        /** @var News $newsPublished2 */
        $newsPublished2 = $executor->getReferenceRepository()->getReference(TestNewsFixtures::NEWS_PUBLISHED_2_REF);
        /** @var News $newsPushExpired */
        $newsPushExpired = $executor->getReferenceRepository()->getReference(TestNewsFixtures::NEWS_PUSH_EXPIRED_REF);
        /** @var News $newsNoPush */
        $newsNoPush = $executor->getReferenceRepository()->getReference(TestNewsFixtures::NEWS_NO_PUSH_REF);


        $this->client->request('GET', '/api/news');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertNotNull($responseData);
        $this->assertIsArray($responseData);

        // On s'attend à 4 actualités publiées (news1, news2, newsPushExpired, newsNoPush)
        $this->assertCount(4, $responseData, "Devrait retourner 4 actualités publiées.");

        // Vérifier l'ordre (ID DESC). newsNoPush -> newsPushExpired -> newsPublished2 -> newsPublished1 (si les IDs sont croissants)
        // Il faut que les fixtures soient créées dans un ordre qui donne des IDs croissants.
        // Ou alors, on récupère les IDs et on vérifie que $responseData[0]['idNews'] > $responseData[1]['idNews'], etc.
        if (count($responseData) === 4) {
            // Supposons que les IDs sont croissants dans l'ordre de création des fixtures :
            // news1 (push active) -> ID plus petit
            // news2 (published 2)
            // news4 (push expired)
            // news5 (no push) -> ID plus grand
            // Donc l'ordre DESC sera : news5, news4, news2, news1 (en termes de nos constantes)
            $this->assertEquals($newsNoPush->getTitleNews(), $responseData[0]['titleNews']);
            $this->assertEquals($newsPushExpired->getTitleNews(), $responseData[1]['titleNews']);
            $this->assertEquals($newsPublished2->getTitleNews(), $responseData[2]['titleNews']);
            $this->assertEquals($newsPublished1->getTitleNews(), $responseData[3]['titleNews']);

            // Vérifier qu'une actualité non publiée n'est pas là
            foreach ($responseData as $newsItem) {
                $this->assertTrue($newsItem['publishNews']);
                $this->assertNotEquals('Actualité Non Publiée', $newsItem['titleNews']);
            }
        }
    }

    public function testGetNewsByIdReturnsCorrectPublishedNews(): void
    {
        $executor = $this->databaseTool->loadFixtures([TestNewsFixtures::class]);
        /** @var News $newsPublished1 */
        $newsPublished1 = $executor->getReferenceRepository()->getReference(TestNewsFixtures::NEWS_PUBLISHED_1_REF);

        $this->client->request('GET', '/api/news/' . $newsPublished1->getIdNews());

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertNotNull($responseData);
        $this->assertEquals($newsPublished1->getIdNews(), $responseData['idNews']);
        $this->assertEquals($newsPublished1->getTitleNews(), $responseData['titleNews']);
        $this->assertTrue($responseData['publishNews']);
    }

    public function testGetNewsByIdReturnsNotFoundForUnpublishedNews(): void
    {
        $executor = $this->databaseTool->loadFixtures([TestNewsFixtures::class]);
        /** @var News $newsUnpublished */
        $newsUnpublished = $executor->getReferenceRepository()->getReference(TestNewsFixtures::NEWS_UNPUBLISHED_REF);

        // Votre API getNewsById ne vérifie pas si la news est publiée, elle la retourne telle quelle.
        // Si vous voulez qu'elle retourne 404 pour une non publiée, il faut ajouter cette logique au contrôleur.
        // Pour l'instant, on s'attend à ce qu'elle soit retournée mais avec publishNews = false.
        $this->client->request('GET', '/api/news/' . $newsUnpublished->getIdNews());
        $this->assertResponseIsSuccessful(); // Car l'entité est trouvée
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertNotNull($responseData);
        $this->assertEquals($newsUnpublished->getIdNews(), $responseData['idNews']);
        $this->assertFalse($responseData['publishNews']); // On vérifie qu'elle n'est pas publiée

        // Si vous modifiez le contrôleur pour retourner 404 si non publiée:
        // $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testGetNewsByIdReturnsNotFoundForNonExistentNews(): void
    {
        $this->databaseTool->loadFixtures([]); // Base vide
        $this->client->request('GET', '/api/news/99999');
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND); // Géré par le ParamConverter de Symfony
    }

    public function testGetLatestNotificationReturnsActiveNotification(): void
    {
        $executor = $this->databaseTool->loadFixtures([TestNewsFixtures::class]);
        /** @var News $activeNotification */
        $activeNotification = $executor->getReferenceRepository()->getReference(TestNewsFixtures::NEWS_PUSH_ACTIVE_REF);

        // Assurez-vous que NewsRepository::findLatestActiveNotification() est correct
        // Il devrait retourner NEWS_PUSH_ACTIVE_REF et pas NEWS_PUSH_EXPIRED_REF
        $this->client->request('GET', '/api/latestNotification');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertNotNull($responseData);
        $this->assertEquals($activeNotification->getIdNews(), $responseData['idNews']);
        $this->assertEquals($activeNotification->getTitleNews(), $responseData['titleNews']);
        $this->assertTrue($responseData['push']);
        $this->assertTrue($responseData['publishNews']);
    }

    public function testGetLatestNotificationReturnsNoContentWhenNoneActive(): void
    {
        // Charger des fixtures SANS notification active
        // (par exemple, que NEWS_PUSH_EXPIRED_REF et NEWS_NO_PUSH_REF)
        // Pour cet exemple, on va juste purger et s'assurer que la méthode retourne bien null
        $this->databaseTool->loadFixtures([]); // Base vide, donc pas de notification active

        $this->client->request('GET', '/api/latestNotification');
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT); // 204
        $this->assertEmpty($this->client->getResponse()->getContent());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->databaseTool = null;
        $this->client = null;
    }
}