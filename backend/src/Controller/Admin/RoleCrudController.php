<?php

namespace App\Controller\Admin;

use App\Entity\Role;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;

class RoleCrudController extends AbstractCrudController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    public static function getEntityFqcn(): string
    {
        return Role::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
        ->addFormTheme('admin/form.html.twig')
        ->setEntityLabelInSingular('Rôle')
        ->setEntityLabelInPlural('Rôles')
        ->setPageTitle('new', 'Ajouter un nouveau rôle')
        ->setPageTitle('index', 'Liste des Rôles (Lecture seule)')
        ->showEntityActionsInlined(false);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->remove(Crud::PAGE_DETAIL, Action::DELETE)
            ->remove(Crud::PAGE_DETAIL, Action::EDIT)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->disable(Action::DELETE, Action::EDIT, Action::NEW);
    }

    public function configureFields(string $pageName): iterable
    {
        return [   
            IdField::new('idRole', 'Identifiant'),  
            TextField::new('role')
                ->setLabel('Rôle')
        ];
    }
}