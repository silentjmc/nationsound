<?php

namespace App\Tests\Controller\Admin;

use App\Controller\Admin\DashboardController;
use App\Controller\Admin\NewsCrudController;
use App\Entity\News;
use App\Entity\User;
use App\Tests\Fixtures\TestNewsFixtures;
use App\Tests\Fixtures\TestRoleFixtures;
use App\Tests\Fixtures\TestUserFixtures;
use App\Tests\Trait\PublishActionTestTrait;
use EasyCorp\Bundle\EasyAdminBundle\Test\AbstractCrudTestCase;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Doctrine\ORM\EntityManagerInterface;

class NewsCrudControllerTest extends AbstractCrudTestCase
{
    protected AbstractDatabaseTool $databaseTool;
    protected EntityManagerInterface $entityManager;
    private ?User $adminUser = null;
    use PublishActionTestTrait;

    protected function getControllerFqcn(): string
    {
        return NewsCrudController::class;
    }

    protected function getDashboardFqcn(): string
    {
        return DashboardController::class;
    }

    public function setUp(): void
    {
        parent::setUp();
        $container = static::getContainer();
        
        $this->databaseTool = $container->get(DatabaseToolCollection::class)->get();
        if ($this->databaseTool instanceof AbstractDatabaseTool) {
            $this->databaseTool->setDatabaseCacheEnabled(false);
        }

        $em = $container->get('doctrine.orm.entity_manager');
        if ($em instanceof EntityManagerInterface) {
            $this->entityManager = $em;
        } else {
            throw new \LogicException('EntityManagerInterface not found in container for tests.');
        }
    }

    private function loadFixturesAndLoginAdmin(array $fixtureClasses = []): \Doctrine\Common\DataFixtures\ReferenceRepository
    {
        // 1. Load all required fixtures for THIS test. This will purge and reload.
        $allFixturesToLoad = array_unique(array_merge([
            TestRoleFixtures::class,
            TestUserFixtures::class,
        ], $fixtureClasses));
        $executor = $this->databaseTool->loadFixtures($allFixturesToLoad);
        
        // 2. Clear the EntityManager.
        $this->entityManager->clear();
        
        // 3. Retrieve the user from the freshly loaded database state
        $userRepo = $this->entityManager->getRepository(User::class);
        $this->adminUser = $userRepo->findOneBy(['email' => 'admin@email.com']);

        // Assert that the user exists (important for debugging)
        $this->assertNotNull($this->adminUser, "Admin user 'admin@email.com' not found after fixture load. Check UserFixtures.");

        // 4. Log in the retrieved user to the test client
        $this->client->loginUser($this->adminUser);

        // Return the reference repository so tests can get entity instances by reference
        return $executor->getReferenceRepository();
    }

    public function testIndexPageLoads(): void
    {
        $this->loadFixturesAndLoginAdmin([TestNewsFixtures::class]);
        
        $indexUrl = $this->generateIndexUrl();
        $this->client->request('GET', $indexUrl);
        
        static::assertResponseIsSuccessful("La page d'index des Actualités devrait se charger.");
        static::assertPageTitleContains('Actualités'); 
    }

    public function testNewPageLoadsSuccessfully(): void
    {
        $this->loadFixturesAndLoginAdmin([]); // User logged in, database purged

        $this->client->request('GET', $this->generateNewFormUrl());
        static::assertResponseIsSuccessful("La page de création d'Actualité devrait se charger.");
        static::assertPageTitleContains('Ajouter une actualité');

        $crawler = $this->client->getCrawler();
        $this->assertCount(1, $crawler->filter('input[name="News[titleNews]"]'), "Le champ 'titleNews' devrait exister.");
        $this->assertCount(1, $crawler->filter('textarea[name="News[contentNews]"]'), "Le champ 'contentNews' devrait exister.");
        $this->assertCount(1, $crawler->filter('select[name="News[typeNews]"]'), "Le champ 'typeNews' devrait exister.");
        $this->assertCount(1, $crawler->filter('input[name="News[publishNews]"]'), "Le champ 'publishNews' devrait exister.");
        $this->assertCount(1, $crawler->filter('input[name="News[push]"]'), "Le champ 'push' devrait exister.");
    }

