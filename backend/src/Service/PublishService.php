<?php
namespace App\Service;

use App\Entity\Event;
use App\Entity\EventLocation;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class PublishService
{
    private EntityManagerInterface $entityManager;
    private AdminUrlGenerator $adminUrlGenerator;
    
    public function __construct(EntityManagerInterface $entityManager, AdminUrlGenerator $adminUrlGenerator) 
    {
        $this->entityManager = $entityManager;
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    private function getMethodNames(object $entity): array
    {
        $entityName = (new \ReflectionClass($entity))->getShortName();
        
        return [
            'setPublish' => 'setPublish' . $entityName,
            'getPublish' => 'getPublish' . $entityName,
        ];
    }

    public function publish(AdminContext $context): array
    {
        $object = $context->getEntity()->getInstance();
        $methods = $this->getMethodNames($object);
        $setter = $methods['setPublish'];
        $hasRelatedItems = false;
        if ($object instanceof Event) {
            $hasRelatedItems = $this->entityManager->getRepository(EventLocation::class)
                ->count(['idEventLocation' => $object->getEventLocation()->getIdEventLocation(), 'publishEventLocation' => false]) > 0;
        }
        if ($hasRelatedItems) {
            $eventLocations = $this->entityManager->getRepository(EventLocation::class)
                ->findBy(['idEventLocation' => $object->getEventLocation()->getIdEventLocation(), 'publishEventLocation' => false]);
            foreach ($eventLocations as $eventLocation) {
                $eventLocation->$setter(true);
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
        $methods = $this->getMethodNames($object);
        $setter = $methods['setPublish'];
        $hasRelatedItems = false;

        if ($object instanceof EventLocation) {
            $hasRelatedItems = $this->entityManager->getRepository(Event::class)
                 ->count(['eventLocation' => $object, 'publishEvent' => true]) > 0;
        }
        if ($hasRelatedItems) {
            $events = $this->entityManager->getRepository(Event::class)
                ->findBy(['eventLocation' => $object]);
            foreach ($events as $event) {
                $event->$setter(false);
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