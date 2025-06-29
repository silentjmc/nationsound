<?php
namespace App\Service;

use App\Entity\Artist;
use App\Entity\LocationType;
use App\Entity\Partner;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * EntityListener is a Doctrine event listener that handles pre-persist, pre-update, and pre-remove events for entities.
 *
 * This listener automatically sets modification date and user information
 * on entities before they are persisted or updated. It also handles the deletion
 * of associated image files for specific entities before they are removed.
 *
 * It listens to the following Doctrine events:
 * - prePersist: Before an entity is first saved to the database.
 * - preUpdate: Before an existing entity is updated in the database.
 * - preRemove: Before an entity is removed from the database.
 */
#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
#[AsDoctrineListener(event: Events::preRemove)]
class EntityListener
{
    private Security $security;
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;
    private Filesystem $filesystem;

    /**
     * EntityListener constructor.
     *
     * Initializes the listener with the necessary services.
     *
     * @param string $projectDir The project's root directory, autowired.
     * @param Security $security Symfony's security component to get the current user.
     * @param EntityManagerInterface $entityManager The Doctrine entity manager.
     * @param LoggerInterface $logger The logger service for logging events.
     * @param Filesystem $filesystem The filesystem service for file operations.
     */
    public function __construct(#[Autowire('%kernel.project_dir%')] private string $projectDir, Security $security, EntityManagerInterface $entityManager, LoggerInterface $logger, Filesystem $filesystem)
    {
        $this->security = $security;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->filesystem = $filesystem;
    }

    /**
     * Generates method names for setting and getting modification date and user.
     *
     * This method constructs the method names based on the entity's class name
     * and the property name provided. It assumes that the methods follow a
     * standard naming convention: set<PropertyName><EntityName> and get<PropertyName><EntityName>.
     *
     * @param object $entity The entity instance.
     * @param string $propertyName The property name to generate methods for.
     * @return array An associative array containing 'set' and 'get' method names.
     */
    public function getMethodNames(object $entity, string $propertyName): array
    {
        $entityName = (new \ReflectionClass($entity))->getShortName();
        $formattedPropertyName = ucfirst($propertyName);
        
       return [
            'set' => 'set' . $formattedPropertyName . $entityName,
            'get' => 'get' . $formattedPropertyName . $entityName
        ];
    }

    /**
     * Pre-persist event handler.
     *
     * This method is called before an entity is persisted to the database.
     * It sets the modification date and user information on the entity.
     *
     * @param LifecycleEventArgs $args The event arguments containing the entity being persisted.
     */
    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        // Get the method names for date and user modification
        $dateModificationMethods = $this->getMethodNames($entity, 'dateModification');
        $userModificationMethods = $this->getMethodNames($entity, 'userModification');

        $this->logger->info('prePersist called for entity: ' . get_class($entity));

        // Check if the methods exist before calling them
        if (method_exists($entity, $dateModificationMethods['set']) && method_exists($entity, $userModificationMethods['set'])) {
            $entity->{$dateModificationMethods['set']}(new \DateTime());    // Set the current date and time
            
            // Get the current user from the security context
            $user = $this->security->getUser();
            if ($user instanceof User) {
                // If the user is authenticated, set their full name
                $entity->{$userModificationMethods['set']}($user->getFullName());
            } elseif ($_ENV['APP_ENV'] === 'test') { // If in test environment, set a default user name
            $entity->{$userModificationMethods['set']}('test_fixture_loader');
            }
        }
    }

    /*
     * Pre-update event handler.
     *
     * This method is called before an existing entity is updated in the database.
     * It updates the modification date and user information on the entity.
     *
     * @param PreUpdateEventArgs $args The event arguments containing the entity being updated.
     */
    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        // Get the method names for date and user modification
        $dateModificationMethods = $this->getMethodNames($entity, 'dateModification');
        $userModificationMethods = $this->getMethodNames($entity, 'userModification');
        $this->logger->info('preUpdate called for entity: ' . get_class($entity));

        // Check if the methods exist before calling them
        if (method_exists($entity, $dateModificationMethods['set']) && method_exists($entity, $userModificationMethods['set'])) {
            $entity->{$dateModificationMethods['set']}(new \DateTime());
            
            // Get the current user from the security context
            $user = $this->security->getUser();
            if ($user instanceof User) {
                // If the user is authenticated, set their full name
                $entity->{$userModificationMethods['set']}($user->getFullName());
            }
        }
    }

    /**
     * Pre-remove event handler.
     *
     * This method is called before an entity is removed from the database.
     * It sets the current user name for the entity and deletes associated images
     * for specific entity types.
     *
     * @param LifecycleEventArgs $args The event arguments containing the entity being removed.
     */
    public function preRemove(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        $userModificationMethods = $this->getMethodNames($entity, 'userModification');
        
        // Check if the method for setting user modification exists
        if (method_exists($entity, $userModificationMethods['set'])) {
            $user = $this->security->getUser();
            $currentUserName = 'unknown';
            if ($user instanceof User) {
                // If the user is authenticated, set their full name
                $currentUserName = $user->getFullName();
                $entity->{$userModificationMethods['set']}($currentUserName);
                //$currentUserName =  $entity->{$userModificationMethods['set']}($user->getFullName());
            } else {
                // If no user is authenticated, set a default name
                //$currentUserName = 'unknown';
                $entity->{$userModificationMethods['set']}($currentUserName);
            }
            $connection = $this->entityManager->getConnection();
            $connection->executeQuery("SET @current_user_name = :name", ['name' => $currentUserName]);
        }

        // Handle image deletion for specific entity types
        if ($entity instanceof Artist) {
            $this->deleteImage($entity, 'ImageArtist', 'artists');
            $this->deleteImage($entity, 'Thumbnail', 'artists');
        }

        if ($entity instanceof Partner) {
            $this->deleteImage($entity, 'ImagePartner', 'partners');
        }

        if ($entity instanceof LocationType) {
            $this->deleteImage($entity, 'Symbol', 'locations');
        }
    }

    /**
     * Deletes an image associated with an entity.
     *
     * This method constructs the file path for the image based on the entity's field
     * and folder, and then attempts to remove the file using the Filesystem service.
     * If an error occurs during deletion, it logs the error message and stack trace.
     *
     * @param object $entity The entity instance containing the image field.
     * @param string $field The name of the field containing the image filename.
     * @param string $folder The folder where the image is stored.
     */
    public function deleteImage($entity, string $field, string $folder){
        $imageGetter = 'get' . $field;
        $image = $entity->$imageGetter();
        try {
            $path = $this->projectDir . '/public/uploads/'. $folder . '/'. $image;
            $this->filesystem->remove($path);
        } catch (\Exception $error) {
            $this->logger->error('Error resizing image', [
                'error' => $error->getMessage(),
                'trace' => $error->getTraceAsString(),
                'entity' => $entity
            ]);
        }
    }
}