<?php

namespace App\Controller\Admin;

use App\Entity\Location;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

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
        //rennomage des actions possibles dans le formulaire
        return parent::configureActions($actions)
        //return $actions
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
            ->addFormTheme('admin/form.html.twig')
            ->setEntityLabelInSingular('Lieu')
            ->setEntityLabelInPlural('Lieux')
            ->setPageTitle('new', 'Ajouter un nouveau lieu');
        }

    public function configureFields(string $pageName): iterable
    {
        $fields = [
            TextField::new('name','Nom du lieu'),
            TextEditorField::new('description','Courte description du lieu pour afficher sur la carte interactive'),
            NumberField::new('latitude','Latitude'),
            NumberField::new('longitude','Longitude'),
        ];
        // Affiche le type de partenaire dans la liste des partenaires sans lien cliquable sinon dans la page de création garde le choix de liste
        if ($pageName === Crud::PAGE_INDEX || $pageName === Crud::PAGE_DETAIL) {             
            $fields[] = TextField::new('type.type', 'Type de lieu');
        } else {
            $addTypeUrl = $this->adminUrlGenerator
            ->setController(LocationTypeCrudController::class)
            ->setAction(Action::NEW)
            ->generateUrl();
            $fields[] = AssociationField::new('type', 'Type de lieu')
                ->setFormTypeOption('placeholder', 'Choisissez le type de lieu')
                ->setFormTypeOption('choice_label', 'type')
                ->setHelp(sprintf('Pas de type adapté ? <a href="%s">Créer un nouveau type</a>', $addTypeUrl));
        }
        return $fields;
    
    }

}
