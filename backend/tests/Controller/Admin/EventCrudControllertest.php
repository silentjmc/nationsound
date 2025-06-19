<?php

namespace App\Tests\Controller\Admin;

use App\Controller\Admin\DashboardController;
use App\Controller\Admin\EventCrudController;
use App\Entity\Event;
use App\Entity\User;
use App\Tests\Fixtures\TestArtistFixtures;
use App\Tests\Fixtures\TestEventDateFixtures;
use App\Tests\Fixtures\TestEventFixtures;
use App\Tests\Fixtures\TestEventLocationFixtures;
use App\Tests\Fixtures\TestEventTypeFixtures;
use App\Tests\Fixtures\TestRoleFixtures;
use App\Tests\Fixtures\TestUserFixtures;
use App\Tests\Trait\PublishActionTestTrait;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Test\AbstractCrudTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;

class EventCrudControllerTest extends AbstractCrudTestCase
{
    protected AbstractDatabaseTool $databaseTool;
    protected EntityManagerInterface $entityManager;
    private ?User $adminUser = null;
    use PublishActionTestTrait;

    protected function getControllerFqcn(): string
    {
        return EventCrudController::class;
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
        $allFixturesToLoad = array_unique(array_merge([
            TestRoleFixtures::class,
            TestUserFixtures::class,
        ], $fixtureClasses));
        $executor = $this->databaseTool->loadFixtures($allFixturesToLoad);
        $this->entityManager->clear();
        $userRepo = $this->entityManager->getRepository(User::class);
        $this->adminUser = $userRepo->findOneBy(['email' => 'admin@email.com']);
        $this->assertNotNull($this->adminUser);
        $this->client->loginUser($this->adminUser);
        return $executor->getReferenceRepository();
    }

    public function testIndexPageLoads(): void
    {
        $this->loadFixturesAndLoginAdmin([
            TestEventTypeFixtures::class,
            TestEventFixtures::class,
            TestArtistFixtures::class,
            TestEventLocationFixtures::class,
            TestEventDateFixtures::class,
        ]);
        $this->client->request('GET', $this->generateIndexUrl());
        static::assertResponseIsSuccessful("La page d'index des Évènements devrait se charger.");
        static::assertPageTitleContains('Évènements'); 
    }

    public function testNewPageLoadsSuccessfully(): void
    {
        $this->loadFixturesAndLoginAdmin([
            TestEventTypeFixtures::class,
            TestEventFixtures::class,
            TestArtistFixtures::class,
            TestEventLocationFixtures::class,
            TestEventDateFixtures::class,
        ]);

        $this->client->request('GET', $this->generateNewFormUrl());
        static::assertResponseIsSuccessful("La page de création de Évènements devrait se charger.");
        static::assertPageTitleContains('Ajouter un nouvel évènement');

        $crawler = $this->client->getCrawler();
        $this->assertCount(1, $crawler->filter('select[name="Event[type]"]'), "Le champ select 'type' devrait exister.");
        $this->assertCount(1, $crawler->filter('select[name="Event[artist]"]'), "Le champ select 'artist' devrait exister.");
        $this->assertCount(1, $crawler->filter('select[name="Event[date]"]'), "Le champ select 'date' devrait exister.");
        $this->assertCount(1, $crawler->filter('select[name="Event[eventLocation]"]'), "Le champ select 'eventLocation' devrait exister.");
        $this->assertCount(1, $crawler->filter('input[name="Event[publishEvent]"]'), "Le champ 'publishEvent' devrait exister.");
        $this->assertCount(1, $crawler->filter('input[name="Event[heureDebut]"]'), "Le champ 'heureDebut' devrait exister.");
        $this->assertCount(1, $crawler->filter('input[name="Event[heureFin]"]'), "Le champ 'heureFin' devrait exister.");
    }

    public function testCreateNewEventSuccessfully(): void
    {
        $referenceRepository = $this->loadFixturesAndLoginAdmin([
            TestEventTypeFixtures::class,
            TestEventFixtures::class,
            TestArtistFixtures::class,
            TestEventLocationFixtures::class,
            TestEventDateFixtures::class,
        ]);
  
        $eventType = $referenceRepository->getReference(TestEventTypeFixtures::TYPE_CONCERT_REF);
        $artist = $referenceRepository->getReference(TestArtistFixtures::ARTIST_ALPHA_EVENTS_REF);
        $eventLocation = $referenceRepository->getReference(TestEventLocationFixtures::LOC_SCENE_PRINCIPALE_REF);
        $eventDate = $referenceRepository->getReference(TestEventDateFixtures::DATE_J1_FESTIVAL_REF);    
        $this->assertNotNull($eventType, "Le type d'évènement de test n'a pas été trouvé.");

        $testHeureDebut = new \DateTime('10:00');
        $testHeureFin = new \DateTime('12:00');

        // Format hours in strings
        $testHeureDebutString = $testHeureDebut->format('H:i'); // Format 'HH:MM'
        $testHeureFinString = $testHeureFin->format('H:i');     // Format 'HH:MM'

        $this->client->request('GET', $this->generateNewFormUrl());
        static::assertResponseIsSuccessful();

        $this->client->submitForm("Créer l'évènement", [
            'Event[heureDebut]' => $testHeureDebutString,
            'Event[heureFin]' => $testHeureFinString,
            'Event[type]' => $eventType->getIdEventType(), // Soumettre l'ID du type
            'Event[artist]' => $artist->getIdArtist(), // Soumettre l'ID du type
            'Event[date]' => $eventDate->getIdEventDate(), // Soumettre l'ID du type
            'Event[eventLocation]' => $eventLocation->getIdEventLocation(), // Soumettre l'ID du type
            'Event[publishEvent]' => true,
        ]);

        $expectedIndexUrl = $this->generateIndexUrl();
        static::assertResponseRedirects($expectedIndexUrl, null, "Devrait rediriger vers l'index après création.");

        $this->entityManager->clear();
        $newEvent = $this->entityManager->getRepository(Event::class)->findOneBy(['heureDebut' => $testHeureDebut, 'heureFin' => $testHeureFin]);
        $this->assertNotNull($newEvent, "Le nouvel Évènement devrait exister.");
        if ($newEvent) {
            $this->assertTrue($newEvent->isPublishEvent());
            $this->assertEquals($eventType->getIdEventType(), $newEvent->getType()->getIdEventType());
            $this->assertEquals($artist->getIdArtist(), $newEvent->getArtist()->getIdArtist());
            $this->assertEquals($eventDate->getIdEventDate(), $newEvent->getDate()->getIdEventDate());
            $this->assertEquals($eventLocation->getIdEventLocation(), $newEvent->getEventLocation()->getIdEventLocation());
        }
    }

