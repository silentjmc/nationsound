<?php

namespace App\Tests\Controller\Admin;

use App\Controller\Admin\DashboardController;
use App\Controller\Admin\EventTypeCrudController;
use App\Entity\EventType;
use App\Entity\User;
use App\Tests\Fixtures\TestEventTypeFixtures;
use App\Tests\Fixtures\TestRoleFixtures;
use App\Tests\Fixtures\TestUserFixtures;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Test\AbstractCrudTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;

class EventTypeCrudControllerTest extends AbstractCrudTestCase
{
protected AbstractDatabaseTool $databaseTool;
    protected EntityManagerInterface $entityManager;
    private ?User $adminUser = null;

    protected function getControllerFqcn(): string
    {
        return EventTypeCrudController::class;
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
        $this->loadFixturesAndLoginAdmin([TestEventTypeFixtures::class]);
        
        $indexUrl = $this->generateIndexUrl();
        $this->client->request('GET', $indexUrl);
        
        static::assertResponseIsSuccessful("La page d'index des types d'Événement devrait se charger.");
        static::assertPageTitleContains("Type d'évènements");
    }

    public function testNewPageLoadsSuccessfully(): void
    {
        $this->loadFixturesAndLoginAdmin([]); 

        $this->client->request('GET', $this->generateNewFormUrl());
        static::assertResponseIsSuccessful("La page de création de Type d'Évènement devrait se charger.");
        static::assertPageTitleContains("Ajouter un nouveau type d'évènement");

        $crawler = $this->client->getCrawler();
        $this->assertCount(1, $crawler->filter('input[name="EventType[nameType]"]'), "Le champ input unique pour 'nameType' (nom='EventType[nameType]') devrait exister.");
    }

    public function testCreateNewEventDateSuccessfully(): void
    {
        $this->loadFixturesAndLoginAdmin([]); // User loggué, base purgée par la méthode helper
        
        $testNameType = "Signature";

        $this->client->request('GET', $this->generateNewFormUrl());
        static::assertResponseIsSuccessful("La page du formulaire de création devrait se charger avant soumission.");

        $this->client->submitForm("Créer le type d'évènement", [
            'EventType[nameType]' => $testNameType, 
        ]);

        $expectedIndexUrl = $this->generateIndexUrl();
        static::assertResponseRedirects($expectedIndexUrl, null, "Devrait rediriger vers l'index après création de EventDate.");
        
        $this->entityManager->clear(); 
        /** @var EventType|null $newEventType */
        $newEventType = $this->entityManager->getRepository(EventType::class)->findOneBy(['nameType' => $testNameType]);
        $this->assertNotNull($newEventType, "La nouvelle EventType pour '{$testNameType}' devrait exister en base de données.");
    }

    public function testEditPageLoadsSuccessfully(): void
    {
        $referenceRepository = $this->loadFixturesAndLoginAdmin([TestEventTypeFixtures::class]);
        /** @var EventType $eventTypeToEdit */
        $eventTypeToEdit = $referenceRepository->getReference(TestEventTypeFixtures::TYPE_CONCERT_REF);
        $this->assertNotNull($eventTypeToEdit, "La fixture EventType avec la référence '" . TestEventTypeFixtures::TYPE_CONCERT_REF . "' n'a pas été trouvée.");

        $this->client->request('GET', $this->generateEditFormUrl($eventTypeToEdit->getIdEventType()));
        static::assertResponseIsSuccessful("La page d'édition de EventType devrait se charger avec succès.");
        static::assertPageTitleContains("Modifier Type d'évènement"); 

        $crawler = $this->client->getCrawler();
        $this->assertStringContainsString($eventTypeToEdit->getNameType(), $crawler->filter('input[name="EventType[nameType]"]')->attr('value'));
    }   

    public function testUpdateExistingEventTypeSuccessfully(): void
    {
        $referenceRepository = $this->loadFixturesAndLoginAdmin([TestEventTypeFixtures::class]);

        $eventTypeToUpdate = $referenceRepository->getReference(TestEventTypeFixtures::TYPE_CONCERT_REF); 
        $this->assertNotNull($eventTypeToUpdate);
        $eventTypeId = $eventTypeToUpdate->getIdEventType();

        $updatedNameEventType = "NEW_TYPE_CONCERT";

        $this->client->request('GET', $this->generateEditFormUrl($eventTypeId));
        static::assertResponseIsSuccessful();

        $this->client->submitForm('Sauvegarder les modifications', [
            'EventType[nameType]' => $updatedNameEventType,
        ]);

        $expectedIndexUrl = $this->generateIndexUrl();
        static::assertResponseRedirects($expectedIndexUrl, null, "Devrait rediriger vers l'index après mise à jour de EventDate.");

        $this->entityManager->clear();

        $updatedEventType = $this->entityManager->getRepository(EventType::class)->find($eventTypeId);
        $this->assertNotNull($updatedEventType, "L'EventType mise à jour (ID: {$eventTypeId}) devrait exister en base de données.");
    }
}

