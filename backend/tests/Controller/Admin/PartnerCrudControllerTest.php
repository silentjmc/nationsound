<?php

namespace App\Tests\Controller\Admin;

use App\Controller\Admin\DashboardController;
use App\Controller\Admin\PartnerCrudController;
use App\Entity\Partner;
use App\Entity\User;
use App\Tests\Fixtures\TestPartnerFixtures;
use App\Tests\Fixtures\TestPartnerTypeFixtures;
use App\Tests\Fixtures\TestRoleFixtures;
use App\Tests\Fixtures\TestUserFixtures;
use App\Tests\Trait\PublishActionTestTrait;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Test\AbstractCrudTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PartnerCrudControllerTest extends AbstractCrudTestCase
{
    protected AbstractDatabaseTool $databaseTool;
    protected EntityManagerInterface $entityManager;
    private ?User $adminUser = null;
    use PublishActionTestTrait;

    protected function getControllerFqcn(): string
    {
        return PartnerCrudController::class;
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
            TestPartnerTypeFixtures::class, 
            TestPartnerFixtures::class
        ]);
        $this->client->request('GET', $this->generateIndexUrl());
        static::assertResponseIsSuccessful("La page d'index des Partenaires devrait se charger.");
        static::assertPageTitleContains('Partenaires'); 
    }

    public function testNewPageLoadsSuccessfully(): void
    {
        $this->loadFixturesAndLoginAdmin([TestPartnerTypeFixtures::class]); // Pour le champ AssociationField typePartner
        $this->client->request('GET', $this->generateNewFormUrl());
        static::assertResponseIsSuccessful("La page de création de Partenaire devrait se charger.");
        static::assertPageTitleContains('Ajouter un nouveau partenaire');

        $crawler = $this->client->getCrawler();
        $this->assertCount(1, $crawler->filter('input[name="Partner[namePartner]"]'), "Le champ 'namePartner' devrait exister.");
        $this->assertCount(1, $crawler->filter('input[name="Partner[imagePartner][file]"]'), "Le champ upload pour 'imagePartner' devrait exister.");
        $this->assertCount(1, $crawler->filter('input[name="Partner[url]"]'), "Le champ 'url' devrait exister.");
        $this->assertCount(1, $crawler->filter('select[name="Partner[typePartner]"]'), "Le champ select 'typePartner' devrait exister.");
        $this->assertCount(1, $crawler->filter('input[name="Partner[publishPartner]"]'), "Le champ 'publishPartner' devrait exister.");
    }

    public function testCreateNewPartnerSuccessfully(): void
    {
        $referenceRepository = $this->loadFixturesAndLoginAdmin([TestPartnerTypeFixtures::class]);
  
        $partnerType = $referenceRepository->getReference(TestPartnerTypeFixtures::TYPE_SPONSOR_REF);
        $this->assertNotNull($partnerType, "Le type de partenaire de test n'a pas été trouvé.");

        $testName = 'Nouveau Partenaire Super Cool';
        $testUrl = 'https://supercool.test';

        // Préparation du fichier pour l'upload
        $projectDir = $this->getContainer()->getParameter('kernel.project_dir');
        $originalTestImagePath = $projectDir . '/tests/Fixtures/files/partner_logo.png'; // Créez ce fichier
        $this->assertFileExists($originalTestImagePath, "Fichier image de test '$originalTestImagePath' introuvable.");
        $uploadFileName = 'partner_upload_' . uniqid() . '.png';
        $tempFilePathForUpload = sys_get_temp_dir() . '/' . $uploadFileName;
        copy($originalTestImagePath, $tempFilePathForUpload);
        $uploadedFileObject = new UploadedFile($tempFilePathForUpload, $uploadFileName, 'image/png', null, true);

        $this->client->request('GET', $this->generateNewFormUrl());
        static::assertResponseIsSuccessful();

        $this->client->submitForm('Créer le partenaire', [
            'Partner[namePartner]' => $testName,
            'Partner[imagePartner][file]' => $uploadedFileObject,
            'Partner[url]' => $testUrl,
            'Partner[typePartner]' => $partnerType->getIdPartnerType(), // Soumettre l'ID du type
            'Partner[publishPartner]' => true,
        ]);

        $expectedIndexUrl = $this->generateIndexUrl();
        static::assertResponseRedirects($expectedIndexUrl, null, "Devrait rediriger vers l'index après création.");

        $this->entityManager->clear();
        $newPartner = $this->entityManager->getRepository(Partner::class)->findOneBy(['namePartner' => $testName]);
        $this->assertNotNull($newPartner, "Le nouveau Partenaire devrait exister.");
        if ($newPartner) {
            $this->assertEquals($testUrl, $newPartner->getUrl());
            $this->assertTrue($newPartner->isPublishPartner());
            $this->assertNotNull($newPartner->getTypePartner());
            $this->assertEquals($partnerType->getIdPartnerType(), $newPartner->getTypePartner()->getIdPartnerType());
            $this->assertNotNull($newPartner->getImagePartner());
            $this->assertStringContainsString(pathinfo($uploadFileName, PATHINFO_FILENAME), $newPartner->getImagePartner());

            $actualUploadedFilePath = $projectDir . '/public/uploads/partners/' . $newPartner->getImagePartner();
            $this->assertFileExists($actualUploadedFilePath);
            if (file_exists($actualUploadedFilePath)) @unlink($actualUploadedFilePath);
        }
        if (file_exists($tempFilePathForUpload)) @unlink($tempFilePathForUpload);
    }

    public function testEditPageLoadsSuccessfully(): void
    {
        $referenceRepository = $this->loadFixturesAndLoginAdmin([
            TestPartnerTypeFixtures::class,
            TestPartnerFixtures::class
        ]);
        $partnerToEdit = $referenceRepository->getReference(TestPartnerFixtures::PARTNER_PUBLISHED_1_REF);
        $this->assertNotNull($partnerToEdit);

        $this->client->request('GET', $this->generateEditFormUrl($partnerToEdit->getIdPartner()));
        static::assertResponseIsSuccessful("La page d'édition devrait se charger.");
        static::assertPageTitleContains('Modifier Partenaire');

        $crawler = $this->client->getCrawler();
        $this->assertStringContainsString($partnerToEdit->getNamePartner(), $crawler->filter('input[name="Partner[namePartner]"]')->attr('value'));
        $this->assertStringContainsString($partnerToEdit->getUrl(), $crawler->filter('input[name="Partner[url]"]')->attr('value'));

        $this->assertEquals((string)$partnerToEdit->getTypePartner()->getIdPartnerType(), $crawler->filter('select[name="Partner[typePartner]"] option[selected="selected"]')->attr('value'));
    }

    public function testUpdateExistingPartnerSuccessfully(): void
        {
        $referenceRepository = $this->loadFixturesAndLoginAdmin([
            TestPartnerTypeFixtures::class,
            TestPartnerFixtures::class
        ]);

        $partnerToUpdate = $referenceRepository->getReference(TestPartnerFixtures::PARTNER_PUBLISHED_1_REF);
        $this->assertNotNull($partnerToUpdate);
        $partnerId = $partnerToUpdate->getIdPartner();
        $originalImageNameBeforeUpdate = $partnerToUpdate->getImagePartner(); // Nom de l'image existante

        $updatedName = "Partenaire Mis à Jour Avec Nouvelle Image";

        //use the project directory to find the test image
        $projectDir = $this->getContainer()->getParameter('kernel.project_dir');
        $sourceTestImagePath = $projectDir . '/tests/Fixtures/files/partner_logo.png'; // Votre fichier existant
        $this->assertFileExists($sourceTestImagePath, "Fichier image de test source '$sourceTestImagePath' introuvable.");

        // base name for the temporary file
        $baseNameForTempFile = uniqid('test_update_upload_', true); 
        $tempFilePathForUpload = sys_get_temp_dir() . '/' . $baseNameForTempFile . '.png';
        copy($sourceTestImagePath, $tempFilePathForUpload);

        // Name for the new upload, even if the source content is the same.
        $clientOriginalNameForNewUpload = 'partner_logo_updated_version.png'; 
        
        $newUploadedFileObject = new UploadedFile(
            $tempFilePathForUpload,
            $clientOriginalNameForNewUpload, 
            'image/png',                     
            null,                            
            true                             
        );

        $this->client->request('GET', $this->generateEditFormUrl($partnerId));
        static::assertResponseIsSuccessful();

        $this->client->submitForm('Sauvegarder les modifications', [ 
            'Partner[namePartner]' => $updatedName,
            'Partner[imagePartner][file]' => $newUploadedFileObject,
        ]);

        $expectedIndexUrl = $this->generateIndexUrl();
        static::assertResponseRedirects($expectedIndexUrl, null, "Devrait rediriger vers la page d'index après mise à jour.");

        $this->entityManager->clear();

        $updatedPartner = $this->entityManager->getRepository(Partner::class)->find($partnerId);
        $this->assertNotNull($updatedPartner);
        $this->assertEquals($updatedName, $updatedPartner->getNamePartner());
        
        $this->assertNotNull($updatedPartner->getImagePartner(), "Le nom de l'image ne devrait pas être null après la mise à jour.");
        $this->assertNotEquals($originalImageNameBeforeUpdate, $updatedPartner->getImagePartner(), "Le nom de l'image aurait dû changer après le téléversement d'une 'nouvelle' version.");
        $expectedImageNamePart = $baseNameForTempFile; 
        $this->assertStringContainsStringIgnoringCase(
            $expectedImageNamePart, 
            $updatedPartner->getImagePartner(), 
            "Le nom de l'image stocké ('{$updatedPartner->getImagePartner()}') devrait contenir la base du nom du fichier temporaire unique ('{$expectedImageNamePart}')."
        );
        $this->assertStringEndsWith('.webp', $updatedPartner->getImagePartner(), "L'image devrait être convertie en .webp.");

        // Verify of physical file existence and cleanup
        $actualUploadedFileBasePath = $projectDir . '/public/uploads/partners/';
        if (str_contains($projectDir, 'public_html/symfony')) {
             $actualUploadedFileBasePath = dirname($projectDir) . '/admin/uploads/partners/';
        }
        
        $newImageFilePath = $actualUploadedFileBasePath . $updatedPartner->getImagePartner();
        $this->assertFileExists($newImageFilePath, "Le nouveau fichier image ('{$newImageFilePath}') devrait exister.");

        // Clean up the old image file if it exists and is different
        if ($originalImageNameBeforeUpdate && $originalImageNameBeforeUpdate !== $updatedPartner->getImagePartner()) {
            $oldImageFilePath = $actualUploadedFileBasePath . $originalImageNameBeforeUpdate;
            if (file_exists($oldImageFilePath)) {
                 @unlink($oldImageFilePath); 
            }
        }

        // Nettoyage des fichiers de test
        if (file_exists($newImageFilePath)) {
            @unlink($newImageFilePath);
        }
        if (file_exists($tempFilePathForUpload)) {
            @unlink($tempFilePathForUpload);
        }
    }

    public function testPublishAction(): void
    {
        $this->publishAction(
            entity: 'Partner',
            fixtureClass: TestPartnerFixtures::class, 
            fixtureReference: TestPartnerFixtures::PARTNER_UNPUBLISHED_REF, 
            action : 'publish');
    }

    public function testUnpublishAction(): void
    {
        $this->publishAction(
            entity: 'Partner',
            fixtureClass: TestPartnerFixtures::class, 
            fixtureReference: TestPartnerFixtures::PARTNER_PUBLISHED_1_REF, 
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