    public function testCreateNewNewsSuccessfully(): void
    {
        $this->loadFixturesAndLoginAdmin([]); // User logged in, database purged

        $testTitle = 'Nouvelle Actualité de Test CRUD';
        $testContent = 'Contenu de cette nouvelle actualité de test.';

        $this->client->request('GET', $this->generateNewFormUrl());
        static::assertResponseIsSuccessful();

        $this->client->submitForm('Créer l\'actualité', [
            'News[titleNews]' => $testTitle,
            'News[contentNews]' => $testContent,
            'News[typeNews]' => 'primary', // 'Normal'
            'News[publishNews]' => true,
            'News[push]' => false,
        ]);

        //Verify that the response redirects to the index page
        $expectedIndexUrl = $this->generateIndexUrl();
        static::assertResponseRedirects($expectedIndexUrl, null, "Devrait rediriger vers l'index après création.");

        // Verfiy that the entity exists in the database
        $this->entityManager->clear();
        $newNews = $this->entityManager->getRepository(News::class)->findOneBy(['titleNews' => $testTitle]);
        $this->assertNotNull($newNews, "La nouvelle actualité devrait exister en base de données.");
        if ($newNews) {
            $this->assertEquals($testContent, $newNews->getContentNews());
            $this->assertTrue($newNews->isPublishNews());
            $this->assertEquals('primary', $newNews->getTypeNews());
        }
    }

    public function testEditPageLoadsSuccessfully(): void
    {
        $referenceRepository = $this->loadFixturesAndLoginAdmin([TestNewsFixtures::class]);
        $newsToEdit = $referenceRepository->getReference(TestNewsFixtures::NEWS_PUBLISHED_1_REF);
        $this->assertNotNull($newsToEdit);

        $this->client->request('GET', $this->generateEditFormUrl($newsToEdit->getIdNews()));
        static::assertResponseIsSuccessful("La page d'édition devrait se charger.");
        static::assertPageTitleContains('Modifier Actualité'); 

        $crawler = $this->client->getCrawler();
        $this->assertStringContainsString($newsToEdit->getTitleNews(), $crawler->filter('input[name="News[titleNews]"]')->attr('value'));
        $this->assertStringContainsString($newsToEdit->getContentNews(), $crawler->filter('textarea[name="News[contentNews]"]')->text());
    }

    public function testUpdateExistingNewsSuccessfully(): void
    {
        $referenceRepository = $this->loadFixturesAndLoginAdmin([TestNewsFixtures::class]);
        /** @var News $newsToUpdate */
        $newsToUpdate = $referenceRepository->getReference(TestNewsFixtures::NEWS_PUBLISHED_1_REF);
        $this->assertNotNull($newsToUpdate);
        $newsId = $newsToUpdate->getIdNews();

        $updatedTitle = "Titre d'Actualité Mis à Jour !";
        $updatedType = 'danger'; // Urgent

        $this->client->request('GET', $this->generateEditFormUrl($newsId));
        static::assertResponseIsSuccessful();

        $this->client->submitForm('Sauvegarder les modifications', [ // Adaptez le texte
            'News[titleNews]' => $updatedTitle,
            'News[typeNews]' => $updatedType,
            'News[publishNews]' => false,
            'News[push]' => false,
        ]);

        $expectedIndexUrl = $this->generateIndexUrl();
        static::assertResponseRedirects($expectedIndexUrl, null, "Devrait rediriger vers l'index après mise à jour.");

        $this->entityManager->clear();
        $updatedNews = $this->entityManager->getRepository(News::class)->find($newsId);
        $this->assertNotNull($updatedNews);
        $this->assertEquals($updatedTitle, $updatedNews->getTitleNews());
        $this->assertEquals($updatedType, $updatedNews->getTypeNews());
        $this->assertFalse($updatedNews->isPublishNews(), "L'actualité devrait être dépubliée.");
        $this->assertFalse($updatedNews->isPush(), "Le push devrait être désactivé si l'actualité est dépubliée.");
    }

    public function testPublishAction(): void
    {
        $this->publishAction(
            entity: 'News',
            fixtureClass: TestNewsFixtures::class, 
            fixtureReference: TestNewsFixtures::NEWS_PUSH_NOT_PUBLISHED_REF, 
            action : 'publish');
    }

