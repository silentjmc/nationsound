<?php

namespace App\Controller\Admin;

use App\Entity\EntityHistory;
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

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('entityName','Nom'),
            IntegerField::new('entityId','Identificateur'),
            TextField::new('action'),
            TextField::new('user.fullname','Utilisateur'),
            DateTimeField::new('dateAction','Date'),
            ArrayField::new('oldValuesAsString', 'Anciennes valeurs')->onlyOnIndex(),
            ArrayField::new('newValuesAsString', 'Nouvelles valeurs')->onlyOnIndex(),
            
        ];
    }
}
