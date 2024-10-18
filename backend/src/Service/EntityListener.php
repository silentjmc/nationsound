<?php
namespace App\Service;

//use App\Entity\EntityHistory;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Bundle\SecurityBundle\Security;

#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
#[AsDoctrineListener(event: Events::preRemove)]
class EntityListener
{
    private Security $security;
    private EntityManagerInterface $entityManager;

    public function __construct( Security $security, EntityManagerInterface $entityManager)
    {
        $this->security = $security;
        $this->entityManager = $entityManager;
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

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

        if (method_exists($entity, 'setDateModification') && method_exists($entity, 'setUserModification')) {
            $entity->setDateModification(new \DateTime());
            $user = $this->security->getUser();
            if ($user instanceof User) {
                $entity->setUserModification($user->getEmail());
            }
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