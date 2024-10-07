<?php

namespace App\Controller\Admin;

//use App\Entity\EntityHistory;

use App\Entity\LocationType;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;

class LocationTypeCrudController extends AbstractCrudController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

    }

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
        ->setEntityLabelInSingular('Type de lieu')
        ->setEntityLabelInPlural('Type de lieux')
        ->setPageTitle('new', 'Ajouter un nouveau type de lieu')
        ->showEntityActionsInlined();
    }

    public mixed $historyRepository = true;

    public function configureFields(string $pageName): iterable 
    {
        $fields = [
            TextField::new('type','Type de lieu'),
            ImageField::new('symbol',($pageName === Crud::PAGE_INDEX ? 'symbole' : 'Télécharger le symbole représentant le lieu sur la carte'))
                ->setUploadDir('public/uploads/locations')
                ->setBasePath('uploads/locations')
                ->setUploadedFileNamePattern('[name][randomhash].[extension]')
                ->setFormTypeOptions([
                    'required' => ($pageName === Crud::PAGE_NEW ? true : false),
                ]),
                DateTimeField::new('dateModification', 'Dernière modification')->onlyOnIndex(),
                TextField::new('userModification', 'Utilisateur')->onlyOnIndex()
        ];       
        return $fields;
    }


}










