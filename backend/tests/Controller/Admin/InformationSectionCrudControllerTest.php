<?php

namespace App\Tests\Controller\Admin;

use App\Controller\Admin\DashboardController;
use App\Controller\Admin\InformationSectionCrudController;
use App\Entity\InformationSection;
use App\Entity\User;
use App\Tests\Fixtures\TestInformationSectionFixtures;
use App\Tests\Fixtures\TestRoleFixtures;
use App\Tests\Fixtures\TestUserFixtures;
use App\Tests\Trait\MoveActionTestTrait;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Test\AbstractCrudTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;

class InformationSectionCrudControllerTest extends AbstractCrudTestCase
{
    protected AbstractDatabaseTool $databaseTool;
    protected EntityManagerInterface $entityManager;
    private ?User $adminUser = null;
    use MoveActionTestTrait;

    protected function getControllerFqcn(): string
    {
        return InformationSectionCrudController::class;
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
        $this->loadFixturesAndLoginAdmin([TestInformationSectionFixtures::class]);
        
        $indexUrl = $this->generateIndexUrl();
        $this->client->request('GET', $indexUrl);
        
        static::assertResponseIsSuccessful("La page d'index des Sections d'Information devrait se charger.");
        static::assertPageTitleContains('Section d\'information'); 
    }

    public function testNewPageLoadsSuccessfully(): void
    {
        $this->loadFixturesAndLoginAdmin([]); // User logged in, database purged

        $this->client->request('GET', $this->generateNewFormUrl());
        static::assertResponseIsSuccessful("La page de création de Section d'information devrait se charger.");
        static::assertPageTitleContains('Ajouter une nouvelle section');

        $crawler = $this->client->getCrawler();
        $this->assertCount(1, $crawler->filter('input[name="InformationSection[sectionLabel]"]'));
        $this->assertCount(1, $crawler->filter('input[name="InformationSection[titleInformationSection]"]'));
        $this->assertCount(1, $crawler->filter('textarea[name="InformationSection[contentInformationSection]"]'));
    }

    public function testCreateNewInformationSectionSuccessfully(): void
    {
        $this->loadFixturesAndLoginAdmin([]); // User logged in, database purged

        $testLabel = 'Nouvelle Section Test';
        $testTitle = 'Titre pour la Nouvelle Section';
        $testContent = 'Contenu de cette section.';

        $this->client->request('GET', $this->generateNewFormUrl());
        static::assertResponseIsSuccessful();

        $this->client->submitForm('Créer la section', [
            'InformationSection[sectionLabel]' => $testLabel,
            'InformationSection[titleInformationSection]' => $testTitle,
            'InformationSection[contentInformationSection]' => $testContent,
        ]);

        $expectedIndexUrl = $this->generateIndexUrl();
        static::assertResponseRedirects($expectedIndexUrl, null, "Devrait rediriger vers l'index après création.");

        $this->entityManager->clear();
        $newInformationSection = $this->entityManager->getRepository(InformationSection::class)->findOneBy(['sectionLabel' => $testLabel]);
        $this->assertNotNull($newInformationSection, "La nouvelle sectiuon devrait exister en base de données.");
        if ($newInformationSection) {
            $this->assertEquals($testLabel, $newInformationSection->getSectionLabel());
            $this->assertEquals($testTitle, $newInformationSection->getTitleInformationSection());
            $this->assertEquals($testContent, $newInformationSection->getContentInformationSection());
        }
    }

    public function testEditPageLoadsSuccessfully(): void
    {
        $referenceRepository = $this->loadFixturesAndLoginAdmin([TestInformationSectionFixtures::class]);
        $informationSectionToEdit = $referenceRepository->getReference(TestInformationSectionFixtures::SECTION_A_REF);
        $this->assertNotNull($informationSectionToEdit);

        $this->client->request('GET', $this->generateEditFormUrl($informationSectionToEdit->getIdInformationSection()));
        static::assertResponseIsSuccessful("La page d'édition devrait se charger.");
        static::assertPageTitleContains('Modifier Section d\'information'); 

        $crawler = $this->client->getCrawler();
        $this->assertStringContainsString($informationSectionToEdit->getSectionLabel(), $crawler->filter('input[name="InformationSection[sectionLabel]"]')->attr('value'));
        $this->assertStringContainsString($informationSectionToEdit->getTitleInformationSection(), $crawler->filter('input[name="InformationSection[titleInformationSection]"]')->attr('value'));
    }

