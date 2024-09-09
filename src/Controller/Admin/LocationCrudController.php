<?php

namespace App\Controller\Admin;

use App\Entity\Location;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

class LocationCrudController extends AbstractCrudController
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
        return Location::class;
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
            });
        }

        public function configureCrud(Crud $crud): Crud
        {
            return $crud
            ->setFormThemes(['admin/location_form.html.twig'])
            ->overrideTemplate('crud/index', 'admin/location_index.html.twig')
            ->setEntityLabelInSingular('Lieu')
            ->setEntityLabelInPlural('Lieux')
            ->setPageTitle('new', 'Ajouter un nouveau lieu');
        }

    public function configureFields(string $pageName): iterable
    {

    if ($pageName === Crud::PAGE_INDEX) {
        $fields = [
            TextField::new('name','Nom du lieu'),
            TextareaField::new('description','Description'),
            TextField::new('type.type', 'Type de lieu'),
            NumberField::new('latitude','Latitude')
                ->setNumDecimals(14),
            NumberField::new('longitude','Longitude')
                ->setNumDecimals(14),
            ];
        } else {
         $addTypeUrl = $this->adminUrlGenerator
            ->setController(LocationTypeCrudController::class)
            ->setAction(Action::NEW)
            ->generateUrl();
         $fields = [
            TextField::new('name','Nom du lieu'),
            TextareaField::new('description','Courte description du lieu pour afficher sur la carte interactive'),
            AssociationField::new('type', 'Type de lieu')
                ->setFormTypeOption('placeholder', 'Choisissez le type de lieu')
                ->setFormTypeOption('choice_label', 'type')
                ->setHelp(sprintf('Pas de type adapté ? <a href="%s">Créer un nouveau type</a>', $addTypeUrl)),
            FormField::addPanel('Position géographique')
                ->setHelp('Vous pouvez indiquer la position en cliquant directement sur la carte ci-dessous.'),
            FormField::addRow(),
            NumberField::new('latitude','Latitude')
            ->setNumDecimals(14)
            ->setFormTypeOption('attr', ['readonly' => true])
            ->setColumns(3),
            NumberField::new('longitude','Longitude')
            ->setNumDecimals(14)
            ->setFormTypeOption('attr', ['readonly' => true])
            ->setColumns(3),
        ];
    }
        return $fields;
    
    }

    public function getLocations(): Response
    {
        $repository = $this->entityManager->getRepository(Location::class);
        $locations = $repository->findAll();

        return $this->render('admin/location_index.html.twig', [
            'locations' => $locations,
        ]);

    }

}
