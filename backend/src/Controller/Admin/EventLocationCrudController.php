<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use App\Entity\EventLocation;
use App\Service\PublishService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

/**
 * EventLocationCrudController is responsible for managing the CRUD operations for EventLocation.
 * It extends AbstractCrudController to leverage EasyAdmin's functionality.
 * 
 * This controller customizes the default CRUD operations for eventLocation, including:
 * - Configuration of fields displayed in forms and index pages.
 * - Custom labels, titles, and templates. 
 */
class EventLocationCrudController extends AbstractCrudController
{
    private EntityManagerInterface $entityManager;
    private AdminUrlGenerator $adminUrlGenerator;
    private AdminContextProvider $adminContextProvider;
    private PublishService $publishService;

    public function __construct(EntityManagerInterface $entityManager, AdminUrlGenerator $adminUrlGenerator, AdminContextProvider $adminContextProvider, PublishService $publishService)
    {
        $this->entityManager = $entityManager;
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->adminContextProvider = $adminContextProvider;
        $this->publishService = $publishService;
    }

    /**
     * Returns the fully qualified class name of the entity managed by this controller.
     * 
     * This method is used by EasyAdmin to determine which entity this controller is responsible for.
     *
     * @return string The fully qualified class name of the EventLocation entity.
     */
    public static function getEntityFqcn(): string
    {
        return EventLocation::class;
    }

