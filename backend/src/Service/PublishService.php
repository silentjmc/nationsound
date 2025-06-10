<?php
namespace App\Service;

use App\Entity\Event;
use App\Entity\EventLocation;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\EntityListener;

class PublishService
{
    private EntityManagerInterface $entityManager;
    private AdminUrlGenerator $adminUrlGenerator;
    private EntityListener $entityListener;
    
    public function __construct(EntityManagerInterface $entityManager, AdminUrlGenerator $adminUrlGenerator, EntityListener $entityListener) 
    {
        $this->entityManager = $entityManager;
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->entityListener = $entityListener;
    }

    public function publish(AdminContext $context): array
    {
        $object = $context->getEntity()->getInstance();
        $publishMethods = $this->entityListener->getMethodNames($object, 'publish');
        $setter = $publishMethods['set'];
        $hasRelatedItems = false;
        if ($object instanceof Event) {
            $hasRelatedItems = $this->entityManager->getRepository(EventLocation::class)
                ->count(['idEventLocation' => $object->getEventLocation()->getIdEventLocation(), 'publishEventLocation' => false]) > 0;
        }
        if ($hasRelatedItems) {
            $eventLocations = $this->entityManager->getRepository(EventLocation::class)
                ->findBy(['idEventLocation' => $object->getEventLocation()->getIdEventLocation(), 'publishEventLocation' => false]);
            foreach ($eventLocations as $eventLocation) {
                $eventLocation->setPublishEventLocation(true);
                $this->entityManager->persist($eventLocation);
            }
        }

        $object->$setter(true);
        $this->entityManager->persist($object);
        $this->entityManager->flush();

        $url = $this->adminUrlGenerator
        ->setAction(Action::INDEX)
        ->removeReferrer()
        ->setController($context->getCrud()?->getControllerFqcn() ?? '')
        ->generateUrl();
        return ['url' => $url, 'hasRelatedItems' => $hasRelatedItems];
    }

    public function unpublish(AdminContext $context): array
    {
        $object = $context->getEntity()->getInstance();
        $unpublishMethods = $this->entityListener->getMethodNames($object, 'publish');
        $setter = $unpublishMethods['set'];
        $hasRelatedItems = false;

        if ($object instanceof EventLocation) {
            $hasRelatedItems = $this->entityManager->getRepository(Event::class)
                 ->count(['eventLocation' => $object, 'publishEvent' => true]) > 0;
        }
        if ($hasRelatedItems) {
            $events = $this->entityManager->getRepository(Event::class)
                ->findBy(['eventLocation' => $object]);
            foreach ($events as $event) {
                $event->setPublishEvent(false);
                $this->entityManager->persist($event);
            }
        }
        $object->$setter(false);
        $this->entityManager->persist($object);
        $this->entityManager->flush();

        $url = $this->adminUrlGenerator
        ->setAction(Action::INDEX)
        ->removeReferrer()
        ->setController($context->getCrud()?->getControllerFqcn() ?? '')
        ->generateUrl();
        return ['url' => $url, 'hasRelatedItems' => $hasRelatedItems];
   }
}