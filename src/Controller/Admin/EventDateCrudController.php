<?php

namespace App\Controller\Admin;

use App\Entity\EventDate;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;

class EventDateCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return EventDate::class;
    }

    public function configureActions(Actions $actions): Actions
    {        
        //rennomage des actions possibles dans le formulaire
        return parent::configureActions($actions)
        //return $actions
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

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
        ->addFormTheme('admin/form.html.twig')
        ->setEntityLabelInSingular('Date')
        ->setEntityLabelInPlural('Dates')
        ->setPageTitle('new', 'Ajouter une nouvelle date')
        ->showEntityActionsInlined();
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            DateField::new('date'),
            BooleanField::new('actif'),
        ];
    }
    
}
