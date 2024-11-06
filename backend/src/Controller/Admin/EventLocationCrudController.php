<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use App\Entity\EventLocation;
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
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class EventLocationCrudController extends AbstractCrudController
{
    private $entityManager;
    private $adminUrlGenerator;
    // injection du service EntityManagerInterface
    public function __construct(EntityManagerInterface $entityManager, AdminUrlGenerator $adminUrlGenerator)
    {
        $this->entityManager = $entityManager;
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    public static function getEntityFqcn(): string
    {
        return EventLocation::class;
    }

    public function configureActions(Actions $actions): Actions
    {        
        //renommage des actions possibles dans le formulaire
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
            });    
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
                    TextField::new('locationName','Nom du lieu'),
                    TextareaField::new('description','Description'),
                    TextField::new('typeLocation', 'Type de lieu'),
                    NumberField::new('latitude','Latitude')
                        ->setNumDecimals(14),
                    NumberField::new('longitude','Longitude')
                        ->setNumDecimals(14),
                    BooleanField::new('publish','Publié'),
                    DateTimeField::new('dateModification', 'Dernière modification'),
                    TextField::new('userModification', 'Utilisateur'),
                    ];
                } else {
                $addTypeUrl = $this->adminUrlGenerator
                    ->setController(EventLocationCrudController::class)
                    ->setAction(Action::NEW)
                    ->generateUrl();
                $fields = [
                    TextField::new('locationName','Nom du lieu'),
                    TextareaField::new('description','Courte description du lieu pour afficher sur la carte interactive'),
                    AssociationField::new('typeLocation', 'Type de lieu')
                        //->setFormTypeOption('placeholder', 'Choisissez le type de lieu')
                        ->setFormTypeOption('choice_label', 'type')
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
                    BooleanField::new('publish','Publié'),
                ];
            }
                return $fields;
        }
        
        public function delete(AdminContext $context)
        {
        /** @var EventLocation $eventLocation */
        $eventLocation = $context->getEntity()->getInstance();

        // Vérifier s'il existe des éléments Suivant liés
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
        
}
