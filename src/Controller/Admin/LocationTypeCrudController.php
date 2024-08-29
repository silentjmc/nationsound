<?php

namespace App\Controller\Admin;

use App\Entity\LocationType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;

class LocationTypeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return LocationType::class;
    }

    public function configureActions(Actions $actions): Actions
    {
    return $actions
        ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
            return $action->setLabel('Ajouter un typer de lieu');
        })
        ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, function (Action $action) {
            return $action->setLabel('Créer le type de lieu');
        })
        ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, function (Action $action) {
            return $action->setLabel('Créer et ajouter un autre type de lieu');
        });
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
        ->addFormTheme('admin/form.html.twig')
        ->setEntityLabelInSingular('Type de lieu')
        ->setEntityLabelInPlural('Type de lieux')
        ->setPageTitle('new', 'Ajouter un nouveau type de lieu')
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('type','Type de lieu'),
            ImageField::new('symbol','Télécharger le symbole représentant le lieu sur la carte')
                ->setUploadDir('public/uploads/locations')
                ->setBasePath('uploads/locations')
                ->setUploadedFileNamePattern('[name][randomhash].[extension]'),
        ];
    }
    
}
