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


class EntityHistoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return EntityHistory::class;
    }

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

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->showEntityActionsInlined()
            ->setPageTitle('index', 'Historique des modifications')
            ->setPageTitle('detail', 'Détails de la modification');
    }

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
