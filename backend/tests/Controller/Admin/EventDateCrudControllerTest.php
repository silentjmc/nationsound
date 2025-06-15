<?php

namespace App\Tests\Controller\Admin;

use App\Controller\Admin\DashboardController;
use App\Controller\Admin\EventDateCrudController;
use App\Entity\EventDate;
use App\Entity\User;
use App\Tests\Fixtures\TestEventDateFixtures;
use App\Tests\Fixtures\TestRoleFixtures;
use App\Tests\Fixtures\TestUserFixtures;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Test\AbstractCrudTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;

class EventDateCrudControllerTest extends AbstractCrudTestCase
{
    protected AbstractDatabaseTool $databaseTool;
    protected EntityManagerInterface $entityManager;
    private ?User $adminUser = null;

    protected function getControllerFqcn(): string
    {
        return EventDateCrudController::class;
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
        $this->loadFixturesAndLoginAdmin([TestEventDateFixtures::class]);
        
        $indexUrl = $this->generateIndexUrl();
        $this->client->request('GET', $indexUrl);
        
        static::assertResponseIsSuccessful("La page d'index des Dates d'Événement devrait se charger.");
        static::assertPageTitleContains('Dates');
    }

    public function testNewPageLoadsSuccessfully(): void
    {
        $this->loadFixturesAndLoginAdmin([]); 

        $this->client->request('GET', $this->generateNewFormUrl());
        static::assertResponseIsSuccessful("La page de création de Date d'Événement devrait se charger.");
        static::assertPageTitleContains('Ajouter une nouvelle date');

        $crawler = $this->client->getCrawler();
        $this->assertGreaterThan(0, $crawler->filter('label:contains("Date")')->count(), "Le label du champ 'date' devrait exister.");
        $this->assertCount(1, $crawler->filter('input[name="EventDate[date]"]'), "Le champ input unique pour 'date' (nom='EventDate[date]') devrait exister.");
    }

    public function testCreateNewEventDateSuccessfully(): void
    {
        $this->loadFixturesAndLoginAdmin([]); // User loggué, base purgée par la méthode helper
        
        $testDateObject = new \DateTime('2025-12-25');
        $testDateStringYmd = $testDateObject->format('Y-m-d'); // Format attendu pour input[type=date] et comparaison BDD

        $this->client->request('GET', $this->generateNewFormUrl());
        static::assertResponseIsSuccessful("La page du formulaire de création devrait se charger avant soumission.");

        $this->client->submitForm('Créer la date', [
            'EventDate[date]' => $testDateStringYmd, 
        ]);

        $expectedIndexUrl = $this->generateIndexUrl();
        static::assertResponseRedirects($expectedIndexUrl, null, "Devrait rediriger vers l'index après création de EventDate.");
        
        $this->entityManager->clear(); 
        $newEventDate = $this->entityManager->getRepository(EventDate::class)->findOneBy(['date' => $testDateObject]);
        $this->assertNotNull($newEventDate, "La nouvelle EventDate pour le {$testDateStringYmd} devrait exister en base de données.");

        if ($newEventDate) {
            $this->assertNotNull($newEventDate->getDate(), "La date de l'entité EventDate créée ne doit pas être nulle.");
            $this->assertEquals(
                $testDateStringYmd,
                $newEventDate->getDate()->format('Y-m-d'),
                "La date de l'EventDate créée ne correspond pas."
            );

            $this->assertNotNull($newEventDate->getDateModificationEventDate(), "dateModificationEventDate devrait être définie par EntityListener.");
        }
    }

    public function testEditPageLoadsSuccessfully(): void
    {
        $referenceRepository = $this->loadFixturesAndLoginAdmin([TestEventDateFixtures::class]);
        $eventDateToEdit = $referenceRepository->getReference(TestEventDateFixtures::DATE_J1_FESTIVAL_REF);
        $this->assertNotNull($eventDateToEdit, "La fixture EventDate avec la référence '" . TestEventDateFixtures::DATE_J1_FESTIVAL_REF . "' n'a pas été trouvée.");

        $this->client->request('GET', $this->generateEditFormUrl($eventDateToEdit->getIdEventDate()));
        static::assertResponseIsSuccessful("La page d'édition de EventDate devrait se charger avec succès.");
        static::assertPageTitleContains('Modifier Date'); 

        $crawler = $this->client->getCrawler();
        $this->assertNotNull($eventDateToEdit->getDate(), "L'objet date de l'entité à éditer ne doit pas être nul."); 
        $this->assertEquals(
            $eventDateToEdit->getDate()->format('Y-m-d'),
            $crawler->filter('input[name="EventDate[date]"]')->attr('value'),
            "Le champ 'date' du formulaire d'édition n'est pas pré-rempli correctement."
        );
    }    

    public function testUpdateExistingEventDateSuccessfully(): void
    {
        $referenceRepository = $this->loadFixturesAndLoginAdmin([TestEventDateFixtures::class]);
        $eventDateToUpdate = $referenceRepository->getReference(TestEventDateFixtures::DATE_J1_FESTIVAL_REF);
        $this->assertNotNull($eventDateToUpdate);
        $eventDateId = $eventDateToUpdate->getIdEventDate();

        $updatedDateObject = new \DateTime('2028-03-10');
        $updatedDateStringYmd = $updatedDateObject->format('Y-m-d'); 

        $this->client->request('GET', $this->generateEditFormUrl($eventDateId));
        static::assertResponseIsSuccessful();

        $this->client->submitForm('Sauvegarder les modifications', [
            'EventDate[date]' => $updatedDateStringYmd,
        ]);

        $expectedIndexUrl = $this->generateIndexUrl();
        static::assertResponseRedirects($expectedIndexUrl, null, "Devrait rediriger vers l'index après mise à jour de EventDate.");

        $this->entityManager->clear();
        $updatedEventDate = $this->entityManager->getRepository(EventDate::class)->find($eventDateId);
        $this->assertNotNull($updatedEventDate, "L'EventDate mise à jour (ID: {$eventDateId}) devrait exister en base de données.");
    }
}