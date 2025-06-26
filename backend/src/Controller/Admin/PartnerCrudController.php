<?php

namespace App\Controller\Admin;

use App\Entity\Partner;
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

/*
 * PartnerCrudController is responsible for managing the CRUD operations for Partner entities in the admin panel.
 * It extends AbstractCrudController to leverage EasyAdmin's functionality.
 * 
 * This cotroller customizes the default CRUD operations for the Partner entity, including:
 * - Custom actions for publishing and unpublishing Partners.
 * - Configuration of fields displayed in forms and index pages.
 * - Custom labels, titles, and templates.
 */
class PartnerCrudController extends AbstractCrudController
{
    private EntityManagerInterface $entityManager;
    private AdminUrlGenerator $adminUrlGenerator;
    private PublishService $publishService;
    private string $projectDir;

    /**
     * PartnerCrudController constructor.
     *
     * Initializes the controller with the EntityManagerInterface, AdminUrlGenerator,
     * project directory, and PublishService.
     *
     * @param EntityManagerInterface $entityManager The Doctrine entity manager.
     * @param AdminUrlGenerator $adminUrlGenerator The admin URL generator for generating URLs.
     * @param string $projectDir The project directory path.
     * @param PublishService $publishService The service for handling publish actions.
     */
    public function __construct(EntityManagerInterface $entityManager, AdminUrlGenerator $adminUrlGenerator, #[Autowire('%kernel.project_dir%')] string $projectDir, PublishService $publishService)
    {
        $this->entityManager = $entityManager;
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->projectDir = $projectDir;
        $this->publishService = $publishService;
    }

    /**
     * Returns the fully qualified class name of the entity managed by this controller.
     * 
     * This method is used by EasyAdmin to determine which entity this controller is responsible for.
     *
     * @return string The fully qualified class name of the Partner entity.
     */
    public static function getEntityFqcn(): string
    {
        return Partner::class;
    }

    /**
     * Configures the actions available for the Partner entity.
     *
     * This method sets custom labels and icons for actions such as New, Save, Edit and Delete
     * and add custom actions for publishing and unpublishing partners.
     * It also configures publish and unpublish actions with specific conditions for display.
     *
     * @param Actions $actions The actions configuration object.
     * @return Actions The configured actions object.
     */
    public function configureActions(Actions $actions): Actions
    {
    // Define custom actions for publishing and unpublishing partners  
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

    // Add the custom actions to the actions configuration and customize existing actions.
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

    /**
     * Configures the CRUD interface for the Partner entity.
     *
     * This method sets the form theme, entity labels, page titles, and inline actions.
     *
     * @param Crud $crud The CRUD configuration object.
     * @return Crud The configured CRUD object.
     */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
        ->addFormTheme('admin/form.html.twig')
        ->setEntityLabelInSingular('Partenaire')
        ->setEntityLabelInPlural('Partenaires')
        ->setPageTitle('new', 'Ajouter un nouveau partenaire')
        ->showEntityActionsInlined();
    }

    /**
     * Configures the fields displayed in the CRUD interface for the Partner entity.
     *
     * This method defines the fields to be displayed in the index, detail, edit, and new pages.
     *
     * @param string $pageName The name of the page being configured (e.g., 'index', 'new', 'edit').
     * @return iterable An iterable collection of field configurations.
     */
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
            ImageField::new('imagePartner',($pageName === Crud::PAGE_INDEX ? 'logo' :'Télécharger le logo du partenaire'))
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

    /**
     * Custom action to publish the Partner.     * 
     *
     * This method uses the PublishService to handle the publishing logic
     * and redirects back to the index page with a success flash message.
     *
     * @param AdminContext $context The admin context containing the entity to publish.
     * @return Response A redirect response to the index page with a success flash message.
     */
    public function publish(AdminContext $context): Response
    {
        $result = $this->publishService->publish($context);
        $url = $result['url'];
        $this->addFlash('success', 'Partenaire publié avec succès');
        return $this->redirect($url);
    }

    /**
     * Custom action to unpublish the Partner.
     *
     * This method uses the PublishService to handle the unpublishing logic
     * and redirects back to the index page with a success flash message.
     *
     * @param AdminContext $context The admin context containing the entity to unpublish.
     * @return Response A redirect response to the index page with a success flash message.
     */
    public function unpublish(AdminContext $context): Response
    {
        $result = $this->publishService->unpublish($context);
        $url = $result['url'];
        $this->addFlash('success', 'Partenaire dépublié avec succès');        
        return $this->redirect($url);
    }
}
