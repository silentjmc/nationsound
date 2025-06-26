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

/**
 * RoleCrudController is responsible for managing Role entities in the admin panel.
 * It extends AbstractCrudController to provide CRUD operations for Role entities.
 * It includes custom configurations for fields, actions, and entity updates.
 */
class RoleCrudController extends AbstractCrudController
{
    private EntityManagerInterface $entityManager;

    /**
     * RoleCrudController constructor.
     *
     * Initializes the controller with the EntityManagerInterface.
     *
     * @param EntityManagerInterface $entityManager The Doctrine entity manager.
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    public static function getEntityFqcn(): string
    {
        return Role::class;
    }

    /**
     * Configures the CRUD settings for the Role entity.
     *
     * This method sets the form theme, entity labels, page titles, and inlined actions.
     *
     * @param Crud $crud The CRUD configuration object.
     * @return Crud The configured CRUD object.
     */
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

    /**
     * Configures the actions available for the Role entity.
     *
     * This method removes certain actions from the detail and index pages,
     * and disables delete, edit, and new actions globally.
     *
     * @param Actions $actions The actions configuration object.
     * @return Actions The configured actions object.
     */
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->remove(Crud::PAGE_DETAIL, Action::DELETE)
            ->remove(Crud::PAGE_DETAIL, Action::EDIT)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->disable(Action::DELETE, Action::EDIT, Action::NEW);
    }

    /**
     * Configures the fields displayed in the CRUD interface for the Role entity.
     *
     * This method defines the fields to be displayed in the index, detail, edit, and new pages.
     *
     * @param string $pageName The name of the page being configured (index, detail, edit, new).
     * @return iterable An iterable collection of field configurations.
     */
    public function configureFields(string $pageName): iterable
    {
        return [   
            IdField::new('idRole', 'Identifiant'),  
            TextField::new('role')
                ->setLabel('Rôle')
        ];
    }
}