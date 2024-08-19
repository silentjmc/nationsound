<?php

namespace App\Controller\Admin;

use App\Entity\Partners;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class PartnersCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Partners::class;
    }

    public function configureActions(Actions $actions): Actions
    {
    return $actions
        ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
            return $action->setLabel('Ajouter un partenaire');
        })
        ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, function (Action $action) {
            return $action->setLabel('Créer le partenaire');
        })
        ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, function (Action $action) {
            return $action->setLabel('Créer et ajouter un autre partenaire');
        })
    ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
        ->addFormTheme('admin/form.html.twig')
        ->setEntityLabelInSingular('Partenaire')
        ->setEntityLabelInPlural('Partenaires')
        ->setPageTitle('new', 'Ajouter un nouveau partenaire')
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = [
            TextField::new('name')->setLabel('Nom')
            ->setFormTypeOptions([
                'attr' => [
                    'placeholder' => 'Saisissez le nom du partenaire'
                ],
            ]),
            ImageField::new('image')->setLabel('Logo')
                ->setUploadDir('public/uploads/partners')
                ->setBasePath('uploads/partners')
                ->setUploadedFileNamePattern('[name][randomhash].[extension]'),
            TextField::new('url')->setLabel('URL')
            ->setFormTypeOptions([
                'attr' => [
                    'placeholder' => 'Saisissez l\'URL du partenaire'
                ],
            ]),
        ];
        // Affiche le type de partenaire dans la liste des partenaires sans lien cliquable sinon dans la page de création garde le choix de liste
        if ($pageName === Crud::PAGE_INDEX || $pageName === Crud::PAGE_DETAIL) {
            $fields[] = TextField::new('type.type', 'Type de partenaire');
        } else {
            $fields[] = AssociationField::new('type')
                ->setLabel('Type de partenaire')
                ->setFormTypeOption('placeholder', 'Choisissez le type de partenaire')
                ->setFormTypeOption('choice_label', 'type');
        }

        return $fields;
    
    }

}
