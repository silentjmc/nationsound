<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use App\Entity\EventType;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

/**
 * EventTypeCrudController is responsible for managing the CRUD operations for EventType entity.
 * It extends AbstractCrudController to leverage EasyAdmin's functionality.
 * 
 * This controller customizes the default CRUD operations for eventType, including:
 * - Configuration of fields displayed in forms and index pages.
 * - Custom labels, titles, and templates. 
 */
class EventTypeCrudController extends AbstractCrudController
{
    private EntityManagerInterface $entityManager;

    /**
     * EventTypeCrudController constructor.
     *
     * Initializes the controller with the necessary services.
     *
     * @param EntityManagerInterface $entityManager The Doctrine entity manager.
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * Returns the fully qualified class name of the entity managed by this controller.
     * 
     * This method is used by EasyAdmin to determine which entity this controller is responsible for.
     *
     * @return string The fully qualified class name of the EventType entity.
     */
    public static function getEntityFqcn(): string
    {
        return EventType::class;
    }

    /**
     * Configures the actions available in the CRUD interface.
     *
     * This method sets custom labels and icons for actions such as New, Save, Edit, and Delete.
     *
     * @param Actions $actions The actions configuration object.
     * @return Actions The configured actions object.
     */
    public function configureActions(Actions $actions): Actions
    {
    // Update the actions with custom labels and icons
    return $actions
        ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
            return $action->setLabel('Ajouter un type d\'évènement');
        })
        ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, function (Action $action) {
            return $action->setLabel('Créer le type d\'évènement');
        })
        ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, function (Action $action) {
            return $action->setLabel('Créer et ajouter un autre type d\'évènement');
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
     * Configures the CRUD settings for the EventType entity.
     *
     * This method sets the form theme, entity labels, page titles, and inlined actions.
     *
     * @param Crud $crud The CRUD configuration object.
     * @return Crud The configured CRUD object.
     */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
        ->addFormTheme('admin/form.html.twig')
        ->setEntityLabelInSingular('Type d\'évènement')
        ->setEntityLabelInPlural('Type d\'évènements')
        ->setPageTitle('new', 'Ajouter un nouveau type d\'évènement')
        ->showEntityActionsInlined();
    }

    /**
     * Configures the fields displayed in the CRUD interface for the EventType entity.
     *
     * This method defines the fields to be displayed in the index, detail, edit, and new pages.
     *
     * @param string $pageName The name of the page being configured (e.g., index, new, edit).
     * @return iterable An iterable collection of field configurations.
     */
    public function configureFields(string $pageName): iterable
    {
        return [
            IntegerField::new('idEventType', 'Identifiant')->onlyOnIndex(),
            TextField::new('nameType','Type d\'évènement')
                ->setFormTypeOptions([
                    'attr' => ['placeholder' => 'Saisissez le type d\'évènement'],
                ]),
            DateTimeField::new('dateModificationEventType', 'Dernière modification')->onlyOnIndex(),
            TextField::new('userModificationEventType', 'Utilisateur')->onlyOnIndex(),
        ];
    }

    /**
     * Deletes the entity instance from the database.
     *
     * This method checks if there are any related Event entities before allowing deletion.
     * If they are, it prevents deletion and redirects with a flash message.
     *
     * @param AdminContext $context The admin context containing the entity to be deleted.
     * @return mixed The result of the delete operation or a redirect response.
     */
    public function delete(AdminContext $context)
    {
        $eventType = $context->getEntity()->getInstance();
        // Verify if there are related items
        $hasRelatedItems = $this->entityManager->getRepository(Event::class)
            ->count(['type' => $eventType]) > 0;

        // If there are related items, prevent deletion and set a flash message
        if ($hasRelatedItems) {
            $this->addFlash('danger', 'Impossible de supprimer cet élément car il est lié à un ou plusieurs éléments Évènements. il faut d\'abord supprimer ou reaffecter les éléméents Évènements concernés');
            
            $url = $this->container->get(AdminUrlGenerator::class)
                ->setController(self::class)
                ->setAction(Action::INDEX)
                ->generateUrl();

            return $this->redirect($url);
        }

        return parent::delete($context);
    }

}