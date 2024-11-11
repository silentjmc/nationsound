<?php
namespace App\Service;

//use App\Entity\Artist;

use App\Entity\Artist;
use App\Entity\Event;
use App\Entity\EventLocation;
use App\Entity\LocationType;
use App\Entity\Partners;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\SessionFactory;

//use Symfony\Component\HttpFoundation\File\UploadedFile;

#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
//#[AsDoctrineListener(event: Events::postUpdate)]
#[AsDoctrineListener(event: Events::preRemove)]
class EntityListener
{
    private Security $security;
    private EntityManagerInterface $entityManager;
    private array $changedEntities = [];
    private LoggerInterface $logger;
    private Filesystem $filesystem;
    private RequestStack $requestStack;
    private MessageService $messageService;

    public function __construct(#[Autowire('%kernel.project_dir%')] private string $projectDir, Security $security, EntityManagerInterface $entityManager, LoggerInterface $logger, Filesystem $filesystem, MessageService $messageService, RequestStack $requestStack)
    {
        $this->security = $security;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->filesystem = $filesystem;
        $this->messageService = $messageService;
        $this->requestStack = $requestStack;
    
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
/*
        if ($entity instanceof EventLocation && $args->hasChangedField('publish') && $args->getNewValue('publish') === false) {
            $this->changedEntities[spl_object_hash($entity)] = true;
        }*/
/*
        if ($entity instanceof Event && $args->hasChangedField('publish') && $args->getNewValue('publish') === true) {
            $this->changedEntities[spl_object_hash($entity)] = true;
        }*/
    }

   /* public function postUpdate(PostUpdateEventArgs $args): void
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

            //$this->messageService->addMessage('info', 'Les événements associés à ce lieu ont été dépubliés.');
            $this->addFlash('info', 'Les événements associés à ce lieu ont été dépubliés.');
            //return $this->redirectToRoute('easyadmin_index');
        }

        if ($entity instanceof Event && isset($this->changedEntities[spl_object_hash($entity)])) {
            unset($this->changedEntities[spl_object_hash($entity)]);
            
            $eventsLocations = $this->entityManager->getRepository(EventLocation::class)->findBy(['event' => $entity, 'publish'=>false]);
            foreach ($eventsLocations as $eventLocation) {
                $entity->setPublish(false);
            }
            $this->entityManager->flush();

            $this->messageService->addMessage('info', "Le lieu associé a cet évènement n'est pas publié");
        
        }
    }*/

        
    

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

        if ($entity instanceof Artist) {
            $this->deleteImage($entity, 'Image', 'artists');
            $this->deleteImage($entity, 'Thumbnail', 'artists');

        }

        if ($entity instanceof Partners) {
            $this->deleteImage($entity, 'Image', 'partners');
        }

        if ($entity instanceof LocationType) {
            $this->deleteImage($entity, 'Symbol', 'location');
        }
    }

    public function deleteImage($entity, string $field, string $folder){
        $imageGetter = 'get' . $field;
        $image = $entity->$imageGetter();
        try {
            $path = $this->projectDir . '/public/uploads/'. $folder . '/'. $image;
            $this->filesystem->remove($path);
        } catch (\Exception $e) {
            $this->logger->error('Error resizing image', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'entity' => $entity
            ]);
        }
    }
}