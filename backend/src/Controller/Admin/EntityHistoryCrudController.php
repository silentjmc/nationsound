<?php

namespace App\Controller\Admin;

use App\Entity\EntityHistory;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * EntityHistoryCrudController is responsible for managing the CRUD operations for the EntityHistory entity.
 * It extends AbstractCrudController to leverage EasyAdmin's functionality.
 * 
 * This controller customizes the default CRUD operations for FAQs, including:
 * - Configuration of fields displayed in forms and index pages.
 * - Custom labels, titles, and templates.
 */
class EntityHistoryCrudController extends AbstractCrudController
{
    /**
     * Returns the fully qualified class name of the entity managed by this controller.
     * This method is used by EasyAdmin to determine which entity this controller is responsible for.
     *
     * @return string The fully qualified class name of the EntityHistory entity.
     */
    public static function getEntityFqcn(): string
    {
        return EntityHistory::class;
    }

    /**
     * Configures the actions available in the CRUD interface.
     *
     * This method disables the 'new', 'edit', and 'delete' actions, making the CRUD
     * effectively read-only. It adds and customizes the 'DETAIL' action to allow
     * users to view the specifics of a history record.
     *
     * @param Actions $actions The actions configuration object.
     * @return Actions The modified actions configuration object.
     */
    public function configureActions(Actions $actions): Actions
    {
        return $actions
        ->disable('new', 'edit', 'delete')
        ->add(Crud::PAGE_INDEX, Action::DETAIL)
        ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
            return $action
                ->setIcon('fa fa-magnifying-glass')
                ->setLabel(false)
                ->setHtmlAttributes([
                    'title' => 'Modifier cet élément',
                ])
                ->displayAsLink()
                ->addCssClass('btn btn-sm btn-light');
        });
    }

    /**
     * Configures the CRUD interface for the EntityHistory entity.
     *
     * Sets custom page titles for the index and detail views and
     * enables inline display of entity actions on the index page (though only 'DETAIL' is active).
     *
     * @param Crud $crud The Crud configuration object provided by EasyAdmin.
     * @return Crud The modified Crud configuration object.
     */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->showEntityActionsInlined()
            ->setPageTitle('index', 'Historique des modifications')
            ->setPageTitle('detail', 'Détails de la modification');
    }

    /**
     * Configures the fields displayed in the CRUD interface for the EntityHistory entity.
     *
     * This configuration applies to both the index and detail views, as editing is disabled.
     *
     * @param string $pageName The name of the page being configured (e.g., 'index', 'new', 'edit').
     * @return iterable An iterable collection of field configurations.
     */
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('entityName','Nom'),
            IntegerField::new('entityId','Identificateur'),
            TextField::new('action'),
            TextField::new('user','Utilisateur'),
            DateTimeField::new('dateAction','Date de modification'),
            ArrayField::new('oldValues', 'Anciennes valeurs'),
            ArrayField::new('newValues', 'Nouvelles valeurs'),
        ];
    }
}