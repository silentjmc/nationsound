<?php
namespace App\Service;

use App\Controller\Admin\DashboardController;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Service\EntityListener;
use App\Service\Direction;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

/*
enum Direction
{
    case Top;
    case Up;
    case Down;
    case Bottom;
}
*/
class PositionService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RequestStack $requestStack, 
        private readonly EntityListener $entityListener, 
        private readonly AdminUrlGenerator $adminUrlGenerator
    ) {   
    }
   
    // Moves the entity to a new position based on the specified direction.
    public function move(AdminContext $context, Direction $direction): array
    {
        $object = $context->getEntity()->getInstance();
        $positionMethods = $this->entityListener->getMethodNames($object, 'position');
        $getter = $positionMethods['get'];
        $setter = $positionMethods['set'];

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
        
        /*return [
            'success' => true,
            'message' => $message,
            'redirect_url' => $this->requestStack->getCurrentRequest()->headers->get('referer')
        ];*/
        $redirectUrl = $this->adminUrlGenerator
            ->setDashboard(DashboardController::class) // <--- CRITICAL LINE TO CHECK
            ->setController($context->getCrud()->getControllerFqcn()) // This should be FaqCrudController::class
            ->setAction(Action::INDEX) // Redirect to the index page of the current CRUD
            ->generateUrl(); // <--- THIS is returning null!

        // Ensure that even if the sorting logic didn't result in an actual move,
        // you still generate a valid URL to redirect back to the list.
        if (null === $redirectUrl) {
            error_log('AdminUrlGenerator::generateUrl returned null in PositionService!');
        }

        return [
            'success' => true, // Or based on your actual logic outcome
            'message' => $message, // Or dynamically generated
            'redirect_url' => $redirectUrl
        ];
    }
}