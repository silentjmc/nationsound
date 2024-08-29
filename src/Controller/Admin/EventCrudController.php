<?php

namespace App\Controller\Admin;

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
use App\Entity\EventType;
use App\Entity\Location;

class EventCrudController extends AbstractCrudController
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
        return Event::class;
    }

    public function configureActions(Actions $actions): Actions
    {        
        //rennomage des actions possibles dans le formulaire
        return parent::configureActions($actions)
        //return $actions
           ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('Ajouter un évènement');
            })
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action->setLabel('Créer l\'évènement');
            })
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, function (Action $action) {
                return $action->setLabel('Créer et ajouter un autre évènement');
            });
        }

        public function configureCrud(Crud $crud): Crud
        {
            return $crud
            ->addFormTheme('admin/form.html.twig')
            ->setEntityLabelInSingular('Évènement')
            ->setEntityLabelInPlural('Évènements')
            ->setPageTitle('new', 'Ajouter un nouvel évènement');
        }

        public function configureFields(string $pageName): iterable
    {
        $fields = [
            TimeField::new('heure_debut','Heure de début'),
        ];
        // Affiche le type d'évènement dans la liste des partenaires sans lien cliquable sinon dans la page de création garde le choix de liste
        if ($pageName === Crud::PAGE_INDEX || $pageName === Crud::PAGE_DETAIL) {             
            $fields[] = AssociationField::new('type', 'Type d\'évènement' );
        } else {
            $addTypeUrl = $this->adminUrlGenerator
            ->setController(EventTypeCrudController::class)
            ->setAction(Action::NEW)
            ->generateUrl();
            $fields[] = AssociationField::new('type', 'Type d\'évènement' )
                ->setFormTypeOption('placeholder', 'Choisissez le type d\'évènement')
                ->setFormTypeOption('choice_label', 'type')
                ->setHelp(sprintf('Pas de type adapté ? <a href="%s">Créer un nouveau type</a>', $addTypeUrl));
        }

        if ($pageName === Crud::PAGE_INDEX || $pageName === Crud::PAGE_DETAIL) {             
            $fields[] = AssociationField::new('artist','Artiste');
        } else {
            $addTypeUrl = $this->adminUrlGenerator
            ->setController(ArtistCrudController::class)
            ->setAction(Action::NEW)
            ->generateUrl();
            $fields[] = AssociationField::new('artist','Artiste')
                ->setFormTypeOption('placeholder', 'Choisissez l\'artiste')
                ->setFormTypeOption('choice_label', 'artist')
                ->setHelp(sprintf('Pas d\'artsite adapté ? <a href="%s">Créer un nouvel artiste</a>', $addTypeUrl));
        }

        
        if ($pageName === Crud::PAGE_INDEX || $pageName === Crud::PAGE_DETAIL) {             
            $fields[] = AssociationField::new('location', 'Lieu');
        } else {
            $addTypeUrl = $this->adminUrlGenerator
            ->setController(LocationCrudController::class)
            ->setAction(Action::NEW)
            ->generateUrl();
            $fields[] = AssociationField::new('location', 'Lieu')
                ->setFormTypeOption('placeholder', 'Choisissez le lien')
                ->setFormTypeOption('choice_label', 'artist')
                ->setHelp(sprintf('Pas de lieu adapté ? <a href="%s">Créer un nouveau lieu</a>', $addTypeUrl));
        }

        return $fields;
    
    }
    
}
