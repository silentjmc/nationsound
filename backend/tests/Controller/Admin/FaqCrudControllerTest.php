<?php

namespace App\Tests\Controller\Admin;

use App\Controller\Admin\DashboardController;
use App\Controller\Admin\FaqCrudController;
use App\Entity\Faq;
use App\Entity\User;
use App\Tests\Fixtures\TestFaqFixtures; // Ensure this exists and is correct
use App\Tests\Fixtures\TestRoleFixtures;
use App\Tests\Fixtures\TestUserFixtures;
use App\Tests\Trait\MoveActionTestTrait;
use App\Tests\Trait\PublishActionTestTrait;
use EasyCorp\Bundle\EasyAdminBundle\Test\AbstractCrudTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Doctrine\ORM\EntityManagerInterface;

class FaqCrudControllerTest extends AbstractCrudTestCase
{
    protected AbstractDatabaseTool $databaseTool;
    protected EntityManagerInterface $entityManager;
    private ?User $adminUser = null; // Store the admin user for convenience
    use MoveActionTestTrait;
    use PublishActionTestTrait;

    protected function getControllerFqcn(): string
    {
        return FaqCrudController::class;
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
        $this->loadFixturesAndLoginAdmin([TestFaqFixtures::class]);

        $this->client->request('GET', $this->generateIndexUrl());
        
        static::assertResponseIsSuccessful("The index page should load successfully after login.");
        static::assertPageTitleContains('Questions/Réponses', "Page title should contain 'Questions/Réponses'.");
    }

    public function testNewPageLoadsSuccessfully(): void
    {
        $this->loadFixturesAndLoginAdmin([]); // User logged in, database purged
        $this->client->request('GET', $this->generateNewFormUrl());
        static::assertResponseIsSuccessful("La page de création de FAQ devrait se charger.");
        static::assertPageTitleContains('Ajouter une nouvelle question/réponse'); 

        $crawler = $this->client->getCrawler();
        $this->assertCount(1, $crawler->filter('input[name="Faq[question]"]'), "Le champ 'question' devrait exister.");
        $this->assertCount(1, $crawler->filter('textarea[name="Faq[reponse]"]'), "Le champ 'reponse' devrait exister.");
        $this->assertCount(1, $crawler->filter('input[name="Faq[publishFaq]"]'), "Le champ 'publishFaq' devrait exister.");
        // the positionFaq field is usually managed by Gedmo and not in the creation form.
    }

    public function testCreateNewFaqSuccessfully(): void
    {
        $this->loadFixturesAndLoginAdmin([]); // User logged in, database purged

        $testQuestion = 'Nouvelle question de test CRUD ?';
        $testReponse = 'Ceci est la réponse pour le test CRUD.';

        $this->client->request('GET', $this->generateNewFormUrl());
        static::assertResponseIsSuccessful();

        $this->client->submitForm('Créer la question/réponse', [ 
            'Faq[question]' => $testQuestion,
            'Faq[reponse]' => $testReponse,
            'Faq[publishFaq]' => true,
            // positionFaq is usually managed by Gedmo and not submitted in the form
        ]);

        //Verify that the response redirects to the index page
        $expectedIndexUrl = $this->generateIndexUrl();
        static::assertResponseRedirects($expectedIndexUrl, null, "Devrait rediriger vers la page d'index après création.");

        // Verfiy that the entity exists in the database
        $this->entityManager->clear(); 
        $newFaq = $this->entityManager->getRepository(Faq::class)->findOneBy(['question' => $testQuestion]);
        $this->assertNotNull($newFaq, "La nouvelle FAQ devrait exister en base de données.");
        if ($newFaq) {
            $this->assertEquals($testReponse, $newFaq->getReponse(), "La réponse de la FAQ créée ne correspond pas.");
            $this->assertTrue($newFaq->isPublishFaq(), "La FAQ créée devrait être publiée.");
            $this->assertNotNull($newFaq->getPositionFaq(), "La FAQ créée devrait avoir une position.");
        }
    }

    public function testEditPageLoadsSuccessfully(): void
    {
        $referenceRepository = $this->loadFixturesAndLoginAdmin([TestFaqFixtures::class]);
        $faqToEdit = $referenceRepository->getReference(TestFaqFixtures::FAQ_1_REF);
        $this->assertNotNull($faqToEdit);

        $this->client->request('GET', $this->generateEditFormUrl($faqToEdit->getIdFaq()));
        static::assertResponseIsSuccessful("La page d'édition devrait se charger.");
        static::assertPageTitleContains('Modifier Question/Réponse'); 

        $crawler = $this->client->getCrawler();
        $this->assertStringContainsString($faqToEdit->getQuestion(), $crawler->filter('input[name="Faq[question]"]')->attr('value'), "Le champ question n'est pas pré-rempli correctement.");
        $this->assertStringContainsString($faqToEdit->getReponse(), $crawler->filter('textarea[name="Faq[reponse]"]')->text(), "Le champ réponse n'est pas pré-rempli correctement.");
        if ($faqToEdit->isPublishFaq()) {
            $this->assertNotEmpty($crawler->filter('input[name="Faq[publishFaq]"]:checked')->count(), "La case 'publishFaq' devrait être cochée.");
        } else {
             $this->assertEmpty($crawler->filter('input[name="Faq[publishFaq]"]:checked')->count(), "La case 'publishFaq' ne devrait pas être cochée.");
        }
    }

