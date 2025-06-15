<?php

namespace App\Tests\Controller\Admin;

use App\Controller\Admin\DashboardController;
use App\Controller\Admin\InformationCrudController;
use App\Entity\Information;
use App\Entity\User;
use App\Tests\Fixtures\TestInformationFixtures;
use App\Tests\Fixtures\TestInformationSectionFixtures;
use App\Tests\Fixtures\TestRoleFixtures;
use App\Tests\Fixtures\TestUserFixtures;
use App\Tests\Trait\MoveActionTestTrait;
use App\Tests\Trait\PublishActionTestTrait;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Test\AbstractCrudTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;

class InformationCrudControllerTest extends AbstractCrudTestCase
{
    protected AbstractDatabaseTool $databaseTool;
    protected EntityManagerInterface $entityManager;
    private ?User $adminUser = null; // Store the admin user for convenience
    use MoveActionTestTrait;
    use PublishActionTestTrait;

    protected function getControllerFqcn(): string
    {
        return InformationCrudController::class;
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
            TestInformationFixtures::class,
            TestInformationSectionFixtures::class
        ]);

        $this->client->request('GET', $this->generateIndexUrl());
        
        static::assertResponseIsSuccessful("The index page should load successfully after login.");
        static::assertPageTitleContains('Informations', "Page title should contain 'Informations'.");
    }

    public function testNewPageLoadsSuccessfully(): void
    {
        $this->loadFixturesAndLoginAdmin([TestInformationSectionFixtures::class]);
        $this->client->request('GET', $this->generateNewFormUrl());
        static::assertResponseIsSuccessful("La page de création d\'informations devrait se charger.");
        static::assertPageTitleContains('Ajouter une nouvelle information'); 

        $crawler = $this->client->getCrawler();
        $this->assertCount(1, $crawler->filter('select[name="Information[sectionInformation]"]'), "Le champ 'sectionInformation' devrait exister.");
        $this->assertCount(1, $crawler->filter('input[name="Information[titleInformation]"]'), "Le champ 'titleInformation' devrait exister.");
        $this->assertCount(1, $crawler->filter('textarea[name="Information[contentInformation]"]'), "Le champ 'contentInformation' devrait exister.");
        $this->assertCount(1, $crawler->filter('input[name="Information[publishInformation]"]'), "Le champ 'publishInformation' devrait exister.");
    }
    
    public function testCreateNewInformationSuccessfully(): void
    {
        $referenceRepository = $this->loadFixturesAndLoginAdmin([TestInformationSectionFixtures::class]);

        $informationSection = $referenceRepository->getReference(TestInformationSectionFixtures::SECTION_A_REF);
        $this->assertNotNull($informationSection, "La section d'information de test n'a pas été trouvé.");

        $testTitleInformation = "Nouvelle Information";
        $testContentInformation = "Contenu mis à jour pour la nouvelle information.";

        $this->client->request('GET', $this->generateNewFormUrl());
        static::assertResponseIsSuccessful(); 

        $this->client->submitForm('Créer l\'information', [ 
            'Information[titleInformation]' => $testTitleInformation,
            'Information[contentInformation]' => $testContentInformation,
            'Information[sectionInformation]' => $informationSection->getIdInformationSection(),
            'Information[publishInformation]' => true,
            // positionInformation is usually managed by Gedmo and not submitted in the form
        ]);

        //Verify that the response redirects to the index page
        $expectedIndexUrl = $this->generateIndexUrl();
        static::assertResponseRedirects($expectedIndexUrl, null, "Devrait rediriger vers la page d'index après création.");

        // Verfiy that the entity exists in the database
        $this->entityManager->clear(); 
        /** @var Information|null $newInformation */
        $newInformation = $this->entityManager->getRepository(Information::class)->findOneBy(['titleInformation' => $testTitleInformation]);
        $this->assertNotNull($newInformation, "La nouvelle Information devrait exister en base de données.");
        if ($newInformation) {
            $this->assertEquals($testContentInformation, $newInformation->getContentInformation(), "La réponse de l\'Information créée ne correspond pas.");
            $this->assertEquals($informationSection->getIdInformationSection(), $newInformation->getSectionInformation()->getIdInformationSection());
            $this->assertTrue($newInformation->isPublishInformation(), "L\'Information créée devrait être publiée.");
            $this->assertNotNull($newInformation->getPositionInformation(), "L\'Information créée devrait avoir une position.");
        }
    }

    public function testEditPageLoadsSuccessfully(): void
    {
        $referenceRepository =  $this->loadFixturesAndLoginAdmin([
            TestInformationFixtures::class,
            TestInformationSectionFixtures::class
        ]);
        /** @var Information $informationToEdit */
        $informationToEdit = $referenceRepository->getReference(TestInformationFixtures::INFO_HORAIRES_REF);
        $this->assertNotNull($informationToEdit);

        $this->client->request('GET', $this->generateEditFormUrl($informationToEdit->getIdInformation()));
        static::assertResponseIsSuccessful("La page d'édition devrait se charger.");
        static::assertPageTitleContains('Modifier Information');

        $crawler = $this->client->getCrawler();
        $this->assertStringContainsString($informationToEdit->getTitleInformation(), $crawler->filter('input[name="Information[titleInformation]"]')->attr('value'));
        $this->assertStringContainsString(
            (string) $informationToEdit->getContentInformation(), // Expected gross HTML  
            html_entity_decode($crawler->filter('textarea#Information_contentInformation')->text()), // HTML decoded from textarea
            "Le contenu du champ 'contentInformation' (Trix editor source) n'est pas pré-rempli correctement."
        );

        // Vérifier que la bonne section est sélectionné
        $this->assertEquals((string)$informationToEdit->getSectionInformation()->getIdInformationSection(), $crawler->filter('select[name="Information[sectionInformation]"] option[selected="selected"]')->attr('value'));
    }

    public function testUpdateExistingInformationSuccessfully(): void
        {
        $referenceRepository =  $this->loadFixturesAndLoginAdmin([
            TestInformationFixtures::class,
            TestInformationSectionFixtures::class
        ]);
         /** @var Information $informationToUpdate */
        $informationToUpdate = $referenceRepository->getReference(TestInformationFixtures::INFO_PARKING_REF);
        $this->assertNotNull($informationToUpdate);
        $informationId = $informationToUpdate->getIdInformation();

        $updatedTitleInformation = "Nouvelle Information";
        $updatedContentInformation = "Contenu mis à jour pour la nouvelle information.";

        $this->client->request('GET', $this->generateEditFormUrl($informationId));
        static::assertResponseIsSuccessful();

        $this->client->submitForm('Sauvegarder les modifications', [ 
            'Information[titleInformation]' => $updatedTitleInformation,
            'Information[contentInformation]' => $updatedContentInformation,
            'Information[publishInformation]' => false, 
        ]);

        $expectedIndexUrl = $this->generateIndexUrl();
        static::assertResponseRedirects($expectedIndexUrl, null, "Devrait rediriger vers la page d'index après mise à jour.");

        $this->entityManager->clear();
        /** @var Information|null $updatedInformation */
        $updatedInformation = $this->entityManager->getRepository(Information::class)->find($informationId);
        $this->assertNotNull($updatedInformation);
        $this->assertEquals($updatedTitleInformation, $updatedInformation->getTitleInformation());

    } 

    public function testPublishAction(): void
    {
        $this->publishAction(
            entity: 'Information',
            fixtureClass: TestInformationFixtures::class, 
            fixtureReference: TestInformationFixtures::INFO_TRANSPORTS_UNPUBLISHED_REF, 
            action : 'publish');
    }

    public function testUnpublishAction(): void
    {
        $this->publishAction(
            entity: 'Information',
            fixtureClass: TestInformationFixtures::class, 
            fixtureReference: TestInformationFixtures::INFO_HORAIRES_REF, 
            action : 'unpublish');
    }

    public function testInformationMoveTop(): void
    {
        $this->moveAction(
            entity: 'Information',
            fixtureClass: TestInformationFixtures::class,
            fixtureReference: TestInformationFixtures::INFO_TARIFS_REF,
            direction: 'moveTop',
            initialExpectedPosition: 1,
            finalExpectedPosition: 0,
        );
    }

    public function testInformationMoveBottom(): void
    {
        $this->moveAction(
            entity: 'Information',
            fixtureClass: TestInformationFixtures::class,
            fixtureReference: TestInformationFixtures::INFO_TARIFS_REF,
            direction: 'moveBottom',
            initialExpectedPosition: 1,
            finalExpectedPosition: -1,
        );
    }

    public function testInformationMoveUp(): void
    {
        $this->moveAction(
            entity: 'Information',
            fixtureClass: TestInformationFixtures::class,
            fixtureReference: TestInformationFixtures::INFO_TARIFS_REF,
            direction: 'moveUp',
            initialExpectedPosition: 1,
            finalExpectedPosition: 0,
        );
    }

    public function testInformationMoveDown(): void
    {
        $this->moveAction(
            entity: 'Information',
            fixtureClass: TestInformationFixtures::class,
            fixtureReference: TestInformationFixtures::INFO_TARIFS_REF,
            direction: 'moveDown',
            initialExpectedPosition: 1,
            finalExpectedPosition: 2,
        );
    }

    public function testMovementActionsVisibilityOnIndexPage(): void
    {
        $referenceRepository = $this->loadFixturesAndLoginAdmin([TestInformationFixtures::class]);

        $informationTop = $referenceRepository->getReference(TestInformationFixtures::INFO_HORAIRES_REF); // Position 0
        $informationMiddle = $referenceRepository->getReference(TestInformationFixtures::INFO_TARIFS_REF); // Position 1

        $crawler = $this->client->request('GET', $this->generateIndexUrl());
        $this->assertResponseIsSuccessful("La page d'index devrait se charger.");

        $rowTopSelector = sprintf('tr[data-id="%s"]', $informationTop->getIdInformation());
        $this->assertSelectorNotExists($rowTopSelector . ' a[data-action-name="moveUp"]', 'Bouton MoveUp ne doit pas être visible pour l\'élément du haut.');
        $this->assertSelectorNotExists($rowTopSelector . ' a[data-action-name="moveTop"]', 'Bouton MoveTop ne doit pas être visible pour l\'élément du haut.');
        $this->assertSelectorExists($rowTopSelector . ' a[data-action-name="moveDown"]', 'Bouton MoveDown doit être visible pour l\'élément du haut.');
        $this->assertSelectorExists($rowTopSelector . ' a[data-action-name="moveBottom"]', 'Bouton MoveBottom doit être visible pour l\'élément du haut.');

        $rowMiddleSelector = sprintf('tr[data-id="%s"]', $informationMiddle->getIdInformation());
        $this->assertSelectorExists($rowMiddleSelector . ' a[data-action-name="moveUp"]', 'Bouton MoveUp doit être visible pour l\'élément du milieu.');
        $this->assertSelectorExists($rowMiddleSelector . ' a[data-action-name="moveTop"]', 'Bouton MoveTop doit être visible pour l\'élément du milieu.');
        $this->assertSelectorExists($rowMiddleSelector . ' a[data-action-name="moveDown"]', 'Bouton MoveDown doit être visible pour l\'élément du milieu.');
        $this->assertSelectorExists($rowMiddleSelector . ' a[data-action-name="moveBottom"]', 'Bouton MoveBottom doit être visible pour l\'élément du milieu.');
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