    /**
     * Configures the actions available in the CRUD interface.
     *
     * This method sets custom labels and icons for actions such as New, Save, Edit, and Delete.
     * It also adds custom actions for publishing and unpublishing event locations.
     *
     * @param Actions $actions The actions configuration object.
     * @return Actions The configured actions object.
     */
    public function configureActions(Actions $actions): Actions
    {   
        // Define custom actions for publishing and unpublishing event locations     
        $publishAction = Action::new('publish', 'Publier', 'fa fa-eye')
            ->addCssClass('btn btn-sm btn-light text-success')
            ->setLabel(false)
            ->displayIf(fn ($entity) => !$entity->isPublishEventLocation())
            ->linkToCrudAction('publish')
            ->setHtmlAttributes([
                'title' => "Publier l'élément",
            ]);
        $unpublishAction = Action::new('unpublish', 'Dépublier', 'fa fa-eye-slash')
            ->addCssClass('btn btn-ms btn-light text-danger')
            ->setLabel(false)
            ->displayIf(fn ($entity) => $entity->isPublishEventLocation() && !$this->shouldDisplayUnpublishAction($entity))
            ->linkToCrudAction('unpublish')
            ->setHtmlAttributes([
                'title' => "Dépublier l'élément",       
            ]);
        $unpublishWithRelatedEventAction = Action::new('unpublishWithRelatedEvent', 'Dépublier', 'fa fa-eye-slash')
            ->addCssClass('btn btn-ms btn-light text-danger confirm-action')
            ->setLabel(false)
            ->displayIf(fn ($entity) => $this->shouldDisplayUnpublishAction($entity))
            ->linkToCrudAction('unpublish')
            ->setHtmlAttributes([
                'title' => "Dépublier l'élément avec ces évènements liés",            
                'data-bs-toggle' => 'modal',
               'data-bs-target' => '#modal-unpublish',
            ]);
        
        return parent::configureActions($actions)
           ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('Ajouter un lieu');
            })
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action->setLabel('Créer le lieu');
            })
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, function (Action $action) {
                return $action->setLabel('Créer et ajouter un autre lieu');
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
            })
            ->add(Crud::PAGE_INDEX,$publishAction) 
            ->add(Crud::PAGE_INDEX,$unpublishAction)
            ->add(Crud::PAGE_INDEX,$unpublishWithRelatedEventAction);
        }

        /**
         * Configures the CRUD settings for the EventLocation entity.
         *
         * This method sets the form theme, entity labels, page titles, and inlined actions.
         *
         * @param Crud $crud The CRUD configuration object.
         * @return Crud The modified CRUD configuration object.
         */
        public function configureCrud(Crud $crud): Crud
        {
            return $crud
            ->setFormThemes(['admin/location_form.html.twig'])
            ->overrideTemplate('crud/index', 'admin/location_index.html.twig')
            ->setEntityLabelInSingular('Lieu')
            ->setEntityLabelInPlural('Lieux')
            ->setPageTitle('new', 'Ajouter un nouveau lieu')
            ->showEntityActionsInlined();
        }
      
        /**
         * Configures the fields displayed in the CRUD interface for the EventLocation entity.
         *
         * This method defines the fields to be displayed in the index, detail, edit, and new pages.
         *
         * @param string $pageName The name of the page being configured (e.g., 'index', 'new', 'edit').
         * @return iterable An iterable collection of field configurations.
         */
        public function configureFields(string $pageName): iterable
        {
            if ($pageName === Crud::PAGE_INDEX) {
                $fields = [
                    IntegerField::new('idEventLocation', 'Identifiant'),
                    TextField::new('nameEventLocation','Nom du lieu'),
                    TextareaField::new('contentEventLocation','Description'),
                    TextField::new('typeLocation', 'Type de lieu'),
                    NumberField::new('latitude','Latitude')
                        ->setNumDecimals(14),
                    NumberField::new('longitude','Longitude')
                        ->setNumDecimals(14),
                    BooleanField::new('publishEventLocation','Publié')
                        ->renderAsSwitch(false),
                    DateTimeField::new('dateModificationEventLocation', 'Dernière modification'),
                    TextField::new('userModificationEventLocation', 'Utilisateur'),
                    ];
                } else {
                $addTypeUrl = $this->adminUrlGenerator
                    ->setController(EventLocationCrudController::class)
                    ->setAction(Action::NEW)
                    ->generateUrl();
                $fields = [
                    TextField::new('nameEventLocation','Nom du lieu')
                        ->setFormTypeOptions([
                            'attr' => ['placeholder' => 'Saisissez le nom du lieu'],
                        ]),
                    TextareaField::new('contentEventLocation','Courte description du lieu pour afficher sur la carte interactive')
                        ->setFormTypeOptions([
                            'attr' => ['placeholder' => 'Saisissez la description du lieu'],
                        ]),
                    AssociationField::new('typeLocation', 'Type de lieu')
                        ->setFormTypeOptions([
                            'choice_label' => 'nameLocationType',
                            'placeholder' => 'Sélectionnez le type du lieu'
                        ])
                        ->setHelp(sprintf('Pas de type adapté ? <a href="%s">Créer un nouveau type</a>', $addTypeUrl)),
                    FormField::addPanel('Position géographique')
                        ->setHelp('Vous devez indiquer la position en cliquant directement sur la carte ci-dessous. <br>Le marqueur du lieu actuel avec un marqueur bleu. Vous pouvez déplacer le marqueur bleu en cliquant sur la carte pour ajuster la position du lieu. <br>Les autres marqueurs de lieux déjà enregistrés sont fixes.'),
                    FormField::addRow(),
                    NumberField::new('latitude','Latitude')
                        ->setNumDecimals(14)
                        ->setFormTypeOption('attr', ['readonly' => true])
                        ->setColumns(3),
                    NumberField::new('longitude','Longitude')
                        ->setNumDecimals(14)
                        ->setFormTypeOption('attr', ['readonly' => true])
                        ->setColumns(3),
                    BooleanField::new('publishEventLocation','Publié'),
                ];
            }
                return $fields;
        }
        
        /**
         * Deletes the entity instance from the database.
         *
         * This method checks if there are any related Event entities before allowing deletion.
         * If they are, it prevents deletion and redirects with an error message.
         *
         * @param AdminContext $context The admin context containing the entity to delete.
         * @return mixed The result of the delete operation or a redirect response.
         */
        public function delete(AdminContext $context)
        {
            $eventLocation = $context->getEntity()->getInstance();

            // Verify if there are related items
            $hasRelatedItems = $this->entityManager->getRepository(Event::class)
                ->count(['eventLocation' => $eventLocation]) > 0;

            // If there are related items, prevent deletion and display an error message
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

        /**
         * Custom action to publish the EventLocation
         * 
         * This method uses the PublishService to handle the publishing logic
         * and redirects back to the index page with a success flash message.
         * 
         * @param AdminContext $context The admin context containing the entity to publish.
         * @return Response A redirect response to the index page with a success flash message.
         */
        public function publish(AdminContext $context): Response
        {
            $result = $this->publishService->publish($context);
            $url = $result['url'];
            $this->addFlash('success', 'Lieu publié avec succès');
            return $this->redirect($url);
        }

        /**
         * Custom action to unpublish the EventLocation
         * 
         * This method uses the PublishService to handle the unpublishing logic
         * and redirects back to the index page with a success flash message.
         * 
         * @param AdminContext $context The admin context containing the entity to unpublish.
         * @return Response A redirect response to the index page with a success flash message. 
         */
        public function unpublish(AdminContext $context): Response
        {
            $result = $this->publishService->unpublish($context);
            $url = $result['url'];
            $hasRelatedItems = $result['hasRelatedItems'];
            if ($hasRelatedItems) {
                $this->addFlash('success', 'Lieu et événements liés dépubliés avec succès');
            } else {
                $this->addFlash('success', 'Lieu dépublié avec succès');
            }
            return $this->redirect($url);
        }

    /**
     * Determines if the "unpublish with related events" action should be displayed.
     *
     * This private helper method checks two conditions:
     * 1. If the given EventLocation is currently published.
     * 2. If this EventLocation has any associated Event entities that are also published.
     *
     * It's used in `configureActions` to conditionally display different unpublish buttons.
     *
     * @param EventLocation $eventLocation The EventLocation entity to check.
     * @return bool True if the specific unpublish action (with modal for related events) should be displayed,
     *              false otherwise.
     */
    private function shouldDisplayUnpublishAction(EventLocation $eventLocation): bool
    {
        if (!$eventLocation->isPublishEventLocation()) {
            return false;
        }

        $hasRelatedPublishedEvents = $this->entityManager->getRepository(Event::class)
            ->count(['eventLocation' => $eventLocation, 'publishEvent' => true]) > 0;

        return $hasRelatedPublishedEvents;
    }
}