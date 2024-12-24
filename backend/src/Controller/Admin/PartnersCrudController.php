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
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;


class PartnersCrudController extends AbstractCrudController
{
    private EntityManagerInterface $entityManager;
    private AdminUrlGenerator $adminUrlGenerator;
    private string $projectDir;
    
    // injection du service EntityManagerInterface
    public function __construct(EntityManagerInterface $entityManager, AdminUrlGenerator $adminUrlGenerator, #[Autowire('%kernel.project_dir%')] string $projectDir)
    {
        $this->entityManager = $entityManager;
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->projectDir = $projectDir;
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
        // Determines the upload path according to the environment
        $isProduction = str_contains($this->projectDir, 'public_html/symfony');
        $uploadPath = $isProduction ? '../admin/uploads/partners' : 'public/uploads/partners';
        $fields = [
            IntegerField::new('id', 'Identifiant')->onlyOnIndex(),
            TextField::new('name','Nom du partenaire')
                ->setFormTypeOptions([
                    'attr' => ['placeholder' => 'Saississez le nom du partenaire'],
                ]),
            ImageField::new('image',($pageName === Crud::PAGE_INDEX ? 'logo' :'Télécharger le logo du partenaire'))
                ->setUploadDir($uploadPath)
                ->setBasePath('uploads/partners')
                ->setUploadedFileNamePattern('[name][randomhash].[extension]')
                ->setHelp(sprintf('<span style="font-weight: 600; color: blue;"><i class="fa fa-circle-info"></i>&nbsp;L\'image sera automatiquement converti à une hauteur de 128px et en format webp.</span>'))
                ->setFormTypeOption('required' , ($pageName === Crud::PAGE_NEW ? true : false)),
            TextField::new('url','URL du site du partenaire')
                ->setFormTypeOptions([
                    'attr' => ['placeholder' => 'Saississez l\'url du site du partenaire'],
                ]),
        ];

        if ($pageName === Crud::PAGE_INDEX || $pageName === Crud::PAGE_DETAIL) {             
            $fields[] = TextField::new('type.type', 'Type de partenaire');
        } else {
            $addTypeUrl = $this->adminUrlGenerator
            ->setController(PartnerTypeCrudController::class)
            ->setAction(Action::NEW)
            ->generateUrl();
            $fields[] = AssociationField::new('type', 'Type de partenaire')
                ->setFormTypeOptions([
                    'placeholder' => 'Selectionnez le type de partenaire',
                    'choice_label' => 'type',
                ])
                ->setHelp(sprintf('Pas de type adapté ? <a href="%s">Créer un nouveau type</a>', $addTypeUrl));
        }
        $fields[] = BooleanField::new('publish','Publié');
        $fields[] = DateTimeField::new('dateModification', 'Dernière modification')->onlyOnIndex();
        $fields[] = TextField::new('userModification', 'Utilisateur')->onlyOnIndex(); 
        return $fields;
    }
}