    public function testEditPageLoadsSuccessfully(): void
    {
        $referenceRepository = $this->loadFixturesAndLoginAdmin([
            TestEventTypeFixtures::class,
            TestEventFixtures::class,
            TestArtistFixtures::class,
            TestEventLocationFixtures::class,
            TestEventDateFixtures::class,
        ]);

        $eventToEdit = $referenceRepository->getReference(TestEventFixtures::EVENT_PUBLISHED_ARTIST1_J1_SCENE_REF);
        $this->assertNotNull($eventToEdit);

        $this->client->request('GET', $this->generateEditFormUrl($eventToEdit->getIdEvent()));
        static::assertResponseIsSuccessful("La page d'édition devrait se charger.");
        static::assertPageTitleContains('Modifier Évènement');

        $crawler = $this->client->getCrawler();

        $this->assertStringContainsString($eventToEdit->getheureDebut()->format('H:i'), $crawler->filter('input[name="Event[heureDebut]"]')->attr('value'));
        $this->assertStringContainsString($eventToEdit->getheureFin()->format('H:i'), $crawler->filter('input[name="Event[heureFin]"]')->attr('value'));
        $this->assertEquals((string)$eventToEdit->getArtist()->getIdArtist(), $crawler->filter('select[name="Event[artist]"] option[selected="selected"]')->attr('value'));
    }

    public function testUpdateExistingEventSuccessfully(): void
    {
        $referenceRepository = $this->loadFixturesAndLoginAdmin([
            TestEventTypeFixtures::class,
            TestEventFixtures::class,
            TestArtistFixtures::class,
            TestEventLocationFixtures::class,
            TestEventDateFixtures::class,
        ]);

        $eventToUpdate = $referenceRepository->getReference(TestEventFixtures::EVENT_PUBLISHED_ARTIST1_J1_SCENE_REF);
        $this->assertNotNull($eventToUpdate);
        $eventId = $eventToUpdate->getIdEvent();

        $updateHeureDebut = new \DateTime('10:00');
        $updateHeureFin = new \DateTime('12:00');

        // Format hours in strings
        $updateHeureDebutString = $updateHeureDebut->format('H:i'); // Format 'HH:MM'
        $updateHeureFinString = $updateHeureFin->format('H:i');     // Format 'HH:MM'

        $this->client->request('GET', $this->generateEditFormUrl($eventId));
        static::assertResponseIsSuccessful();

        $this->client->submitForm('Sauvegarder les modifications', [ 
            'Event[heureDebut]' => $updateHeureDebutString,
            'Event[heureFin]' => $updateHeureFinString,
        ]);

        $expectedIndexUrl = $this->generateIndexUrl();
        static::assertResponseRedirects($expectedIndexUrl, null, "Devrait rediriger vers la page d'index après mise à jour.");

        $this->entityManager->clear();

        $updatedEvent = $this->entityManager->getRepository(Event::class)->find($eventId);
        $this->assertNotNull($updatedEvent);
        $this->assertEquals($updateHeureDebutString, $updatedEvent->getHeureDebut()->format('H:i'));     
        $this->assertEquals($updateHeureFinString, $updatedEvent->getHeureFin()->format('H:i'));    
    }

    public function testPublishAction(): void
    {
        $this->publishAction(
            entity: 'Event',
            fixtureClass: TestEventFixtures::class, 
            fixtureReference: TestEventFixtures::EVENT_UNPUBLISHED_ARTIST1_J1_SCENE_REF, 
            action : 'publish');
    }

    public function testUnpublishAction(): void
    {
        $this->publishAction(
            entity: 'Event',
            fixtureClass: TestEventFixtures::class, 
            fixtureReference: TestEventFixtures::EVENT_PUBLISHED_ARTIST1_J1_SCENE_REF, 
            action : 'unpublish');
    }

}