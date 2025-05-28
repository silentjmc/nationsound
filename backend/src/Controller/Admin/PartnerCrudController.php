<?php

namespace App\Controller\Admin;

use App\Entity\Partner;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use App\Service\PublishService;

class PartnerCrudController extends AbstractCrudController
{
    private EntityManagerInterface $entityManager;
    private AdminUrlGenerator $adminUrlGenerator;
    private PublishService $publishService;
    private string $projectDir;
    
    // injection du service EntityManagerInterface
    public function __construct(EntityManagerInterface $entityManager, AdminUrlGenerator $adminUrlGenerator, #[Autowire('%kernel.project_dir%')] string $projectDir, PublishService $publishService)
    {
        $this->entityManager = $entityManager;
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->projectDir = $projectDir;
        $this->publishService = $publishService;
    }

    public static function getEntityFqcn(): string
    {
        return Partner::class;
    }

    public function configureActions(Actions $actions): Actions
    {
    // New actions     
    $publishAction = Action::new('publish', 'Publier', 'fa fa-eye')
        ->addCssClass('btn btn-sm btn-light text-success')
        ->setLabel(false)
        ->displayIf(fn ($entity) => !$entity->isPublishPartner())
        ->linkToCrudAction('publish')
        ->setHtmlAttributes([
            'title' => "Publier l'élément",
        ]);
    $unpublishAction = Action::new('unpublish', 'Dépublier', 'fa fa-eye-slash')
        ->addCssClass('btn btn-sm btn-light text-danger')
        ->setLabel(false)
        ->displayIf(fn ($entity) => $entity->isPublishPartner())
        ->linkToCrudAction('unpublish')
        ->setHtmlAttributes([
            'title' => "Dépublier l'élément",       
        ]);

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
        })
        ->add(Crud::PAGE_INDEX,$publishAction) 
        ->add(Crud::PAGE_INDEX,$unpublishAction);    
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
            IntegerField::new('idPartner', 'Identifiant')->onlyOnIndex(),
            TextField::new('namePartner','Nom du partenaire')
                ->setFormTypeOptions([
                    'attr' => ['placeholder' => 'Saississez le nom du partenaire'],
                ]),
            ImageField::new('imagepartner',($pageName === Crud::PAGE_INDEX ? 'logo' :'Télécharger le logo du partenaire'))
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
            $fields[] = TextField::new('typePartner', 'Type de partenaire');
        } else {
            $addTypeUrl = $this->adminUrlGenerator
            ->setController(PartnerTypeCrudController::class)
            ->setAction(Action::NEW)
            ->generateUrl();
            $fields[] = AssociationField::new('typePartner', 'Type de partenaire')
                ->setFormTypeOptions([
                    'placeholder' => 'Selectionnez le type de partenaire',
                    'choice_label' => 'titlePartnerType',
                ])
                 ->setQueryBuilder(function ($queryBuilder) {
                            return $queryBuilder->orderBy('entity.titlePartnerType', 'ASC');
                            })
                ->setHelp(sprintf('Pas de type adapté ? <a href="%s">Créer un nouveau type</a>', $addTypeUrl));
        }
        $fields[] = BooleanField::new('publishPartner','Publié')->renderAsSwitch(false)->onlyOnIndex();
        $fields[] = BooleanField::new('publishPartner','Publié')->renderAsSwitch(true)->hideOnIndex();
        $fields[] = DateTimeField::new('dateModificationPartner', 'Dernière modification')->onlyOnIndex();
        $fields[] = TextField::new('userModificationPartner', 'Utilisateur')->onlyOnIndex(); 
        return $fields;
    }

    public function publish(AdminContext $context): Response
    {
        $result = $this->publishService->publish($context);
        $url = $result['url'];
        $this->addFlash('success', 'Actualité publié avec succès');
        return $this->redirect($url);
    }

    public function unpublish(AdminContext $context): Response
    {
        $result = $this->publishService->unpublish($context);
        $url = $result['url'];
        $this->addFlash('success', 'Actualité dépublié avec succès');        
        return $this->redirect($url);
    }
}