     public function testUnpublishActionMakesNewsUnpublishedAndDisablesPush(): void
    {
        $referenceRepository = $this->loadFixturesAndLoginAdmin([TestNewsFixtures::class]);
        // Utiliser une news qui est publiée ET notifiée pour tester toute la logique du message flash
        $newsToUnpublish = $referenceRepository->getReference(TestNewsFixtures::NEWS_PUSH_ACTIVE_REF);
        $this->assertNotNull($newsToUnpublish, "Fixture NEWS_PUSH_ACTIVE_REF non trouvée.");
        $this->assertTrue($newsToUnpublish->isPublishNews(), "L'actualité doit être publiée initialement.");
        $this->assertTrue($newsToUnpublish->isPush(), "L'actualité doit avoir le push activé initialement.");
        $newsId = $newsToUnpublish->getIdNews();

        $adminUrlGenerator = $this->getContainer()->get(AdminUrlGenerator::class);
        $unpublishUrl = $adminUrlGenerator
            ->setController(static::getControllerFqcn())
            ->setAction('unpublish')
            ->setEntityId($newsId)
            ->generateUrl();

        $this->client->request('GET', $unpublishUrl);

        static::assertResponseRedirects($this->generateIndexUrl(), null, "Devrait rediriger vers l'index après l'action unpublish.");
        $this->client->followRedirect();
        static::assertSelectorTextContains('.alert-success', 'Actualité dépublié avec succès et la notification est annulée');

        $this->entityManager->clear();
        $unpublishedNews = $this->entityManager->getRepository(News::class)->find($newsId);
        $this->assertNotNull($unpublishedNews, "L'actualité ID {$newsId} devrait exister après dépublication.");
        $this->assertFalse($unpublishedNews->isPublishNews(), "L'actualité devrait être dépubliée.");
        $this->assertFalse($unpublishedNews->isPush(), "Le push devrait être désactivé si l'actualité est dépubliée.");
        $this->assertNull($unpublishedNews->getNotificationDate(), "La date de notification devrait être nulle si le push est désactivé.");
    }

    public function testSendNotificationActionEnablesPush(): void
    {
        $referenceRepository = $this->loadFixturesAndLoginAdmin([TestNewsFixtures::class]);
        // Utiliser une news publiée mais pas encore notifiée
        $news = $referenceRepository->getReference(TestNewsFixtures::NEWS_PUBLISHED_2_REF);
        $this->assertNotNull($news, "Fixture NEWS_PUBLISHED_OLDER_REF non trouvée.");
        $this->assertTrue($news->isPublishNews(), "L'actualité doit être publiée initialement.");
        $this->assertFalse($news->isPush(), "L'actualité ne doit pas avoir le push activé initialement.");
        $newsId = $news->getIdNews();

        $adminUrlGenerator = $this->getContainer()->get(AdminUrlGenerator::class);
        $sendNotificationUrl = $adminUrlGenerator
            ->setController(static::getControllerFqcn())
            ->setAction('sendNotification')
            ->setEntityId($newsId)
            ->generateUrl();

        $this->client->request('GET', $sendNotificationUrl);

        static::assertResponseRedirects($this->generateIndexUrl());
        $this->client->followRedirect();
        static::assertSelectorTextContains('.alert-success', 'Notification envoyée');

        $this->entityManager->clear();

        $updatedNews = $this->entityManager->getRepository(News::class)->find($newsId);
        $this->assertNotNull($updatedNews);
        $this->assertTrue($updatedNews->isPush(), "Le push devrait être activé.");
        $this->assertNotNull($updatedNews->getNotificationDate(), "La date de notification devrait être définie.");
    }

    public function testUnsendNotificationActionDisablesPush(): void
    {
        $referenceRepository = $this->loadFixturesAndLoginAdmin([TestNewsFixtures::class]);

        $news = $referenceRepository->getReference(TestNewsFixtures::NEWS_PUSH_ACTIVE_REF);
        $this->assertNotNull($news, "Fixture NEWS_PUSH_ACTIVE_REF non trouvée.");
        $this->assertTrue($news->isPush(), "L'actualité doit avoir le push activé initialement.");
        $newsId = $news->getIdNews();

        $adminUrlGenerator = $this->getContainer()->get(AdminUrlGenerator::class);
        $unsendNotificationUrl = $adminUrlGenerator
            ->setController(static::getControllerFqcn())
            ->setAction('unsendNotification')
            ->setEntityId($newsId)
            ->generateUrl();

        $this->client->request('GET', $unsendNotificationUrl);

        static::assertResponseRedirects($this->generateIndexUrl());
        $this->client->followRedirect();
        static::assertSelectorTextContains('.alert-success', 'Notification annulée');

        $this->entityManager->clear();

        $updatedNews = $this->entityManager->getRepository(News::class)->find($newsId);
        $this->assertNotNull($updatedNews);
        $this->assertFalse($updatedNews->isPush(), "Le push devrait être désactivé.");
        $this->assertNull($updatedNews->getNotificationDate(), "La date de notification devrait être nulle.");
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // Clear the entity manager and explicitly set adminUser to null to avoid any stale state across tests.
        if ($this->entityManager->isOpen()) {
            $this->entityManager->close();
        }
        $this->adminUser = null;
    }
}