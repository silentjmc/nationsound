<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use App\Entity\EventDate;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

/**
 * EventDateCrudController is responsible for managing the CRUD operations for the EventDate entity.
 * It extends AbstractCrudController to leverage EasyAdmin's functionality.
 * 
 * This controller customizes the default CRUD operations for EventDate, including:
 * - Configuration of fields displayed in forms and index pages.
 * - Custom labels, titles, and templates.
 */
class EventDateCrudController extends AbstractCrudController
{
    private EntityManagerInterface $entityManager;
    private AdminUrlGenerator $adminUrlGenerator;

    /**
     * EventDateCrudController constructor.
     *
     * Initializes the controller with the necessary services. 
     * 
     * @param EntityManagerInterface $entityManager The Doctrine entity manager.
     * @param AdminUrlGenerator $adminUrlGenerator The EasyAdmin URL generator service.
     */
    public function __construct(EntityManagerInterface $entityManager, AdminUrlGenerator $adminUrlGenerator)
    {
        $this->entityManager = $entityManager;
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

   /**
     * Returns the fully qualified class name of the entity managed by this controller.
     *
     * This method is used by EasyAdmin to determine which entity this controller is responsible for.
     *
     * @return string The fully qualified class name of the EventDate entity.
     */
    public static function getEntityFqcn(): string
    {
        return EventDate::class;
    }

    /**
     * Configures the actions available in the CRUD interface.
     *
     * This method sets custom labels and icons for actions such as New, Save, Edit, and Delete
     *
     * @param Actions $actions The actions configuration object.
     * @return Actions The modified actions configuration object.
     */
    public function configureActions(Actions $actions): Actions
    {        
        return parent::configureActions($actions)
           ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('Ajouter une date');
            })
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action->setLabel('Créer la date');
            })
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, function (Action $action) {
                return $action->setLabel('Créer et ajouter une autre date');
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action
                    ->setIcon('fa fa-edit')
                    ->setLabel(false)
                    ->setHtmlAttributes([
                        'title' => 'Modifier cet élément',
                    ])
                    ->displayAsLink()
                    ->addCssClass('btn btn-sm btn-light');
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action
                    ->setIcon('fa fa-trash')
                    ->setLabel(false)
                    ->setHtmlAttributes([
                        'title' => 'Supprimer cet élément',
                    ])
                    ->displayAsLink()
                    ->addCssClass('btn btn-sm btn-light');
            });  
    }

    /**
     * Configures the CRUD interface for the EventDate entity.
     *
     * This method sets the form theme, entity labels, page titles, and inlined actions.
     *
     * @param Crud $crud The CRUD configuration object.
     * @return Crud The modified CRUD configuration object.
     */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
        ->addFormTheme('admin/form.html.twig')
        ->setEntityLabelInSingular('Date')
        ->setEntityLabelInPlural('Dates')
        ->setPageTitle('new', 'Ajouter une nouvelle date')
        ->showEntityActionsInlined();
    }

    /**
     * Configures the fields displayed in the CRUD interface for the EventDate entity.
     *
     * This method defines the fields to be displayed in the index, detail, edit, and new pages.
     *
     * @param string $pageName The name of the page being configured (e.g., 'index', 'new', 'edit').
     * @return iterable An iterable collection of field configurations.
     */
    public function configureFields(string $pageName): iterable
    {
        return [
            IntegerField::new('idEventDate', 'Identifiant')->onlyOnIndex(),
            DateField::new('date'),
            DateTimeField::new('dateModificationEventDate', 'Dernière modification')->onlyOnIndex(),
            TextField::new('userModificationEventDate', 'Faite par')->onlyOnIndex(),
        ];
    }

    /**
     * Deletes the entity instance from the database.
     *
     * This method prevents the deletion of an EventDate if it is currently associated with one or more Event entities.
     * If they are, it prevents deletion and sets an appropriate flash message.
     *
     * @param AdminContext $context The admin context containing the entity to delete.
     * @return mixed The result of the delete operation or a redirect response.
     */
    public function delete(AdminContext $context)
    {
        /** @var EventDate $eventDate */
        $eventDate = $context->getEntity()->getInstance();

        // Verify if there are related items
        $hasRelatedItems = $this->entityManager->getRepository(Event::class)
            ->count(['date' => $eventDate]) > 0;

        if ($hasRelatedItems) {
            $this->addFlash('danger', 'Impossible de supprimer cet élément car il est lié à un ou plusieurs éléments Évènements. il faut d\'abord supprimer ou reaffecter les éléments Évènements concernés');
            
            $url = $this->container->get(AdminUrlGenerator::class)
                ->setController(self::class)
                ->setAction(Action::INDEX)
                ->generateUrl();

            return $this->redirect($url);
        }

        return parent::delete($context);
    } 
}
