<?php

namespace App\Controller\Admin;

use App\Controller\Admin\Trait\UrlGeneratorTrait;
use App\Entity\Event;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Entity\Artist;
use App\Entity\EventDate;
use App\Entity\EventLocation;
use App\Entity\EventType;
use App\Service\PublishService;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

/*
 * EventCrudController is responsible for managing event entities in the admin panel.
 * It extends AbstractCrudController to provide CRUD operations for Event entities.
 * It includes custom configurations for fields, actions, and entity updates.
 */
class EventCrudController extends AbstractCrudController
{
    use UrlGeneratorTrait;
    private EntityManagerInterface $entityManager;
    private AdminUrlGenerator $adminUrlGenerator;
    private PublishService $publishService;
    private LoggerInterface $logger;

    /**
     * EventCrudController constructor.
     *
     * Initializes the controller with the necessary services.
     *
     * @param EntityManagerInterface $entityManager The Doctrine entity manager.
     * @param AdminUrlGenerator $adminUrlGenerator The EasyAdmin URL generator service.
     * @param PublishService $publishService The service for handling publish actions.
     */
    public function __construct(EntityManagerInterface $entityManager, AdminUrlGenerator $adminUrlGenerator, PublishService $publishService, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->publishService = $publishService;
    }

    /**
     * Returns the fully qualified class name of the entity managed by this controller.
     *
     * This method is used by EasyAdmin to determine which entity this controller is responsible for.
     *
     * @return string The fully qualified class name of the Event entity.
     */
    public static function getEntityFqcn(): string
    {
        return Event::class;
    }

