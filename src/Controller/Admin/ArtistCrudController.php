<?php

namespace App\Controller\Admin;

use App\Entity\Artist;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ArtistCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Artist::class;
    }

    public function configureActions(Actions $actions): Actions
    {
    return $actions
        ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
            return $action->setLabel('Ajouter un artiste');
        })
        ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, function (Action $action) {
            return $action->setLabel('Créer un artiste');
        })
        ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, function (Action $action) {
            return $action->setLabel('Créer et ajouter un autre artiste');
        });
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
        ->addFormTheme('admin/form.html.twig')
        ->setEntityLabelInSingular('Artiste')
        ->setEntityLabelInPlural('Artistes')
        ->setPageTitle('new', 'Ajouter un nouvel artiste');
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = [
            TextField::new('name', 'Nom de l\'artiste ou du groupe')
                ->setFormTypeOptions([
                    'attr' => [
                        'placeholder' => 'Saisissez le nom de l\'artiste'
                    ],
                ]),
            TextareaField::new('description', 'Description' . ($pageName === Crud::PAGE_INDEX ? '' : ' de l\'artiste ou du groupe'))
            ->setFormTypeOptions([
                'attr' => [
                    'placeholder' => 'Saisissez la description de l\'artiste'
                ],
            ]),
            ImageField::new('image','Image'. ($pageName === Crud::PAGE_INDEX ? '' : ' de l\'artiste ou du groupe'))
                    ->setUploadDir('public/uploads/artists')
                    ->setBasePath('uploads/artists')
                    ->setUploadedFileNamePattern('[name][randomhash].[extension]')
                    ->setFormTypeOptions([
                        'required' => ($pageName === Crud::PAGE_NEW ? true : false),
                    ]),
            TextField::new('type_music', 'Type de musique')
                ->setFormTypeOptions([
                    'attr' => [
                        'placeholder' => 'Saisissez le type de musique de l\'artiste'
                    ],
                ]),
        ];

        return $fields;
    }
    
}