    public function testUpdateExistingFaqSuccessfully(): void
    {
        $referenceRepository = $this->loadFixturesAndLoginAdmin([TestFaqFixtures::class]);
        $faqToUpdate = $referenceRepository->getReference(TestFaqFixtures::FAQ_1_REF);
        $this->assertNotNull($faqToUpdate);
        $faqId = $faqToUpdate->getIdFaq();

        $updatedQuestion = "Question mise à jour par le test CRUD !";
        $updatedReponse = "Réponse mise à jour également.";

        $this->client->request('GET', $this->generateEditFormUrl($faqId));
        static::assertResponseIsSuccessful();

        $this->client->submitForm('Sauvegarder les modifications', [ // Adaptez le texte du bouton
            'Faq[question]' => $updatedQuestion,
            'Faq[reponse]' => $updatedReponse,
            'Faq[publishFaq]' => false, 
        ]);

        $expectedIndexUrl = $this->generateIndexUrl();
        static::assertResponseRedirects($expectedIndexUrl, null, "Devrait rediriger vers la page d'index après mise à jour.");

        $this->entityManager->clear();
        $updatedFaq = $this->entityManager->getRepository(Faq::class)->find($faqId);
        $this->assertNotNull($updatedFaq);
        $this->assertEquals($updatedQuestion, $updatedFaq->getQuestion(), "La question n'a pas été mise à jour.");
        $this->assertEquals($updatedReponse, $updatedFaq->getReponse(), "La réponse n'a pas été mise à jour.");
        $this->assertFalse($updatedFaq->isPublishFaq(), "Le statut de publication n'a pas été mis à jour.");
    }

    public function testPublishAction(): void
    {
        $this->publishAction(
            entity: 'Faq',
            fixtureClass: TestFaqFixtures::class, 
            fixtureReference: TestFaqFixtures::FAQ_3_REF, 
            action : 'publish');
    }

    public function testUnpublishAction(): void
    {
        $this->publishAction(
            entity: 'Faq',
            fixtureClass: TestFaqFixtures::class, 
            fixtureReference: TestFAQFixtures::FAQ_1_REF, 
            action : 'unpublish');
    }

    public function testFaqMoveTop(): void
    {
        $this->moveAction(
            entity: 'Faq',
            fixtureClass: TestFaqFixtures::class,
            fixtureReference: TestFaqFixtures::FAQ_2_REF,
            direction: 'moveTop',
            initialExpectedPosition: 1,
            finalExpectedPosition: 0,
        );
    }

    public function testFaqMoveBottom(): void
    {
        $this->moveAction(
            entity: 'Faq',
            fixtureClass: TestFaqFixtures::class,
            fixtureReference: TestFaqFixtures::FAQ_2_REF,
            direction: 'moveBottom',
            initialExpectedPosition: 1,
            finalExpectedPosition: -1,
        );
    }

    public function testFaqMoveUp(): void
    {
        $this->moveAction(
            entity: 'Faq',
            fixtureClass: TestFaqFixtures::class,
            fixtureReference: TestFaqFixtures::FAQ_2_REF,
            direction: 'moveUp',
            initialExpectedPosition: 1,
            finalExpectedPosition: 0,
        );
    }

    public function testFaqMoveDown(): void
    {
        $this->moveAction(
            entity: 'Faq',
            fixtureClass: TestFaqFixtures::class,
            fixtureReference: TestFaqFixtures::FAQ_2_REF,
            direction: 'moveDown',
            initialExpectedPosition: 1,
            finalExpectedPosition: 2,
        );
    }

    public function testMovementActionsVisibilityOnIndexPage(): void
    {
        $referenceRepository = $this->loadFixturesAndLoginAdmin([TestFaqFixtures::class]);

        /** @var Faq $faqTop */
        $faqTop = $referenceRepository->getReference(TestFaqFixtures::FAQ_1_REF); // Position 0
        /** @var Faq $faqMiddle */
        $faqMiddle = $referenceRepository->getReference(TestFaqFixtures::FAQ_2_REF); // Position 1

        $crawler = $this->client->request('GET', $this->generateIndexUrl());
        $this->assertResponseIsSuccessful("La page d'index devrait se charger.");

        $rowTopSelector = sprintf('tr[data-id="%s"]', $faqTop->getIdFaq());
        $this->assertSelectorNotExists($rowTopSelector . ' a[data-action-name="moveUp"]', 'Bouton MoveUp ne doit pas être visible pour l\'élément du haut.');
        $this->assertSelectorNotExists($rowTopSelector . ' a[data-action-name="moveTop"]', 'Bouton MoveTop ne doit pas être visible pour l\'élément du haut.');
        $this->assertSelectorExists($rowTopSelector . ' a[data-action-name="moveDown"]', 'Bouton MoveDown doit être visible pour l\'élément du haut.');
        $this->assertSelectorExists($rowTopSelector . ' a[data-action-name="moveBottom"]', 'Bouton MoveBottom doit être visible pour l\'élément du haut.');

        $rowMiddleSelector = sprintf('tr[data-id="%s"]', $faqMiddle->getIdFaq());
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