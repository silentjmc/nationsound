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

    public function publish(AdminContext $context): array
    {
        $entity = $context->getEntity()->getInstance();
        $hasRelatedItems = false;
        if ($entity instanceof Event) {
            $hasRelatedItems = $this->entityManager->getRepository(EventLocation::class)
                ->count(['id' => $entity->getEventLocation()->getId(), 'publish' => false]) > 0;
        }
        if ($hasRelatedItems) {
            $eventLocations = $this->entityManager->getRepository(EventLocation::class)
                ->findBy(['id' => $entity->getEventLocation()->getId(), 'publish' => false]);
            foreach ($eventLocations as $eventLocation) {
                $eventLocation->setPublish(true);
                $this->entityManager->persist($eventLocation);
            }
        }

        $entity->setPublish(true);
        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $url = $this->adminUrlGenerator
        ->setAction(Action::INDEX)
        ->removeReferrer()
        ->setController($context->getCrud()?->getControllerFqcn() ?? '')
        ->generateUrl();
        return ['url' => $url, 'hasRelatedItems' => $hasRelatedItems];
    }
    /*
    public function publish(AdminContext $context): string
    {
        $entity = $context->getEntity()->getInstance();
        $entity->setPublish(true);
        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $url = $this->adminUrlGenerator
        ->setAction(Action::INDEX)
        ->removeReferrer()
        ->setController($context->getCrud()?->getControllerFqcn() ?? '')
        ->generateUrl();
        return $url;
    }*/

    public function unpublish(AdminContext $context): array
    {
        $entity = $context->getEntity()->getInstance();
        $hasRelatedItems = false;

        if ($entity instanceof EventLocation) {
            $hasRelatedItems = $this->entityManager->getRepository(Event::class)
                 ->count(['eventLocation' => $entity, 'publish' => true]) > 0;
        }
        if ($hasRelatedItems) {
            $events = $this->entityManager->getRepository(Event::class)
                ->findBy(['eventLocation' => $entity]);
            foreach ($events as $event) {
                $event->setPublish(false);
                $this->entityManager->persist($event);
            }
        }
        $entity->setPublish(false);
        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $url = $this->adminUrlGenerator
        ->setAction(Action::INDEX)
        ->removeReferrer()
        ->setController($context->getCrud()?->getControllerFqcn() ?? '')
        ->generateUrl();
        return ['url' => $url, 'hasRelatedItems' => $hasRelatedItems];
   }
}