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

/**
 * PositionService is responsible for managing the position of entities in a list.
 * It allows moving entities up, down, to the top, or to the bottom of the list.
 */
class PositionService
{
    /**
     * PositionService constructor.
     *
     * Initializes the service with the necessary dependencies.
     *
     * @param EntityManagerInterface $entityManager The Doctrine entity manager.
     * @param RequestStack $requestStack The Symfony request stack for handling requests.
     * @param EntityListener $entityListener The entity listener for managing entity methods.
     * @param AdminUrlGenerator $adminUrlGenerator The admin URL generator for generating URLs.
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RequestStack $requestStack, 
        private readonly EntityListener $entityListener, 
        private readonly AdminUrlGenerator $adminUrlGenerator
    ) {   
    }
   
    /**
     * Moves an entity to a new position based on the specified direction.
     * 
     * The direction of movement is determined by the `$direction` parameter.
     * This method dynamically determines the getter and setter for the entity's
     * position property using `EntityListener::getMethodNames()`.
     * After updating the position, it flushes the changes to the database.
     * 
     * It returns an array containing a success status, a message, and a redirect URL
     * pointing back to the index page of the current CRUD controller.
     *
     * @param AdminContext $context The admin context containing the entity to move.
     * @param Direction $direction The direction to move the entity (Top, Up, Down, Bottom).
     * @return array An array containing the success status, message, and redirect URL.
     */
    public function move(AdminContext $context, Direction $direction): array
    {
        //Get the entity instance from the context
        $object = $context->getEntity()->getInstance();
        // Dynamically get the getter and setter method names for the 'position' property.
        $positionMethods = $this->entityListener->getMethodNames($object, 'position');
        $getter = $positionMethods['get'];
        $setter = $positionMethods['set'];

        // Determine the new position and success message based on the specified direction.
        $currentPosition = $object->$getter();
        $message = '';
        $newPosition = $currentPosition;
        
        // Determine the new position and success message based on the specified direction.
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
        
        // Update & persist the position of the current object
        $object->$setter($newPosition);
        $this->entityManager->persist($object);
        $this->entityManager->flush();
        
        // Generate the URL to redirect back to the index page of the current CRUD controller.
        $redirectUrl = $this->adminUrlGenerator
            ->setDashboard(DashboardController::class)
            ->setController($context->getCrud()->getControllerFqcn())
            ->setAction(Action::INDEX) 
            ->generateUrl(); 

        // Ensure that even if the sorting logic didn't result in an actual move,
        // you still generate a valid URL to redirect back to the list.
        if (null === $redirectUrl) {
            error_log('AdminUrlGenerator::generateUrl returned null in PositionService!');
        }

        // Return an array with the success status, message, and redirect URL.
        return [
            'success' => true,
            'message' => $message,
            'redirect_url' => $redirectUrl
        ];
    }
}