    public function testUpdateExistingInformationSectionSuccessfully(): void
    {
        $referenceRepository = $this->loadFixturesAndLoginAdmin([TestInformationSectionFixtures::class]);
        $informationSectionToUpdate = $referenceRepository->getReference(TestInformationSectionFixtures::SECTION_A_REF);
        $this->assertNotNull($informationSectionToUpdate);
        $informationSectionId = $informationSectionToUpdate->getIdInformationSection();
        
        $updatedLabel = "Label Section B Mis à Jour";

        $this->client->request('GET', $this->generateEditFormUrl($informationSectionId));
        static::assertResponseIsSuccessful();

        $this->client->submitForm('Sauvegarder les modifications', [ 
            'InformationSection[sectionLabel]' => $updatedLabel,
        ]);

        $expectedIndexUrl = $this->generateIndexUrl();
        static::assertResponseRedirects($expectedIndexUrl, null, "Devrait rediriger vers l'index après mise à jour.");

        $this->entityManager->clear();
        /** @var InformationSection|null $updatedInformationSection */
        $updatedInformationSection = $this->entityManager->getRepository(InformationSection::class)->find($informationSectionId);
        $this->assertNotNull($updatedInformationSection);
        $this->assertEquals($updatedLabel, $updatedInformationSection->getSectionLabel());
    }

    public function testInformationSectionMoveTop(): void
    {
        $this->moveAction(
            entity: 'InformationSection',
            fixtureClass: TestInformationSectionFixtures::class,
            fixtureReference: TestInformationSectionFixtures::SECTION_B_REF,
            direction: 'moveTop',
            initialExpectedPosition: 1,
            finalExpectedPosition: 0,
        );
    }

    public function testInformationSectionMoveBottom(): void
    {
        $this->moveAction(
            entity: 'InformationSection',
            fixtureClass: TestInformationSectionFixtures::class,
            fixtureReference: TestInformationSectionFixtures::SECTION_B_REF,
            direction: 'moveBottom',
            initialExpectedPosition: 1,
            finalExpectedPosition: -1,
        );
    }

    public function testInformationSectionMoveUp(): void
    {
        $this->moveAction(
            entity: 'InformationSection',
            fixtureClass: TestInformationSectionFixtures::class,
            fixtureReference: TestInformationSectionFixtures::SECTION_B_REF,
            direction: 'moveUp',
            initialExpectedPosition: 1,
            finalExpectedPosition: 0,
        );
    }

    public function testInformationSectionMoveDown(): void
    {
        $this->moveAction(
            entity: 'InformationSection',
            fixtureClass: TestInformationSectionFixtures::class,
            fixtureReference: TestInformationSectionFixtures::SECTION_B_REF,
            direction: 'moveDown',
            initialExpectedPosition: 1,
            finalExpectedPosition: 2,
        );
    }

    public function testMovementActionsVisibilityOnIndexPage(): void
    {
        $referenceRepository = $this->loadFixturesAndLoginAdmin([TestInformationSectionFixtures::class]);
        $informationSectionTop = $referenceRepository->getReference(TestInformationSectionFixtures::SECTION_A_REF); // Position 0
        $informationSectionMiddle = $referenceRepository->getReference(TestInformationSectionFixtures::SECTION_B_REF); // Position 1

        $crawler = $this->client->request('GET', $this->generateIndexUrl());
        $this->assertResponseIsSuccessful("La page d'index devrait se charger.");

        $rowTopSelector = sprintf('tr[data-id="%s"]', $informationSectionTop->getIdInformationSection());
        $this->assertSelectorNotExists($rowTopSelector . ' a[data-action-name="moveUp"]', 'Bouton MoveUp ne doit pas être visible pour l\'élément du haut.');
        $this->assertSelectorNotExists($rowTopSelector . ' a[data-action-name="moveTop"]', 'Bouton MoveTop ne doit pas être visible pour l\'élément du haut.');
        $this->assertSelectorExists($rowTopSelector . ' a[data-action-name="moveDown"]', 'Bouton MoveDown doit être visible pour l\'élément du haut.');
        $this->assertSelectorExists($rowTopSelector . ' a[data-action-name="moveBottom"]', 'Bouton MoveBottom doit être visible pour l\'élément du haut.');

        $rowMiddleSelector = sprintf('tr[data-id="%s"]', $informationSectionMiddle->getIdInformationSection());
        $this->assertSelectorExists($rowMiddleSelector . ' a[data-action-name="moveUp"]', 'Bouton MoveUp doit être visible pour l\'élément du milieu.');
        $this->assertSelectorExists($rowMiddleSelector . ' a[data-action-name="moveTop"]', 'Bouton MoveTop doit être visible pour l\'élément du milieu.');
        $this->assertSelectorExists($rowMiddleSelector . ' a[data-action-name="moveDown"]', 'Bouton MoveDown doit être visible pour l\'élément du milieu.');
        $this->assertSelectorExists($rowMiddleSelector . ' a[data-action-name="moveBottom"]', 'Bouton MoveBottom doit être visible pour l\'élément du milieu.');
    }

}