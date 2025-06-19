<?php

namespace App\Tests\Controller\Admin;

use App\Controller\Admin\ArtistCrudController;
use App\Controller\Admin\DashboardController;
use App\Entity\Artist;
use App\Entity\User;
use App\Tests\Fixtures\TestArtistFixtures;
use App\Tests\Fixtures\TestRoleFixtures;
use App\Tests\Fixtures\TestUserFixtures;
use App\Tests\Trait\PublishActionTestTrait;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Test\AbstractCrudTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ArtistCrudControllerTest extends AbstractCrudTestCase
{
    protected AbstractDatabaseTool $databaseTool;
    protected EntityManagerInterface $entityManager;
    private ?User $adminUser = null;
    use PublishActionTestTrait;

    protected function getControllerFqcn(): string
    {
        return ArtistCrudController::class;
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
            TestArtistFixtures::class
        ]);
        $this->client->request('GET', $this->generateIndexUrl());
        static::assertResponseIsSuccessful("La page d'index des Artistes devrait se charger.");
        static::assertPageTitleContains('Artistes'); 
    }

    public function testNewPageLoadsSuccessfully(): void
    {
        $this->loadFixturesAndLoginAdmin(); 
        $this->client->request('GET', $this->generateNewFormUrl());
        static::assertResponseIsSuccessful("La page de création d'Artistes devrait se charger.");
        static::assertPageTitleContains('Ajouter un nouvel artiste');

        $crawler = $this->client->getCrawler();
        $this->assertCount(1, $crawler->filter('input[name="Artist[nameArtist]"]'), "Le champ 'nameArtist' devrait exister.");
        $this->assertCount(1, $crawler->filter('textarea[name="Artist[contentArtist]"]'), "Le champ 'contentArtist' devrait exister.");
        $this->assertCount(1, $crawler->filter('input[name="Artist[imageArtist][file]"]'), "Le champ upload pour 'imageArtist' devrait exister.");
        $this->assertCount(1, $crawler->filter('input[name="Artist[thumbnail][file]"]'), "Le champ upload pour 'imagePartner' devrait exister.");
        $this->assertCount(1, $crawler->filter('input[name="Artist[typeMusic]"]'), "Le champ 'typeMusic' devrait exister.");
    }

    public function testCreateNewArtistSuccessfully(): void
    {
        $referenceRepository = $this->loadFixturesAndLoginAdmin();
  
        $testNameArtist = 'Nouvel Artiste';
        $testContentArtist = 'Un super artiste pour les tests.';
        $testTypeMusic = 'Rock Test';

        // Préparation du fichier pour l'upload
        $projectDir = $this->getContainer()->getParameter('kernel.project_dir');
        $originalTestImagePath = $projectDir . '/tests/Fixtures/files/image_artist.png'; // Créez ce fichier
        $this->assertFileExists($originalTestImagePath, "Fichier image de test '$originalTestImagePath' introuvable.");
        $uploadFileName = 'artist_upload_' . uniqid() . '.webp';
        $tempFilePathForUpload = sys_get_temp_dir() . '/' . $uploadFileName;
        copy($originalTestImagePath, $tempFilePathForUpload);
        $uploadedFileObject = new UploadedFile($tempFilePathForUpload, $uploadFileName, 'image/webp', null, true);

        $this->client->request('GET', $this->generateNewFormUrl());
        static::assertResponseIsSuccessful();

        $this->client->submitForm("Créer un artiste", [
            'Artist[nameArtist]' => $testNameArtist,
            'Artist[contentArtist]' => $testContentArtist,
            'Artist[typeMusic]' => $testTypeMusic,
            'Artist[imageArtist][file]' => $uploadedFileObject,
        ]);

        $expectedIndexUrl = $this->generateIndexUrl();
        static::assertResponseRedirects($expectedIndexUrl, null, "Devrait rediriger vers l'index après création.");

        $this->entityManager->clear();
        $newArtist = $this->entityManager->getRepository(Artist::class)->findOneBy(['nameArtist' => $testNameArtist]);
        $this->assertNotNull($newArtist, "Le nouvel Artiste devrait exister.");
        if ($newArtist) {
            $this->assertEquals($testContentArtist, $newArtist->getContentArtist());
            $this->assertEquals($testTypeMusic, $newArtist->getTypeMusic());

            $this->assertNotNull($newArtist->getImageArtist());
            $this->assertStringContainsString(pathinfo($uploadFileName, PATHINFO_FILENAME), $newArtist->getImageArtist());
            $actualUploadedImagePath = $projectDir . '/public/uploads/artists/' . $newArtist->getImageArtist();
            $this->assertFileExists($actualUploadedImagePath);
            if (file_exists($actualUploadedImagePath)) @unlink($actualUploadedImagePath);

            $this->assertNotNull($newArtist->getThumbnail());
            $this->assertStringContainsString(pathinfo($uploadFileName, PATHINFO_FILENAME), $newArtist->getThumbnail());
            $actualUploadedThumbnailPath = $projectDir . '/public/uploads/artists/' . $newArtist->getThumbnail();
            $this->assertFileExists($actualUploadedThumbnailPath);
            if (file_exists($actualUploadedThumbnailPath)) @unlink($actualUploadedThumbnailPath);
        }
        if (file_exists($tempFilePathForUpload)) @unlink($tempFilePathForUpload);
    }

    public function testEditPageLoadsSuccessfully(): void
    {
        $referenceRepository = $this->loadFixturesAndLoginAdmin([TestArtistFixtures::class]);
        $artistToEdit = $referenceRepository->getReference(TestArtistFixtures::ARTIST_ALPHA_EVENTS_REF);
        $this->assertNotNull($artistToEdit);

        $this->client->request('GET', $this->generateEditFormUrl($artistToEdit->getIdArtist()));
        static::assertResponseIsSuccessful("La page d'édition devrait se charger.");
        static::assertPageTitleContains('Modifier Artiste');

        $crawler = $this->client->getCrawler();
        $this->assertStringContainsString($artistToEdit->getNameArtist(), $crawler->filter('input[name="Artist[nameArtist]"]')->attr('value'));
        $this->assertStringContainsString($artistToEdit->getContentArtist(), $crawler->filter('textarea[name="Artist[contentArtist]"]')->text());
        $this->assertStringContainsString($artistToEdit->getTypeMusic(), $crawler->filter('input[name="Artist[typeMusic]"]')->attr('value'));
    }

    public function testUpdateExistingPartnerSuccessfully(): void
        {
        $referenceRepository = $this->loadFixturesAndLoginAdmin([TestArtistFixtures::class]);

        $artistToUpdate = $referenceRepository->getReference(TestArtistFixtures::ARTIST_ALPHA_EVENTS_REF);
        $this->assertNotNull($artistToUpdate);
        $artistId = $artistToUpdate->getIdArtist();
        $originalImageNameBeforeUpdate = $artistToUpdate->getImageArtist(); // Nom de l'image existante
        $originalThumbnailNameBeforeUpdate = $artistToUpdate->getThumbnail(); // Nom du thumbnail existante

        $updatedName = "Artiste Mis à Jour Avec Nouvelles Images";

        //use the project directory to find the test image
        $projectDir = $this->getContainer()->getParameter('kernel.project_dir');
        $sourceTestImagePath = $projectDir . '/tests/Fixtures/files/image_artist.png'; // Votre fichier existant
        $this->assertFileExists($sourceTestImagePath, "Fichier image de test source '$sourceTestImagePath' introuvable.");

        $sourceTestThumbnailPath = $projectDir . '/tests/Fixtures/files/thumbnail_artist.png'; // Votre fichier existant
        $this->assertFileExists($sourceTestThumbnailPath, "Fichier image de test source '$sourceTestThumbnailPath' introuvable.");

        // base name for the temporary file
        $baseNameForTempImage = uniqid('test_update_upload_', true); 
        $baseNameForTempThumbnail = uniqid('thumb_test_update_upload_', true); 
        $tempImagePathForUpload = sys_get_temp_dir() . '/' . $baseNameForTempImage . '.webp';
        $tempThumbnailPathForUpload = sys_get_temp_dir() . '/' . $baseNameForTempThumbnail . '.webp';
        copy($sourceTestImagePath, $tempImagePathForUpload);
        copy($sourceTestThumbnailPath, $tempThumbnailPathForUpload);

        // Name for the new upload, even if the source content is the same.
        $clientOriginalImageNameForNewUpload = 'alpha_event.webp'; 
        $clientOriginalThumbnailNameForNewUpload = 'thumbnail_alpha_event.webp'; 
        
        $newUploadedFileImage = new UploadedFile(
            $tempImagePathForUpload,
            $clientOriginalImageNameForNewUpload, 
            'image/webp',                     
            null,                            
            true                             
        );

        $newUploadedFileThumbnail = new UploadedFile(
            $tempThumbnailPathForUpload,
            $clientOriginalThumbnailNameForNewUpload, 
            'image/webp',                     
            null,                            
            true                             
        );

        $this->client->request('GET', $this->generateEditFormUrl($artistId));
        static::assertResponseIsSuccessful();

        $this->client->submitForm('Sauvegarder les modifications', [ 
            'Artist[nameArtist]' => $updatedName,
            'Artist[imageArtist][file]' => $newUploadedFileImage,
            'Artist[thumbnail][file]' => $newUploadedFileThumbnail,
        ]);

        $expectedIndexUrl = $this->generateIndexUrl();
        static::assertResponseRedirects($expectedIndexUrl, null, "Devrait rediriger vers la page d'index après mise à jour.");

        $this->entityManager->clear();

        $updatedArtist = $this->entityManager->getRepository(Artist::class)->find($artistId);
        $this->assertNotNull($updatedArtist);
        $this->assertEquals($updatedName, $updatedArtist->getNameArtist());
        
        $this->assertNotNull($updatedArtist->getImageArtist(), "Le nom de l'image ne devrait pas être null après la mise à jour.");
        $this->assertNotEquals($originalImageNameBeforeUpdate, $updatedArtist->getImageArtist(), "Le nom de l'image aurait dû changer après le téléversement d'une 'nouvelle' version.");
        $expectedImageNamePart = $baseNameForTempImage; 
        $this->assertStringContainsStringIgnoringCase(
            $expectedImageNamePart, 
            $updatedArtist->getImageArtist(), 
            "Le nom de l'image stocké ('{$updatedArtist->getImageArtist()}') devrait contenir la base du nom du fichier temporaire unique ('{$expectedImageNamePart}')."
        );
        $this->assertStringEndsWith('.webp', $updatedArtist->getImageArtist(), "L'image devrait être convertie en .webp.");

        $expectedThumbnailNamePart = $baseNameForTempThumbnail; 
        $this->assertStringContainsStringIgnoringCase(
            $expectedThumbnailNamePart, 
            $updatedArtist->getThumbnail(), 
            "Le nom de l'image stocké ('{$updatedArtist->getThumbnail()}') devrait contenir la base du nom du fichier temporaire unique ('{$expectedThumbnailNamePart}')."
        );
        $this->assertStringEndsWith('.webp', $updatedArtist->getImageArtist(), "L'image devrait être convertie en .webp.");


        // Verify of physical file existence and cleanup
        $actualUploadedFileBasePath = $projectDir . '/public/uploads/artists/';
        if (str_contains($projectDir, 'public_html/symfony')) {
             $actualUploadedFileBasePath = dirname($projectDir) . '/admin/uploads/artists/';
        }
        
        $newImageFilePath = $actualUploadedFileBasePath . $updatedArtist->getImageArtist();
        $this->assertFileExists($newImageFilePath, "Le nouveau fichier image ('{$newImageFilePath}') devrait exister.");

        $newThumbnailFilePath = $actualUploadedFileBasePath . $updatedArtist->getThumbnail();
        $this->assertFileExists($newThumbnailFilePath, "Le nouveau fichier image ('{$newThumbnailFilePath}') devrait exister.");

        // Clean up the old image file if it exists and is different
        if ($originalImageNameBeforeUpdate && $originalImageNameBeforeUpdate !== $updatedArtist->getImageArtist()) {
            $oldImageFilePath = $actualUploadedFileBasePath . $originalImageNameBeforeUpdate;
            if (file_exists($oldImageFilePath)) {
                 @unlink($oldImageFilePath); 
            }
        }

        if ($originalThumbnailNameBeforeUpdate && $originalThumbnailNameBeforeUpdate !== $updatedArtist->getThumbnail()) {
            $oldThumbnailFilePath = $actualUploadedFileBasePath . $originalThumbnailNameBeforeUpdate;
            if (file_exists($oldThumbnailFilePath)) {
                 @unlink($oldThumbnailFilePath); 
            }
        }

        // Nettoyage des fichiers de test
        if (file_exists($newImageFilePath)) {
            @unlink($newImageFilePath);
        }
        if (file_exists($newThumbnailFilePath)) {
            @unlink($newThumbnailFilePath);
        }
        
        if (file_exists($tempImagePathForUpload)) {
            @unlink($tempThumbnailPathForUpload);
        }
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