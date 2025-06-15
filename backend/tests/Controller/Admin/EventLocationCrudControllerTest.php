<?php

namespace App\Tests\Controller\Admin;

use App\Controller\Admin\DashboardController;
use App\Controller\Admin\EventLocationCrudController;
use App\Entity\EventLocation;
use App\Entity\User;
use App\Tests\Fixtures\TestEventLocationFixtures;
use App\Tests\Fixtures\TestLocationTypeFixtures;
use App\Tests\Fixtures\TestRoleFixtures;
use App\Tests\Fixtures\TestUserFixtures;
use App\Tests\Trait\PublishActionTestTrait;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Test\AbstractCrudTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;

class EventLocationCrudControllerTest extends AbstractCrudTestCase
{
    protected AbstractDatabaseTool $databaseTool;
    protected EntityManagerInterface $entityManager;
    private ?User $adminUser = null; // Store the admin user for convenience
    use PublishActionTestTrait;

    protected function getControllerFqcn(): string
    {
        return EventLocationCrudController::class;
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



    private function loadFixturesAndLoginAdmin(array $fixtureClasses): \Doctrine\Common\DataFixtures\ReferenceRepository
    {
        $allFixturesToLoad = array_unique(array_merge([
            TestRoleFixtures::class,
            TestUserFixtures::class,
        ], $fixtureClasses));
        $executor = $this->databaseTool->loadFixtures($allFixturesToLoad);
        $this->entityManager->clear();
        $userRepo = $this->entityManager->getRepository(User::class);
        $this->adminUser = $userRepo->findOneBy(['email' => 'admin@email.com']);

        $this->assertNotNull($this->adminUser, "Admin user 'admin@email.com' not found after fixture load. Check UserFixtures.");

        $this->client->loginUser($this->adminUser);

        return $executor->getReferenceRepository();
    }

    public function testIndexPageLoads(): void
    {
        $this->loadFixturesAndLoginAdmin([
            TestLocationTypeFixtures::class,
            TestEventLocationFixtures::class
        ]);

        $this->client->request('GET', $this->generateIndexUrl());
        
        static::assertResponseIsSuccessful("The index page should load successfully after login.");
        static::assertPageTitleContains('Lieux', "Page title should contain 'Lieux'.");
    }

    public function testNewPageLoadsSuccessfully(): void
    {
        $this->loadFixturesAndLoginAdmin([TestLocationTypeFixtures::class]);
        $this->client->request('GET', $this->generateNewFormUrl());
        static::assertResponseIsSuccessful("La page de création de lieux devrait se charger.");
        static::assertPageTitleContains('Ajouter un nouveau lieu'); 

        $crawler = $this->client->getCrawler();
        $this->assertCount(1, $crawler->filter('input[name="EventLocation[nameEventLocation]"]'), "Le champ 'nameEventLocation' devrait exister.");
        $this->assertCount(1, $crawler->filter('input[name="EventLocation[latitude]"]'), "Le champ 'latitude' devrait exister.");
        $this->assertCount(1, $crawler->filter('input[name="EventLocation[longitude]"]'), "Le champ 'longitude' devrait exister.");
        $this->assertCount(1, $crawler->filter('textarea[name="EventLocation[contentEventLocation]"]'), "Le champ 'contentEventLocation' devrait exister.");
        $this->assertCount(1, $crawler->filter('select[name="EventLocation[typeLocation]"]'), "Le champ 'typeLocation' devrait exister.");
        $this->assertCount(1, $crawler->filter('input[name="EventLocation[publishEventLocation]"]'), "Le champ 'publishEventLocation' devrait exister.");
    }

    public function testCreateNewEventLocationSuccessfully(): void
    {
        $referenceRepository = $this->loadFixturesAndLoginAdmin([TestLocationTypeFixtures::class]);

        $locationType = $referenceRepository->getReference(TestLocationTypeFixtures::LOC_TYPE_SCENE_REF);
        $this->assertNotNull($locationType, "Le type de lieu de test n'a pas été trouvé.");

        $testNameEventLocation = 'Nouveau lieu Super Cool';
        $testLatitude = 48.0015;
        $testLongitude = 2.0015;
        $testContentEventLocation = 'la méga scène de test.';

        $this->client->request('GET', $this->generateNewFormUrl());
        static::assertResponseIsSuccessful();

        $this->client->submitForm('Créer le lieu', [
            'EventLocation[nameEventLocation]' => $testNameEventLocation,
            'EventLocation[latitude]' => $testLatitude,
            'EventLocation[longitude]' => $testLongitude,
            'EventLocation[contentEventLocation]' => $testContentEventLocation,
            'EventLocation[typeLocation]' => $locationType->getIdLocationType(), // Soumettre l'ID du type
            'EventLocation[publishEventLocation]' => true,
        ]);

        $expectedIndexUrl = $this->generateIndexUrl();
        static::assertResponseRedirects($expectedIndexUrl, null, "Devrait rediriger vers l'index après création.");

        $this->entityManager->clear();
       
        $newEventLocation = $this->entityManager->getRepository(EventLocation::class)->findOneBy(['nameEventLocation' => $testNameEventLocation]);
        $this->assertNotNull($newEventLocation, "Le nouveau Lieu devrait exister.");
        if ($newEventLocation) {
            $this->assertEquals($testNameEventLocation, $newEventLocation->getNameEventLocation());
            $this->assertEquals($testLatitude, $newEventLocation->getLatitude());
            $this->assertEquals($testLongitude, $newEventLocation->getLongitude());
            $this->assertEquals($testContentEventLocation, $newEventLocation->getContentEventLocation());
            $this->assertTrue($newEventLocation->isPublishEventLocation());
            $this->assertNotNull($newEventLocation->getTypeLocation());
            $this->assertEquals($locationType->getIdLocationType(), $newEventLocation->getTypeLocation()->getIdLocationType());
        }
    }

    public function testEditPageLoadsSuccessfully(): void
    {
        $referenceRepository = $this->loadFixturesAndLoginAdmin([
            TestLocationTypeFixtures::class,
            TestEventLocationFixtures::class
        ]);

        $eventLocationToEdit = $referenceRepository->getReference(TestEventLocationFixtures::LOC_BAR_UNPUBLISHED_REF);
        $this->assertNotNull($eventLocationToEdit);

        $this->client->request('GET', $this->generateEditFormUrl($eventLocationToEdit->getIdEventLocation()));
        static::assertResponseIsSuccessful("La page d'édition devrait se charger.");
        static::assertPageTitleContains('Modifier Lieu');



        

        $crawler = $this->client->getCrawler();
        $expectedLatitude = (string) $eventLocationToEdit->getLatitude();
        $actualLatitudeInForm = $crawler->filter('input[name="EventLocation[latitude]"]')->attr('value');
        $this->assertEquals($expectedLatitude, str_replace(',', '.', $actualLatitudeInForm), "La latitude...");

        $expectedLongitude = (string) $eventLocationToEdit->getLongitude();
        $actualLongitudeInForm = $crawler->filter('input[name="EventLocation[longitude]"]')->attr('value');
        $this->assertEquals($expectedLongitude, str_replace(',', '.', $actualLongitudeInForm), "La longitude...");

        $this->assertStringContainsString($eventLocationToEdit->getNameEventLocation(), $crawler->filter('input[name="EventLocation[nameEventLocation]"]')->attr('value'));
        $this->assertStringContainsString($eventLocationToEdit->getContentEventLocation(), $crawler->filter('textarea[name="EventLocation[contentEventLocation]"]')->text(), "Le champ contentEventLocation n'est pas pré-rempli correctement.");
        $this->assertEquals((string)$eventLocationToEdit->getTypeLocation()->getIdLocationType(), $crawler->filter('select[name="EventLocation[typeLocation]"] option[selected="selected"]')->attr('value'));
        if ($eventLocationToEdit->isPublishEventLocation()) {
            $this->assertNotEmpty($crawler->filter('input[name="EventLocation[PublishEventLocation]"]:checked')->count(), "La case 'PublishEventLocation' devrait être cochée.");
        } else {
             $this->assertEmpty($crawler->filter('input[name="EventLocation[PublishEventLocation]"]:checked')->count(), "La case 'PublishEventLocation' ne devrait pas être cochée.");
        }
    }

    public function testPublishAction(): void
    {
        $this->publishAction(
            entity: 'EventLocation',
            fixtureClass: TestEventLocationFixtures::class, 
            fixtureReference: TestEventLocationFixtures::LOC_BAR_UNPUBLISHED_REF, 
            action : 'publish');
    }

    public function testUnpublishAction(): void
    {
        $this->publishAction(
            entity: 'EventLocation',
            fixtureClass: TestEventLocationFixtures::class, 
            fixtureReference: TestEventLocationFixtures::LOC_SCENE_PRINCIPALE_REF, 
            action : 'unpublish');
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