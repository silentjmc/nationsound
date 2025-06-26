<?php
namespace App\Service;

use App\Entity\Event;
use App\Entity\EventLocation;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\EntityListener;

/**
 * PublishService is responsible for publishing and unpublishing entities.
 * It handles the logic for updating the publish status of entities and their related items.
 */
class PublishService
{
    private EntityManagerInterface $entityManager;
    private AdminUrlGenerator $adminUrlGenerator;
    private EntityListener $entityListener;
    
    /**
     * PublishService constructor.
     *
     * Initializes the service with the necessary dependencies.
     *
     * @param EntityManagerInterface $entityManager The Doctrine entity manager.
     * @param AdminUrlGenerator $adminUrlGenerator The admin URL generator for generating URLs.
     * @param EntityListener $entityListener The entity listener for managing entity methods.
     */
    public function __construct(EntityManagerInterface $entityManager, AdminUrlGenerator $adminUrlGenerator, EntityListener $entityListener) 
    {
        $this->entityManager = $entityManager;
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->entityListener = $entityListener;
    }

    /**
     * Publishes the entity in the given context.
     *
     * This method sets the entity's publish status to true.
     * If the entity is an `Event`, it also checks if its related `EventLocation` is unpublished.
     * If so, it publishes the related `EventLocation`(s) as well.
     *
     * @param AdminContext $context The admin context containing the entity to publish.
     * @return array An array containing the redirect URL and a flag indicating if there are related items.
     */
    public function publish(AdminContext $context): array
    {
        // Get the entity instance from the context
        $object = $context->getEntity()->getInstance();
        // Dynamically get the setter method names for the 'publish' property.
        $publishMethods = $this->entityListener->getMethodNames($object, 'publish');
        $setter = $publishMethods['set'];
        $hasRelatedItems = false;

        // Check if the object is an Event and if its related EventLocation is unpublished.
        if ($object instanceof Event) {
            $hasRelatedItems = $this->entityManager->getRepository(EventLocation::class)
                ->count(['idEventLocation' => $object->getEventLocation()->getIdEventLocation(), 'publishEventLocation' => false]) > 0;
        }

        // If there are related unpublished EventLocations, publish them.
        if ($hasRelatedItems) {
            $eventLocations = $this->entityManager->getRepository(EventLocation::class)
                ->findBy(['idEventLocation' => $object->getEventLocation()->getIdEventLocation(), 'publishEventLocation' => false]);
            foreach ($eventLocations as $eventLocation) {
                $eventLocation->setPublishEventLocation(true);
                $this->entityManager->persist($eventLocation);
            }
        }

        // Set the publish status of the object to true and persist the changes.
        $object->$setter(true);
        $this->entityManager->persist($object);
        $this->entityManager->flush();

        // Generate the URL for redirecting to the index page of the current CRUD controller.
        $url = $this->adminUrlGenerator
        ->setAction(Action::INDEX)
        ->removeReferrer()
        ->setController($context->getCrud()?->getControllerFqcn() ?? '')
        ->generateUrl();
        return ['url' => $url, 'hasRelatedItems' => $hasRelatedItems];
    }

    /**
     * Unpublishes the entity in the given context.
     *
     * This method sets the entity's publish status to false.
     * If the entity is an `EventLocation`, it also checks if there are related `Event` items that are published.
     * If so, it unpublishes those `Event` items as well.
     *
     * @param AdminContext $context The admin context containing the entity to unpublish.
     * @return array An array containing the redirect URL and a flag indicating if there are related items.
     */
    public function unpublish(AdminContext $context): array
    {
        // Get the entity instance from the context
        $object = $context->getEntity()->getInstance();
        // Dynamically get the setter method names for the 'publish' property.
        $unpublishMethods = $this->entityListener->getMethodNames($object, 'publish');
        $setter = $unpublishMethods['set'];
        $hasRelatedItems = false;

        // Check if the object is an EventLocation and if there are related published Events.
        if ($object instanceof EventLocation) {
            $hasRelatedItems = $this->entityManager->getRepository(Event::class)
                 ->count(['eventLocation' => $object, 'publishEvent' => true]) > 0;
        }
        // If there are related published Events, unpublish them.
        if ($hasRelatedItems) {
            $events = $this->entityManager->getRepository(Event::class)
                ->findBy(['eventLocation' => $object]);
            foreach ($events as $event) {
                $event->setPublishEvent(false);
                $this->entityManager->persist($event);
            }
        }
        // Set the publish status of the object to false and persist the changes.
        $object->$setter(false);
        $this->entityManager->persist($object);
        $this->entityManager->flush();

        // Generate the URL for redirecting to the index page of the current CRUD controller.
        $url = $this->adminUrlGenerator
        ->setAction(Action::INDEX)
        ->removeReferrer()
        ->setController($context->getCrud()?->getControllerFqcn() ?? '')
        ->generateUrl();
        return ['url' => $url, 'hasRelatedItems' => $hasRelatedItems];
   }
}