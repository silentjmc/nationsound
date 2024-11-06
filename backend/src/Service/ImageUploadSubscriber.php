<?php
namespace App\Service;

use App\Entity\Artist;
use App\Entity\LocationType;
use App\Entity\Partners;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
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
        //$entityClass = get_class($entity);

        if ($entity instanceof Artist) {
            if (empty($entity->getThumbnail())) {
                if (!empty($entity->getImage())) {
                    // Récupérer le chemin de l'image originale
                    $originalImagePath = $this->projectDir . '/public/uploads/artists/image/' . $entity->getImage();
                    $thumbnailPath = $this->projectDir . '/public/uploads/artists/thumbnail/th_' . $entity->getImage();

                    // Copier l'image originale vers le chemin du thumbnail
                    if (file_exists($originalImagePath)) {
                        if (copy($originalImagePath, $thumbnailPath)) {
                            $entity->setThumbnail('th_' . $entity->getImage());
                            $this->logger->info('Thumbnail created successfully', [
                                'entity' => $entity
                            ]);
                        } else {
                            $this->logger->error('Failed to create thumbnail', [
                                'entity' => $entity
                            ]);
                        }
                    } else {
                        $this->logger->warning('Original image does not exist', [
                            'entity' => $entity
                        ]);
                    }
                } else {
                    $this->logger->warning('Thumbnail cannot be set because image is empty', [
                        'entity' => $entity
                    ]);
                    return; // Sortir si l'image est vide
                }
            }

            $this->resizeAndSaveImage($entity, 'Image', 'image', 'webp', 768);
            $this->resizeAndSaveImage($entity, 'Thumbnail', 'thumbnail', 'webp', 248);

        }

        if ($entity instanceof Partners) {
            $this->resizeAndSaveImage($entity, 'Image', 'partners', 'webp', 128);
        }

        if ($entity instanceof LocationType) {
            $this->resizeAndSaveImage($entity, 'Symbol', 'location', 'png', 24);
        }
   
    }

    public function resizeAndSaveImage ($entity, string $field, string $folder, string $format, int $height): void
    {
        $imageGetter = 'get' . $field;
        $imageSetter = 'set' . $field;
        $image = $entity->$imageGetter();
        try {
            $this->entityManager->getConnection()->executeQuery('SET @TRIGGER_DISABLED = TRUE');

            $path = $this->projectDir . '/public/uploads/'. $folder . '/'. $image;

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

        } catch (\Exception $e) {
            $this->logger->error('Error resizing image', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'entity' => $entity
            ]);
        
        $this->entityManager->getConnection()->executeQuery('SET @TRIGGER_DISABLED = FALSE');        
        }
    }
}