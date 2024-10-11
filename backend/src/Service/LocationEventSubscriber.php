<?php
namespace App\Service;

use App\Controller\Admin\LocationCrudController;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeCrudActionEvent;
use App\Entity\Location;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LocationEventSubscriber implements EventSubscriberInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            BeforeCrudActionEvent::class => 'onBeforeCrudAction',
        ];
    }
   
    public function onBeforeCrudAction(BeforeCrudActionEvent $event)
    {
        $adminContext = $event->getAdminContext();
        if ($adminContext->getCrud()->getControllerFqcn() === LocationCrudController::class) {
            //$repository = $this->entityManager->getRepository(Location::class);
            //$locations = $repository->findAll();
            $locations = $this->entityManager->getRepository(Location::class)->findAll();
            $adminContext->getRequest()->attributes->set('locations', $locations);

            $currentEntity = $adminContext->getEntity()->getInstance();
            if ($currentEntity) {
                $adminContext->getRequest()->attributes->set('currentEntity', $currentEntity);
            }
        }
    }
 }
