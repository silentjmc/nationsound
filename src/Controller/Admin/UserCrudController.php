<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
        ->addFormTheme('admin/form.html.twig')
        ->setEntityLabelInSingular('Utilisateur')
        ->setEntityLabelInPlural('Utilisateurs')
        ->setPageTitle('new', 'Ajouter un nouvel utilisateur')
            ;
    }

    public function configureActions(Actions $actions): Actions
    {
    return $actions
        ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
            return $action->setLabel('Ajouter un utilisateur');
        })
        ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, function (Action $action) {
            return $action->setLabel('Créer l\'utilisateur');
        })
        ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, function (Action $action) {
            return $action->setLabel('Créer et ajouter un autre utilisateur');
        })
    ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            EmailField::new('email')
                ->setLabel('Email')
                ->setRequired(true)
                ->setHelp('Les champs marqués d\'un astérisque (*) sont obligatoires')
                ->setFormTypeOptions([
                    'attr' => [
                        'placeholder' => 'Saisissez l\'email de l\'utilisateur'
                    ],
                ]),
            TextField::new('password')
                ->setLabel('Mot de passe')
                ->setRequired(true)
                ->setFormTypeOptions([
                    'attr' => [
                        'placeholder' => 'Saisissez le mot de passe de l\'utilisateur'
                    ],
                ]),
            TextField::new('lastname')
                ->setLabel('Nom')
                ->setRequired(true)
                ->setFormTypeOptions([
                    'attr' => [
                        'placeholder' => 'Saisissez le nom de l\'utilisateur'
                    ],
                ]),
            TextField::new('firstname')
                ->setLabel('Prénom')
                ->setRequired(true)
                ->setFormTypeOptions([
                    'attr' => [
                        'placeholder' => 'Saisissez le prénom de l\'utilisateur'
                    ],
                ]),
            AssociationField::new('role')
                ->setLabel('Rôle')
                ->setRequired(true)
                ->setFormTypeOption('placeholder', 'Choisissez le rôle de l\'utilisateur')


        ];
    }

}