    /**
     * Configures the actions available in the CRUD interface.
     *
     * This method sets custom labels and icons for actions such as New, Save, Edit, and Delete
     * It also configures publish and unpublish actions with specific conditions for display.
     *
     * @param Actions $actions The actions configuration object.
     * @return Actions The modified actions configuration object.
     */
    public function configureActions(Actions $actions): Actions
    {        
        // Define custom actions for publishing and unpublishing Event
        $publishAction = Action::new('publish', 'Publier', 'fa fa-eye')
            ->addCssClass('btn btn-sm btn-light text-success')
            ->setLabel(false)
            ->displayIf(fn ($entity) => !$entity->isPublishEvent() && !$this->hasrelatedPublishEventLocation($entity))
            ->linkToCrudAction('publish')
            ->setHtmlAttributes([
                'title' => "Publier l'élément",
            ]);

        $publishWithRelationAction = Action::new('publishWithRelatedEventLocation', 'Publier', 'fa fa-eye')
        ->addCssClass('btn btn-sm btn-light text-success confirm-action')
        ->setLabel(false)
        ->displayIf(fn ($entity) => !$entity->isPublishEvent() && $this->hasrelatedPublishEventLocation($entity))
        ->linkToCrudAction('publish')
        ->setHtmlAttributes([
            'title' => "Publier l'élément et les lieux en relation",
            'data-bs-toggle' => 'modal',
            'data-bs-target' => '#modal-publish',
        ]);

        $unpublishAction = Action::new('unpublish', 'Dépublier', 'fa fa-eye-slash')
            ->addCssClass('btn btn-ms btn-light text-danger')
            ->setLabel(false)
            ->displayIf(fn ($entity) => $entity->isPublishEvent())
            ->linkToCrudAction('unpublish')
            ->setHtmlAttributes([
                'title' => "Dépublier l'élément",       
            ]);

        // Add the custom actions to the actions configuration and customize existing actions.
        return parent::configureActions($actions)
           ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('Ajouter un évènement');
            })
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action->setLabel('Créer l\'évènement');
            })
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, function (Action $action) {
                return $action->setLabel('Créer et ajouter un autre évènement');
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
            ->add(Crud::PAGE_INDEX,$publishWithRelationAction) 
            ->add(Crud::PAGE_INDEX,$unpublishAction);                    
        }

        /**
         * Configures the CRUD interface for the Event entity.
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
                ->overrideTemplate('crud/index', 'admin/event_index.html.twig')
                ->setEntityLabelInSingular('Évènement')
                ->setEntityLabelInPlural('Évènements')
                ->setPageTitle('new', 'Ajouter un nouvel évènement')
                ->setTimeFormat('short')
                ->showEntityActionsInlined();
        }

        /**
         * Configures the fields displayed in the CRUD interface for the Event entity.
         *
         * This method defines the fields to be displayed in the index, detail, edit, and new pages.
         *
         * @param string $pageName The name of the page being configured (e.g., 'index', 'new', 'edit').
         * @return iterable An iterable collection of field configurations.
         */
        public function configureFields(string $pageName): iterable
        {
            $fields = [];
            $artistRepository = $this->entityManager->getRepository(Artist::class);

            if ($pageName === Crud::PAGE_INDEX) { 
                $fields=[IntegerField::new('idEvent', 'Identifiant'),
                TextField::new('type.nameType', 'Type d\'évènement' ),
                TextField::new('artist.nameArtist','Artiste'),
                TextField::new('eventLocation.nameEventLocation','Lieu'),
                TextField::new('date.dateToString','Date de l\'évènement'),
                TimeField::new('heureDebut','Heure de début'),
                TimeField::new('heureFin','Heure de fin'),
                BooleanField::new('publishEvent','Publié')->renderAsSwitch(false),
                DateTimeField::new('dateModificationEvent', 'Dernière modification'),
                TextField::new('userModificationEvent', 'Utilisateur')];
            } else {
                $addTypeEventUrl = $this->addUrl(EventTypeCrudController::class);
                $addArtistUrl = $this->addUrl(ArtistCrudController::class);
                $addLocationUrl = $this->addUrl(EventLocationCrudController::class);

                $fields=[
                    AssociationField::new('type', 'Type d\'évènement')
                        ->setQueryBuilder(
                            fn (QueryBuilder $queryBuilder) => $queryBuilder->orderBy('entity.nameType', 'ASC')
                            )
                        ->setFormTypeOptions([
                                'choice_label' => 'nameType',
                                'placeholder' => 'Choisissez le type d\'évènement'
                        ])
                        ->setHelp(sprintf('Pas de type adapté ? <a href="%s">Créer un nouveau type</a>', $addTypeEventUrl)),
                    AssociationField::new('artist','Artiste')
                        ->setQueryBuilder(
                            fn (QueryBuilder $queryBuilder) => $queryBuilder->orderBy('entity.nameArtist', 'ASC')
                            )
                        ->setFormTypeOptions([
                            'choice_label' => 'nameArtist',
                            'placeholder' => 'Choisissez l\'artiste'
                    ])
                        ->setHelp(sprintf('Pas d\'artiste adapté ? <a href="%s">Créer un nouvel artiste</a>', $addArtistUrl)),
                    AssociationField::new('eventLocation','Lieu')
                        ->setFormTypeOptions([
                            'choice_label' => 'nameEventLocation',
                            'placeholder' => 'Choisissez le lieu de l\'évènement'
                    ])
                        ->setQueryBuilder(function ($queryBuilder) {
                            return $queryBuilder->andWhere('entity.publishEventLocation = :active')
                                                ->setParameter('active', true)
                                                ->orderBy('entity.nameEventLocation', 'ASC');
                            })
                            ->setHelp(sprintf('Pas de lieu adapté ? <a href="%s">Créer un nouveau lieu</a>', $addLocationUrl)),
                    AssociationField::new('date','Date de l\'évènement')
                        ->setFormTypeOption('choice_label', 'datetostring'),
                    TimeField::new('heureDebut','Heure de début')
                        ->setColumns(2),        
                    TimeField::new('heureFin','Heure de fin')
                        ->setColumns(2),
                    BooleanField::new('publishEvent','Publié')
                ];
            }
            return $fields;
        }

    /**
     * Custom action to publish the Event.
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
        $hasRelatedItems = $result['hasRelatedItems'];
        if ($hasRelatedItems) {
            $this->addFlash('success', 'Évènement et lieu liés dépubliés avec succès');
        } else {
            $this->addFlash('success', 'Évènement publié avec succès');
        }
        return $this->redirect($url);
    }

    /**
     * Custom action to unpublish the Event.
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
        $this->addFlash('success', 'Évènement dépublié avec succès');            
        return $this->redirect($url);
    }

    /**
     * Checks if the given Event has a related EventLocation that is currently unpublished.
     *
     * This private helper method is used in `configureActions` to determine which "publish"
     * button variant to display for an Event (standard publish or publish with modal for location).
     *
     * @param Event $event The Event entity to check.
     * @return bool True if the event's location is set and is currently unpublished, false otherwise.
     */ 
    private function hasrelatedPublishEventLocation(Event $event): bool
    {
        $hasRelatedPublishedEvents = $this->entityManager->getRepository(EventLocation::class)
        ->count(['idEventLocation' => $event->getEventLocation()->getIdEventLocation(), 'publishEventLocation' => false]) > 0;

        return $hasRelatedPublishedEvents;
    }
}