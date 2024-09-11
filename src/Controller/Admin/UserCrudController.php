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
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

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
        ->showEntityActionsInlined();
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

    public function configureFields(string $pageName): iterable
    {
        $fields = [];
        if ($pageName === Crud::PAGE_INDEX) { 
            $fields = [
                TextField::new('email','Email'),
                TextField::new('lastname','Nom'),
                TextField::new('firstname','Prénom'),
                TextField::new('role','Rôle')];

        } else {    
        $fields = [
            EmailField::new('email','Email')
                ->setFormTypeOptions([
                    'attr' => [
                        'placeholder' => 'Saisissez l\'email de l\'utilisateur'
                    ],
                ]),
            TextField::new('lastname','Nom')
                ->setFormTypeOptions([
                    'attr' => [
                        'placeholder' => 'Saisissez le nom de l\'utilisateur'
                    ],
                ]),
            TextField::new('firstname','Prénom')
                ->setFormTypeOptions([
                    'attr' => [
                        'placeholder' => 'Saisissez le prénom de l\'utilisateur'
                    ],
                ]),
            AssociationField::new('role','Rôle')
                ->setFormTypeOption('placeholder', 'Choisissez le rôle de l\'utilisateur')
                ->setFormTypeOption('choice_label', 'role')
        ];}

        if ($pageName === Crud::PAGE_NEW) {
            $fields[] = TextField::new('password', 'Mot de passe')
                ->setFormType(RepeatedType::class)
                ->setFormTypeOptions([
                    'type' => PasswordType::class,
                    'first_options' => ['label' => 'Mot de passe'],
                    'second_options' => ['label' => 'Répéter le mot de passe'],
                    'mapped' => false,
                ]);
        } elseif ($pageName === Crud::PAGE_EDIT) {
            $fields[] = TextField::new('password', 'Mot de passe')
                ->setFormType(PasswordType::class)
                ->setFormTypeOptions([
                    'attr' => ['placeholder' => 'Laissez vide pour ne pas changer'],
                    'required' => false,
                    'mapped' => false,
                ]);
        }

        return $fields;
    }

}
