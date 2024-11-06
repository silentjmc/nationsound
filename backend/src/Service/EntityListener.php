<?php
namespace App\Service;

//use App\Entity\Artist;
use App\Entity\Event;
use App\Entity\EventLocation;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
//use Symfony\Component\HttpFoundation\File\UploadedFile;

#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
#[AsDoctrineListener(event: Events::postUpdate)]
#[AsDoctrineListener(event: Events::preRemove)]
class EntityListener
{
    private Security $security;
    private EntityManagerInterface $entityManager;
    private array $changedEntities = [];
    private LoggerInterface $logger;

    public function __construct( Security $security, EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->security = $security;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        $this->logger->info('prePersist called for entity: ' . get_class($entity));

        if (method_exists($entity, 'setDateModification') && method_exists($entity, 'setUserModification')) {
            $entity->setDateModification(new \DateTime());

            $user = $this->security->getUser();
            if ($user instanceof User) {
                $entity->setUserModification($user->getEmail());
            }
        }
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        $this->logger->info('preUpdate called for entity: ' . get_class($entity));

        if (method_exists($entity, 'setDateModification') && method_exists($entity, 'setUserModification')) {
            $entity->setDateModification(new \DateTime());

            $user = $this->security->getUser();
            if ($user instanceof User) {
                $entity->setUserModification($user->getEmail());
            }
        }

        if ($entity instanceof EventLocation && $args->hasChangedField('publish') && $args->getNewValue('publish') === false) {
            $this->changedEntities[spl_object_hash($entity)] = true;
        }
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $entity = $args->getObject();

        // Vérifiez si l'entité a été marquée comme ayant changé dans preUpdate
        if ($entity instanceof EventLocation && isset($this->changedEntities[spl_object_hash($entity)])) {
            unset($this->changedEntities[spl_object_hash($entity)]);

            $events = $this->entityManager->getRepository(Event::class)->findBy(['eventLocation' => $entity]);
            foreach ($events as $event) {
                $event->setPublish(false);
            }
            $this->entityManager->flush();
        }
    }

    public function preRemove(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (method_exists($entity, 'setUserModification')) {
            $user = $this->security->getUser();
            if ($user instanceof User) {
                $currentUserEmail = $user->getEmail();
            } else {
                $currentUserEmail = 'unknown';
            }
            $connection = $this->entityManager->getConnection();
            $connection->executeQuery("SET @current_user_email = :email", ['email' => $currentUserEmail]);
        }
    }
}