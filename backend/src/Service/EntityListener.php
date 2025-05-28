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

#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
#[AsDoctrineListener(event: Events::preRemove)]
class EntityListener
{
    private Security $security;
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;
    private Filesystem $filesystem;

    public function __construct(#[Autowire('%kernel.project_dir%')] private string $projectDir, Security $security, EntityManagerInterface $entityManager, LoggerInterface $logger, Filesystem $filesystem)
    {
        $this->security = $security;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->filesystem = $filesystem;
    }

    // Generates method names based on the entity's class name.
     private function getMethodNames(object $entity): array
    {
        $entityName = (new \ReflectionClass($entity))->getShortName();
        
        return [
            'dateModification' => 'setDateModification' . $entityName,
            'userModification' => 'setUserModification' . $entityName
        ];
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        $methods = $this->getMethodNames($entity);

        $this->logger->info('prePersist called for entity: ' . get_class($entity));

        if (method_exists($entity, $methods['dateModification']) && method_exists($entity, $methods['userModification'])) {
            $entity->{$methods['dateModification']}(new \DateTime());
            
            $user = $this->security->getUser();
            if ($user instanceof User) {
                $entity->{$methods['userModification']}($user->getFullName());
            }
        }
    }

        public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        $methods = $this->getMethodNames($entity);
        $this->logger->info('preUpdate called for entity: ' . get_class($entity));

        if (method_exists($entity, $methods['dateModification']) && method_exists($entity, $methods['userModification'])) {
            $entity->{$methods['dateModification']}(new \DateTime());
            
            $user = $this->security->getUser();
            if ($user instanceof User) {
                $entity->{$methods['userModification']}($user->getFullName());
            }
        }
    }

    public function preRemove(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        $methods = $this->getMethodNames($entity);
        if (method_exists($entity, $methods['userModification'])) {
            $user = $this->security->getUser();
            if ($user instanceof User) {
                $currentUserName =  $entity->{$methods['userModification']}($user->getFullName());
            } else {
                $currentUserName = 'unknown';
            }
            $connection = $this->entityManager->getConnection();
            $connection->executeQuery("SET @current_user_name = :name", ['name' => $currentUserName]);
        }

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