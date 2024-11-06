<?php

namespace App\Controller\Admin;

use App\Entity\Partners;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;


class PartnersCrudController extends AbstractCrudController
{
    private $entityManager;
    private $adminUrlGenerator;
    // injection du service EntityManagerInterface
    public function __construct(EntityManagerInterface $entityManager, AdminUrlGenerator $adminUrlGenerator)
    {
        $this->entityManager = $entityManager;
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

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
        ->setEntityLabelInSingular('Partenaire')
        ->setEntityLabelInPlural('Partenaires')
        ->setPageTitle('new', 'Ajouter un nouveau partenaire')
        ->showEntityActionsInlined();
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = [
            TextField::new('name','Nom du partenaire'),
            ImageField::new('image',($pageName === Crud::PAGE_INDEX ? 'logo' :'Télécharger le logo du partenaire'))
                ->setUploadDir('public/uploads/partners')
                ->setBasePath('uploads/partners')
                ->setUploadedFileNamePattern('[name][randomhash].[extension]')
                ->setHelp(sprintf('L\'image sera automatiquement converti à une hauteur de 128px et en format webp.'))
                ->setFormTypeOptions([
                    'required' => ($pageName === Crud::PAGE_NEW ? true : false),
                ]),
            TextField::new('url','URL du site du partenaire'),
        ];
        // Affiche le type de partenaire dans la liste des partenaires sans lien cliquable sinon dans la page de création garde le choix de liste
        if ($pageName === Crud::PAGE_INDEX || $pageName === Crud::PAGE_DETAIL) {             
            $fields[] = TextField::new('type.type', 'Type de partenaire');
        } else {
            $addTypeUrl = $this->adminUrlGenerator
            ->setController(PartnerTypeCrudController::class)
            ->setAction(Action::NEW)
            ->generateUrl();
            $fields[] = AssociationField::new('type', 'Type de partenaire')
                ->setFormTypeOption('placeholder', 'Choisissez le type de partenaire')
                ->setFormTypeOption('choice_label', 'type')
                ->setHelp(sprintf('Pas de type adapté ? <a href="%s">Créer un nouveau type</a>', $addTypeUrl));
        }
        $fields[] = BooleanField::new('publish','Publié');
        $fields[] = DateTimeField::new('dateModification', 'Dernière modification')->onlyOnIndex();
        $fields[] = TextField::new('userModification', 'Utilisateur')->onlyOnIndex();
        

        return $fields;
    
    }

}
