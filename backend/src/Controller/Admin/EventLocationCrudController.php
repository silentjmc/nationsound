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

    public static function getEntityFqcn(): string
    {
        return EventLocation::class;
    }

    public function configureActions(Actions $actions): Actions
    {   
        // New actions     
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
        
        public function delete(AdminContext $context)
        {
        /** @var EventLocation $eventLocation */
            $eventLocation = $context->getEntity()->getInstance();

            // Verify if there are related items
            $hasRelatedItems = $this->entityManager->getRepository(Event::class)
                ->count(['eventLocation' => $eventLocation]) > 0;

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

        public function publish(AdminContext $context): Response
        {
            $result = $this->publishService->publish($context);
            $url = $result['url'];
            $this->addFlash('success', 'Lieu publié avec succès');
            return $this->redirect($url);
        }

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
