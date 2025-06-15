<?php

namespace App\Tests\Controller\Admin;

use App\Controller\Admin\DashboardController;
use App\Controller\Admin\LocationTypeCrudController;
use App\Entity\LocationType;
use App\Entity\User;
use App\Tests\Fixtures\TestLocationTypeFixtures;
use App\Tests\Fixtures\TestRoleFixtures;
use App\Tests\Fixtures\TestUserFixtures;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Test\AbstractCrudTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class LocationTypeCrudControllerTest extends AbstractCrudTestCase
{
    protected AbstractDatabaseTool $databaseTool;
    protected EntityManagerInterface $entityManager;
    private ?User $adminUser = null;

    protected function getControllerFqcn(): string
    {
        return LocationTypeCrudController::class;
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
        $this->loadFixturesAndLoginAdmin([TestLocationTypeFixtures::class]);
        
        $indexUrl = $this->generateIndexUrl();
        $this->client->request('GET', $indexUrl);
        
        static::assertResponseIsSuccessful("La page d'index des Sections d'Information devrait se charger.");
        static::assertPageTitleContains('Type de lieux'); 
    }

    public function testNewPageLoadsSuccessfully(): void
    {
        $this->loadFixturesAndLoginAdmin([]); // User logged in, database purged

        $this->client->request('GET', $this->generateNewFormUrl());
        static::assertResponseIsSuccessful("La page de création de Tpe de Location devrait se charger.");
        static::assertPageTitleContains('Ajouter un nouveau type de lieu');

        $crawler = $this->client->getCrawler();
        $this->assertCount(1, $crawler->filter('input[name="LocationType[nameLocationType]"]'), "Le champ 'nameLocationType' devrait exister.");
        $this->assertCount(1, $crawler->filter('input[name="LocationType[symbol][file]"]'), "Le champ upload pour 'symbol' devrait exister.");
    }

    public function testCreateNewLocationTypeSuccessfully(): void
    {
        $this->loadFixturesAndLoginAdmin([]); // User loggué, base purgée

        $testNameLocationType = 'Nouveau Type de Lieu Test par submitForm';

        // --- Préparation du fichier pour l'upload ---
        $projectDir = $this->getContainer()->getParameter('kernel.project_dir');
        $originalTestImagePath = $projectDir . '/tests/Fixtures/files/symbol.png'; // <--- Chemin vers votre VRAI fichier PNG de test

        $this->assertFileExists($originalTestImagePath, "Le fichier image de test '$originalTestImagePath' est introuvable.");

        // Créer un nom de fichier temporaire unique pour l'upload
        $uploadFileName = 'test_symbole_upload_' . uniqid() . '.png'; // Nom du fichier tel qu'il sera "uploadé"
        $tempFilePathForUpload = sys_get_temp_dir() . '/' . $uploadFileName;

        // Copier le fichier image de test vers l'emplacement temporaire
        copy($originalTestImagePath, $tempFilePathForUpload);

        $uploadedFileObject = new UploadedFile(
            $tempFilePathForUpload,     // Chemin vers la copie temporaire du fichier image
            $uploadFileName,            // Nom que le serveur "verra"
            'image/png',               // Type MIME correct
            null,                      // Taille (laisser null)
            true                       // Mode test = true
        );
        // --- Fin de la préparation du fichier ---

        $crawler = $this->client->request('GET', $this->generateNewFormUrl());
        static::assertResponseIsSuccessful("La page du formulaire de création de Type de Lieu devrait se charger.");

        $form = $crawler->selectButton("Créer le type de lieu")->form(); // Adaptez le texte du bouton

        $form['LocationType[nameLocationType]'] = $testNameLocationType;
        // Utiliser l'objet UploadedFile pour le champ de fichier
        $form['LocationType[symbol][file]'] = $uploadedFileObject;

        $this->client->submit($form);

        $expectedIndexUrl = $this->generateIndexUrl();
        // Vérification plus détaillée de la redirection pour aider au débogage
        if (!$this->client->getResponse()->isRedirect($expectedIndexUrl)) {
            $formErrors = $this->client->getCrawler()->filter('.invalid-feedback, .text-danger'); // Chercher les messages d'erreur
            $errorMessages = $formErrors->each(fn ($node) => $node->text());
            $this->fail(sprintf(
                "La création aurait dû rediriger vers l'index '%s'. Statut reçu: %s. Redirigé vers: '%s'. Erreurs de validation possibles: [%s]. Contenu: %s",
                $expectedIndexUrl,
                $this->client->getResponse()->getStatusCode(),
                $this->client->getResponse()->headers->get('Location') ?? 'N/A',
                implode(', ', $errorMessages),
                substr($this->client->getResponse()->getContent(), 0, 1000)
            ));
        }
        static::assertResponseRedirects($expectedIndexUrl, null, "Devrait rediriger vers l'index après création.");

        $this->entityManager->clear();
        /** @var LocationType|null $newLocationType */
        $newLocationType = $this->entityManager->getRepository(LocationType::class)->findOneBy(['nameLocationType' => $testNameLocationType]);
        $this->assertNotNull($newLocationType, "Le nouveau Type de Lieu '$testNameLocationType' devrait exister en base de données.");

        if ($newLocationType) {
            $this->assertNotNull($newLocationType->getSymbol(), "La propriété 'symbol' ne devrait pas être nulle après l'upload.");
            $this->assertStringEndsWith('.png', $newLocationType->getSymbol()); // Ou .webp si LiipImagine convertit
            $this->assertStringContainsString(pathinfo($uploadFileName, PATHINFO_FILENAME), $newLocationType->getSymbol());


            $actualUploadedFilePath = $projectDir . '/public/uploads/locations/' . $newLocationType->getSymbol();
            $this->assertFileExists($actualUploadedFilePath, "Le fichier uploadé '$actualUploadedFilePath' devrait exister sur le serveur.");
            if (file_exists($actualUploadedFilePath)) {
                @unlink($actualUploadedFilePath); // Nettoyer le fichier uploadé
            }
        }

        if (file_exists($tempFilePathForUpload)) {
            @unlink($tempFilePathForUpload); // Nettoyer la copie temporaire
        }
    }

    public function testEditPageLoadsSuccessfully(): void
    {
        $referenceRepository = $this->loadFixturesAndLoginAdmin([TestLocationTypeFixtures::class]);
        /** @var Faq $faqToEdit */
        $locationTypeToEdit = $referenceRepository->getReference(TestLocationTypeFixtures::LOC_TYPE_SCENE_REF);
        $this->assertNotNull($locationTypeToEdit);

        $this->client->request('GET', $this->generateEditFormUrl($locationTypeToEdit->getIdLocationType()));
        static::assertResponseIsSuccessful("La page d'édition devrait se charger.");
        static::assertPageTitleContains('Modifier Type de lieu'); 

        $crawler = $this->client->getCrawler();
        $this->assertStringContainsString($locationTypeToEdit->getNameLocationType(), $crawler->filter('input[name="LocationType[nameLocationType]"]')->attr('value'));
        // Vérifier la présence du champ image (il n'affichera pas la valeur de l'ancien fichier directement dans un input)
        $this->assertCount(1, $crawler->filter('input[name="LocationType[symbol][file]"]'));
    }

    public function testUpdateExistingLocationTypeSuccessfully(): void
    {
        $referenceRepository = $this->loadFixturesAndLoginAdmin([TestLocationTypeFixtures::class]);
        /** @var LocationType $locationTypeToUpdate */
        $locationTypeToUpdate = $referenceRepository->getReference(TestLocationTypeFixtures::LOC_TYPE_SCENE_REF);
        $this->assertNotNull($locationTypeToUpdate);
        $locationTypeId = $locationTypeToUpdate->getIdLocationType();

        $updatedName = "Bar VIP Lounge Test";

        $this->client->request('GET', $this->generateEditFormUrl($locationTypeId));
        static::assertResponseIsSuccessful();

        $this->client->submitForm('Sauvegarder les modifications', [ // Adaptez le texte du bouton
            'LocationType[nameLocationType]' => $updatedName,
        ]);

        $expectedIndexUrl = $this->generateIndexUrl();
        static::assertResponseRedirects($expectedIndexUrl, null, "Devrait rediriger vers la page d'index après mise à jour.");

        $this->entityManager->clear();
        /** @var LocationType|null $updatedLocationType */
        $updatedLocationType = $this->entityManager->getRepository(LocationType::class)->find($locationTypeId);
        $this->assertNotNull($updatedLocationType);
        $this->assertEquals($updatedName, $updatedLocationType->getNameLocationType(), "Le nom du type n'a pas été mise à jour.");
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