<?php

namespace App\Tests\Controller\Admin;

use App\Controller\Admin\DashboardController;
use App\Controller\Admin\PartnerTypeCrudController;
use App\Entity\PartnerType;
use App\Entity\User;
use App\Tests\Fixtures\TestPartnerTypeFixtures;
use App\Tests\Fixtures\TestRoleFixtures;
use App\Tests\Fixtures\TestUserFixtures;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Test\AbstractCrudTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;

class PartnerTypeCrudControllerTest extends AbstractCrudTestCase
{
    protected AbstractDatabaseTool $databaseTool;
    protected EntityManagerInterface $entityManager;
    private ?User $adminUser = null;

    protected function getControllerFqcn(): string
    {
        return PartnerTypeCrudController::class;
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

        $this->assertNotNull($this->adminUser, "Admin user 'admin@email.com' not found after fixture load. Check UserFixtures.");

        $this->client->loginUser($this->adminUser);

        return $executor->getReferenceRepository();
    }

     public function testIndexPageLoads(): void
    {
        $this->loadFixturesAndLoginAdmin([TestPartnerTypeFixtures::class]);

        $this->client->request('GET', $this->generateIndexUrl());
        
        static::assertResponseIsSuccessful("The index page should load successfully after login.");
        static::assertPageTitleContains('Type de partenaires');
    }

    public function testNewPageLoadsSuccessfully(): void
    {
        $this->loadFixturesAndLoginAdmin([]);

        $this->client->request('GET', $this->generateNewFormUrl());

        static::assertResponseIsSuccessful("La page de création de Type de Partenaire devrait se charger.");
        static::assertPageTitleContains('Ajouter un nouveau type de partenaire');

        $crawler = $this->client->getCrawler();
        $this->assertCount(1, $crawler->filter('input[name="PartnerType[titlePartnerType]"]'), "Le champ 'titlePartnerType' devrait exister.");
    }

    public function testCreateNewPartnerTypeSuccessfully(): void
    {
        $this->loadFixturesAndLoginAdmin([]);
        $testTitle = 'Type Partenaire Test Création';

        $this->client->request('GET', $this->generateNewFormUrl());
        static::assertResponseIsSuccessful();

        $this->client->submitForm('Créer le type de partenaire', [ // Texte du bouton de PartnerTypeCrudController
            'PartnerType[titlePartnerType]' => $testTitle,
        ]);

        $expectedIndexUrl = $this->generateIndexUrl();
        static::assertResponseRedirects($expectedIndexUrl, null, "Devrait rediriger vers l'index après création.");

        $this->entityManager->clear();
        $newPartnerType = $this->entityManager->getRepository(PartnerType::class)->findOneBy(['titlePartnerType' => $testTitle]);
        $this->assertNotNull($newPartnerType, "Le nouveau Type de Partenaire devrait exister.");
        if ($newPartnerType) {
            $this->assertEquals($testTitle, $newPartnerType->getTitlePartnerType());
            // Vérifier les champs d'audit si EntityListener est censé les remplir
            $this->assertNotNull($newPartnerType->getDateModificationPartnerType());
            $this->assertEquals($this->adminUser->getFullName(), $newPartnerType->getUserModificationPartnerType());
        }
    }

    public function testEditPageLoadsSuccessfully(): void
    {
        $referenceRepository = $this->loadFixturesAndLoginAdmin([TestPartnerTypeFixtures::class]);
        $typeToEdit = $referenceRepository->getReference(TestPartnerTypeFixtures::TYPE_SPONSOR_REF);
        $this->assertNotNull($typeToEdit);

        $this->client->request('GET', $this->generateEditFormUrl($typeToEdit->getIdPartnerType()));
        static::assertResponseIsSuccessful("La page d'édition devrait se charger.");
        static::assertPageTitleContains('Modifier Type de partenaire'); // Adaptez au titre réel généré

        $crawler = $this->client->getCrawler();
        $this->assertStringContainsString($typeToEdit->getTitlePartnerType(), $crawler->filter('input[name="PartnerType[titlePartnerType]"]')->attr('value'));
    }

    public function testUpdateExistingPartnerTypeSuccessfully(): void // Nom de méthode corrigé
    {
        $referenceRepository = $this->loadFixturesAndLoginAdmin([TestPartnerTypeFixtures::class]);
        $typeToUpdate = $referenceRepository->getReference(TestPartnerTypeFixtures::TYPE_MEDIA_REF);
        $this->assertNotNull($typeToUpdate);
        $typeId = $typeToUpdate->getIdPartnerType();

        $updatedTitle = "Partenaire Média Mis à Jour !";

        $this->client->request('GET', $this->generateEditFormUrl($typeId));
        static::assertResponseIsSuccessful();

        $this->client->submitForm('Sauvegarder les modifications', [ // Texte par défaut du bouton de sauvegarde en édition
            'PartnerType[titlePartnerType]' => $updatedTitle,
        ]);

        $expectedIndexUrl = $this->generateIndexUrl();
        static::assertResponseRedirects($expectedIndexUrl, null, "Devrait rediriger vers l'index après mise à jour.");

        $this->entityManager->clear();

        $updatedType = $this->entityManager->getRepository(PartnerType::class)->find($typeId);
        $this->assertNotNull($updatedType);
        $this->assertEquals($updatedTitle, $updatedType->getTitlePartnerType());
        $this->assertNotNull($updatedType->getDateModificationPartnerType());
        $this->assertEquals($this->adminUser->getFullName(), $updatedType->getUserModificationPartnerType());
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