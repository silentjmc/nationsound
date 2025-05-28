<?php
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use Symfony\Component\HttpFoundation\RequestStack; //rajout

enum Direction
{
    case Top;
    case Up;
    case Down;
    case Bottom;
}

class PositionService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RequestStack $requestStack //rajout
    ) {
    }

    // Generates method names based on the entity's class name.
    private function getMethodNames(object $entity): array
    {
        $entityName = (new \ReflectionClass($entity))->getShortName();
        
        return [
            'setPosition' => 'setPosition' . $entityName,
            'getPosition' => 'getPosition' . $entityName,
        ];
    }
   
    // Moves the entity to a new position based on the specified direction.
    public function move(AdminContext $context, Direction $direction): array
    {
        $object = $context->getEntity()->getInstance();
        $methods = $this->getMethodNames($object);
        $getter = $methods['getPosition'];
        $setter = $methods['setPosition'];

        $currentPosition = $object->$getter();
        $message = '';
        $newPosition = $currentPosition;
        
        switch ($direction) {
            case Direction::Top:
                $newPosition = 0;
                $message = 'l\'élément a bien été déplacé en haut de page.';  
                break;
                
            case Direction::Up:
                $newPosition = $currentPosition - 1;
                $message = 'l\'élément a bien été déplacé d\'un cran en haut.';
                break;
                
            case Direction::Down:
                $newPosition = $currentPosition + 1;
                $message = 'l\'élément a bien été déplacé d\'un cran en bas.';
                break;
                
            case Direction::Bottom:
                $newPosition = -1;
                $message = 'l\'élément a bien été déplacé en bas de page.';
                break;
        }
        
        // Update the position of the current object

        $object->$setter($newPosition);
        $this->entityManager->persist($object);
        $this->entityManager->flush();
        
        return [
            'success' => true,
            'message' => $message,
            'redirect_url' => $this->requestStack->getCurrentRequest()->headers->get('referer')
        ];
    }
}