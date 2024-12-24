<?php
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;

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
        private readonly EntityManagerInterface $entityManager
    ) {
    }
    public function move(AdminContext $context, Direction $direction)
    {
        $object = $context->getEntity()->getInstance();
        $newPosition = match($direction) {
            Direction::Top => 0,
            Direction::Up => $object->getPosition() - 1,
            Direction::Down => $object->getPosition() + 1,
            Direction::Bottom => -1,
        };
    
        $object->setPosition($newPosition);
        $this->entityManager->flush();
    }
}