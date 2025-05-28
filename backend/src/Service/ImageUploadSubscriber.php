<?php
namespace App\Service;

use App\Entity\Artist;
use App\Entity\LocationType;
use App\Entity\Partner;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Imagine\Gd\Imagine;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManagerInterface;
#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preUpdate)]

class ImageUploadSubscriber
{
    private $imagine;
    private $entityManager;

    public function __construct(
        private LoggerInterface $logger,
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir, EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
        $this->imagine = new Imagine();
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $this->imageResize($args);
    }

    public function preUpdate(LifecycleEventArgs $args): void
    {
        $this->imageResize($args);
    }

    private function imageResize(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof Artist) {
            if (empty($entity->getThumbnail())) {
                if (!empty($entity->getImageArtist())) {
                    // Determine the base path according to the environment
                    $basePath = str_contains($this->projectDir, 'public_html/symfony')
                        ? dirname($this->projectDir) . '/admin/uploads/artists'
                        : $this->projectDir . '/public/uploads/artists';
                    // Retrieve the path of the original image
                    $originalImagePath = $basePath . '/' . $entity->getImageArtist();
                    $thumbnailPath = $basePath . '/thumb_' . $entity->getImageArtist();

                    // Copy the original image to the thumbnail path
                    if (file_exists($originalImagePath)) {
                        if (copy($originalImagePath, $thumbnailPath)) {
                            $entity->setThumbnail('thumb_' . $entity->getImageArtist());
                            $this->logger->info('Thumbnail created successfully', [
                                'entity' => $entity,
                                'path' => $thumbnailPath
                            ]);
                        } else {
                            $this->logger->error('Failed to create thumbnail', [
                                'entity' => $entity,
                                'path' => $thumbnailPath
                            ]);
                        }
                    } else {
                        $this->logger->warning('Original image does not exist', [
                            'entity' => $entity,
                            'path' => $originalImagePath
                        ]);
                    }
                } else {
                    $this->logger->warning('Thumbnail cannot be set because image is empty', [
                        'entity' => $entity
                    ]);
                    return;
                }
            } 
            $this->resizeAndSaveImage($entity, 'ImageArtist', 'artists', 'webp', 768);
            $this->resizeAndSaveImage($entity, 'Thumbnail', 'artists', 'webp', 248);
        }

        if ($entity instanceof Partner) {
            $this->resizeAndSaveImage($entity, 'ImagePartner', 'partners', 'webp', 128);
        }

        if ($entity instanceof LocationType) {
            $this->resizeAndSaveImage($entity, 'Symbol', 'locations', 'png', 24);
        }
    }

    public function resizeAndSaveImage ($entity, string $field, string $folder, string $format, int $height): void
    {
        $imageGetter = 'get' . $field;
        $imageSetter = 'set' . $field;
        $image = $entity->$imageGetter();
        try {
            $this->entityManager->getConnection()->executeQuery('SET @TRIGGER_DISABLED = TRUE');

            // Determine the base path according to the environment
            $basePath = str_contains($this->projectDir, 'public_html/symfony') 
            ? dirname($this->projectDir) . '/admin/uploads'
            : $this->projectDir . '/public/uploads';
                    
            $path = $basePath . '/'. $folder . '/'. $image;

            if (!file_exists($path)) {
                $this->logger->warning('Image file does not exist', [
                    'path' => $path,
                    'entity' => $entity
                ]);
                return;
            }

            $image = $this->imagine->open($path);
            $newPath = preg_replace('/\.[^.]+$/', '.' . $format, $path);
            $newFileName = pathinfo($newPath, PATHINFO_BASENAME);
            $image->resize($image->getSize()->heighten($height))
                        ->save($newPath, [
                            'quality' => '75',
                            'format' => $format
                        ]);

            if ($path !== $newPath) {
                unlink($path);
            }
            
            $entity->$imageSetter($newFileName);
            $this->logger->info('Image resized successfully', [
                'path' => $path,
                'entity' => $entity,
                'size' => '200x200',
                'quality' => '75'
            ]);

        } catch (\Exception $error) {
            $this->logger->error('Error resizing image', [
                'error' => $error->getMessage(),
                'trace' => $error->getTraceAsString(),
                'entity' => $entity
            ]);
        
        $this->entityManager->getConnection()->executeQuery('SET @TRIGGER_DISABLED = FALSE');        
        }
    }
}