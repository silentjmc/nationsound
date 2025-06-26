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

/**
 * ImageUploadSubscriber is a Doctrine event subscriber that handles image uploads and resizing for specific entities.
 * It listens to pre-persist and pre-update events to ensure that images are resized and saved correctly.
 */
class ImageUploadSubscriber
{
    private $imagine;
    private $entityManager;

    /**
     * ImageUploadSubscriber constructor.
     *
     * Initializes the subscriber with the necessary services.
     *
     * @param LoggerInterface $logger The logger service for logging events.
     * @param string $projectDir The project's root directory, autowired.
     * @param EntityManagerInterface $entityManager The Doctrine entity manager.
     */
    public function __construct(
        private LoggerInterface $logger,
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir, EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
        $this->imagine = new Imagine();
    }

    /**
     * Handles the pre-persist event for entities.
     * This method is called before an entity is first saved to the database.
     * It triggers the image resizing process for the entity.
     *
     * @param LifecycleEventArgs $args The event arguments containing the entity being persisted.
     */
    public function prePersist(LifecycleEventArgs $args): void
    {
        $this->imageResize($args);
    }

    /**
     * Handles the pre-update event for entities.
     *
     * This method is called before an entity is updated in the database.
     * It triggers the image resizing process for the entity.
     *
     * @param LifecycleEventArgs $args The event arguments containing the entity being updated.
     */
    public function preUpdate(LifecycleEventArgs $args): void
    {
        $this->imageResize($args);
    }

    /**
     * Resizes and saves images for specific entities.
     *
     * This method checks the type of the entity and performs image resizing
     * and saving operations based on the entity type (Artist, Partner, LocationType).
     * It also handles thumbnail creation for Artist entities if necessary.
     *
     * @param LifecycleEventArgs $args The event arguments containing the entity being processed.
     */
    private function imageResize(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        // Check if the entity is an instance of Artist, Partner, or LocationType
        if ($entity instanceof Artist) {
            // If the entity is an Artist and has no thumbnail set, create one from the original image
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
            // Resize and save the artist image and thumbnail
            $this->resizeAndSaveImage($entity, 'ImageArtist', 'artists', 'webp', 768);
            $this->resizeAndSaveImage($entity, 'Thumbnail', 'artists', 'webp', 248);
        }

        if ($entity instanceof Partner) {
            // Resize and save the partner image
            $this->resizeAndSaveImage($entity, 'ImagePartner', 'partners', 'webp', 128);
        }

        if ($entity instanceof LocationType) {
            // Resize and save the location type symbol
            $this->resizeAndSaveImage($entity, 'Symbol', 'locations', 'png', 24);
        }
    }

    /**
     * Resizes and saves an image for a given entity and field.
     *
     * This method opens the image file, resizes it to the specified height,
     * saves it in the specified format, and updates the entity with the new filename.
     * It also handles logging and error management.
     *
     * @param object $entity The entity instance containing the image field.
     * @param string $field The name of the field containing the image filename.
     * @param string $folder The folder where the image is stored.
     * @param string $format The format to save the resized image (e.g., 'webp', 'png').
     * @param int $height The height to resize the image to.
     */
    public function resizeAndSaveImage ($entity, string $field, string $folder, string $format, int $height): void
    {
        // Construct the method names for getting and setting the image field
        $imageGetter = 'get' . $field;
        $imageSetter = 'set' . $field;
        $image = $entity->$imageGetter();
        
        try {
            // Disable triggers to prevent unwanted side effects during image processing
            $this->entityManager->getConnection()->executeQuery('SET @TRIGGER_DISABLED = TRUE');

            // Determine the base path according to the environment
            $basePath = str_contains($this->projectDir, 'public_html/symfony') 
            ? dirname($this->projectDir) . '/admin/uploads'
            : $this->projectDir . '/public/uploads';
                    
            $path = $basePath . '/'. $folder . '/'. $image;

            // Check if the image file exists before processing
            if (!file_exists($path)) {
                $this->logger->warning('Image file does not exist', [
                    'path' => $path,
                    'entity' => $entity
                ]);
                return;
            }
            // Open the image file using Imagine
            $image = $this->imagine->open($path);

            // Construct the new path for the resized image
            $newPath = preg_replace('/\.[^.]+$/', '.' . $format, $path);
            $newFileName = pathinfo($newPath, PATHINFO_BASENAME);

            // Resize the image to the specified height while maintaining aspect ratio
            $image->resize($image->getSize()->heighten($height))
                        ->save($newPath, [
                            'quality' => '75',
                            'format' => $format
                        ]);

            // If the new path is different from the original, delete the old file
            if ($path !== $newPath) {
                unlink($path);
            }
            
            // Update the entity with the new filename
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

        // Re-enable triggers after processing
        $this->entityManager->getConnection()->executeQuery('SET @TRIGGER_DISABLED = FALSE');        
        }
    }
}