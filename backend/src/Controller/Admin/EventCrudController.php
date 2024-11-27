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
use Symfony\Component\HttpFoundation\Response;

class EventCrudController extends AbstractCrudController
{
    use UrlGeneratorTrait;
    private EntityManagerInterface $entityManager;
    private AdminUrlGenerator $adminUrlGenerator;
    private PublishService $publishService;
    // injection de service
    public function __construct(EntityManagerInterface $entityManager, AdminUrlGenerator $adminUrlGenerator, PublishService $publishService)
    {
        $this->entityManager = $entityManager;
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->publishService = $publishService;
    }

    public static function getEntityFqcn(): string
    {
        return Event::class;
    }

    public function configureActions(Actions $actions): Actions
    {        
    $publishAction = Action::new('publish', 'Publier', 'fa fa-eye')
        ->addCssClass('btn btn-sm btn-light text-success')
        ->setLabel(false)
        ->displayIf(fn ($entity) => !$entity->isPublish() && !$this->hasrelatedPublishEventLocation($entity))
        ->linkToCrudAction('publish')
        ->setHtmlAttributes([
            'title' => "Publier l'élément",
        ]);

    $publishWithRelationAction = Action::new('publishWithRelatedEventLocation', 'Publier', 'fa fa-eye')
    ->addCssClass('btn btn-sm btn-light text-success confirm-action')
    ->setLabel(false)
    ->displayIf(fn ($entity) => !$entity->isPublish() && $this->hasrelatedPublishEventLocation($entity))
    ->linkToCrudAction('publish')
    ->setHtmlAttributes([
        'title' => "Publier l'élément et les lieux en relation",
        'data-bs-toggle' => 'modal',
        'data-bs-target' => '#modal-publish',
    ]);

    $unpublishAction = Action::new('unpublish', 'Dépublier', 'fa fa-eye-slash')
        ->addCssClass('btn btn-ms btn-light text-danger')
        ->setLabel(false)
        ->displayIf(fn ($entity) => $entity->isPublish())
        ->linkToCrudAction('unpublish')
        ->setHtmlAttributes([
            'title' => "Dépublier l'élément",       
        ]);
        //rennomage des actions possibles dans le formulaire
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

        public function configureFields(string $pageName): iterable
    {
        $fields = [];

        if ($pageName === Crud::PAGE_INDEX) { 
            $fields=[IntegerField::new('id', 'Identifiant'),
            TextField::new('type.type', 'Type d\'évènement' ),
            TextField::new('artist.name','Artiste'),
            TextField::new('eventLocation.location_name','Lieu'),
            TextField::new('date','Date de l\'évènement'),
            TimeField::new('heure_debut','Heure de début'),
            TimeField::new('heure_fin','Heure de fin'),
            BooleanField::new('publish','Publié'),
            DateTimeField::new('dateModification', 'Dernière modification'),
            TextField::new('userModification', 'Utilisateur')];
        } else {
            $addTypeEventUrl = $this->addUrl(EventTypeCrudController::class);
            $addArtistUrl = $this->addUrl(ArtistCrudController::class);
            $addLocationUrl = $this->addUrl(EventLocationCrudController::class);

            $fields=[
                AssociationField::new('type', 'Type d\'évènement' )
                    //->setFormTypeOption('placeholder', 'Choisissez le type d\'évènement')
                    ->setFormTypeOption('choice_label', 'type')
                    ->setHelp(sprintf('Pas de type adapté ? <a href="%s">Créer un nouveau type</a>', $addTypeEventUrl)),
                AssociationField::new('artist','Artiste')
                    //->setFormTypeOption('placeholder', 'Choisissez l\'artiste')
                    ->setFormTypeOption('choice_label', 'name')
                    ->setHelp(sprintf('Pas d\'artiste adapté ? <a href="%s">Créer un nouvel artiste</a>', $addArtistUrl)),
                AssociationField::new('eventLocation','Lieu')
                //    ->setFormTypeOption('placeholder', 'Choisissez le lieu')
                    ->setFormTypeOption('choice_label', 'locationName')
                    ->setHelp(sprintf('Pas de lieu adapté ? <a href="%s">Créer un nouveau lieu</a>', $addLocationUrl))
                    ->setQueryBuilder(function ($queryBuilder) {
                        return $queryBuilder->andWhere('entity.publish = :active')
                                            ->setParameter('active', true);
                        }),
                AssociationField::new('date','Date de l\'évènement')
                    ->setFormTypeOption('choice_label', 'datetostring'),
                TimeField::new('heure_debut','Heure de début')
                    ->setColumns(2),        
                TimeField::new('heure_fin','Heure de fin')
                    ->setColumns(2),
                BooleanField::new('publish','Publié')
            ];

        }
        return $fields;
    }

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


    public function unpublish(AdminContext $context): Response
    {
        $result = $this->publishService->unpublish($context);
        $url = $result['url'];
        $this->addFlash('success', 'Évènement dépublié avec succès');            
        return $this->redirect($url);
    }
    /*
    private function hasrelatedPublishEventLocation(AdminContext $context, Event $event): bool
    {
        $entity = $context->getEntity()->getInstance();
        if (!$event->isPublish()) {
            return false;
        }

        $hasrelatedPublishEventLocation = $this->entityManager->getRepository(EventLocation::class)
                ->count(['id' => $entity->getEventLocation()->getId(), 'publish' => false]) > 0;

        return $hasrelatedPublishEventLocation;
    }*/
    private function hasrelatedPublishEventLocation(Event $event): bool
    {
        //$hasRelatedPublishedEvents = $this->entityManager->getRepository(Event::class)
        //    ->count(['eventLocation' => $event->getEventLocation(), 'publish' => false]) > 0;
        $hasRelatedPublishedEvents = $this->entityManager->getRepository(EventLocation::class)
        ->count(['id' => $event->getEventLocation()->getId(), 'publish' => false]) > 0;

        return $hasRelatedPublishedEvents;
    }

}