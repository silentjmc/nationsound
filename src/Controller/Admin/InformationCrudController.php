<?php

namespace App\Controller\Admin;

use App\Entity\Information;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class InformationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Information::class;
    }

    public function configureActions(Actions $actions): Actions
    {
    return $actions
        ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
            return $action->setLabel('Ajouter une information');
        })
        ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, function (Action $action) {
            return $action->setLabel('Créer l\'information');
        })
        ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, function (Action $action) {
            return $action->setLabel('Créer et ajouter une autre information');
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
        ->setEntityLabelInSingular('Information')
        ->setEntityLabelInPlural('Informations')
        ->setPageTitle('new', 'Ajouter une nouvelle information')
        ->showEntityActionsInlined();
    }
    
    public function configureFields(string $pageName): iterable
    {
        $fields = [
            ChoiceField::new('type', 'Type')
            ->setChoices([
                'Information' => 'Information',
                'Scène' => 'Scène'
            ]),
            TextField::new('titre','Titre '),
            TextEditorField::new('description', 'Texte'),
            ImageField::new('picto','picto')
                ->setUploadDir('public/uploads/informations')
                ->setBasePath('uploads/informations')
                ->setUploadedFileNamePattern('[name][randomhash].[extension]'),
        ];    
        return $fields;
    }
    
}
