<?php

namespace App\Controller\Admin;

use App\Entity\EventLocation;
use App\Entity\LocationType;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * LocationTypeCrudController is responsible for managing the CRUD operations for LocationType.
 * It extends AbstractCrudController to leverage EasyAdmin's functionality.
 * 
 * This controller customizes the default CRUD operations for locationType, including:
 * - Configuration of fields displayed in forms and index pages.
 * - Custom labels, titles, and templates. 
 */
class LocationTypeCrudController extends AbstractCrudController
{
    private EntityManagerInterface $entityManager;
    private string $projectDir;

    /**
     * LocationTypeCrudController constructor.
     *
     * Initializes the controller with the necessary services.
     *
     * @param EntityManagerInterface $entityManager The Doctrine entity manager.
     * @param string $projectDir The project directory path.
     */
    public function __construct(EntityManagerInterface $entityManager, #[Autowire('%kernel.project_dir%')] string $projectDir)
    {
        $this->entityManager = $entityManager;
        $this->projectDir = $projectDir;
    }

    /**
     * Returns the fully qualified class name of the entity managed by this controller.
     * 
     * This method is used by EasyAdmin to determine which entity this controller is responsible for.
     *
     * @return string The fully qualified class name of the LocationType entity.
     */
    public static function getEntityFqcn(): string
    {
        return LocationType::class;
    }

    /**
     * Configures the actions available in the CRUD interface.
     *
     * This method sets custom labels and icons for actions such as New, Save, Edit, and Delete.
     *
     * @param Actions $actions The actions configuration object.
     * @return Actions The configured actions object.
     */
    public function configureActions(Actions $actions): Actions
    {
    return $actions
        ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
            return $action->setLabel('Ajouter un type de lieu');
        })
        ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, function (Action $action) {
            return $action->setLabel('Créer le type de lieu');
        })
        ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, function (Action $action) {
            return $action->setLabel('Créer et ajouter un autre type de lieu');
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

    /**
     * Configures the CRUD interface for the LocationType entity.
     *
     * This method sets the form theme, entity labels, page titles, and inlined actions.
     *
     * @param Crud $crud The CRUD configuration object.
     * @return Crud The modified CRUD configuration object.
     */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
        ->addFormTheme('admin/form.html.twig')
        ->setEntityLabelInSingular('Type de lieu')
        ->setEntityLabelInPlural('Type de lieux')
        ->setPageTitle('new', 'Ajouter un nouveau type de lieu')
        ->showEntityActionsInlined();
    }

    public mixed $historyRepository = true;

    /**
     * Configures the fields displayed in the CRUD interface for the LocationType entity.
     *
     * This method defines the fields to be displayed in the index, detail, edit, and new pages.
     *
     * @param string $pageName The name of the page being configured (e.g., index, new, edit).
     * @return iterable An iterable collection of field configurations.
     */
    public function configureFields(string $pageName): iterable 
    {
        // Determines the upload path according to the environment
        $isProduction = str_contains($this->projectDir, 'public_html/symfony');
        $uploadPath = $isProduction ? '../admin/uploads/locations' : 'public/uploads/locations';
        $fields = [
            IntegerField::new('idLocationType', 'Identifiant')->onlyOnIndex(),
            TextField::new('nameLocationType','Type de lieu')
                ->setFormTypeOptions([
                    'attr' => ['placeholder' => 'Saississez le type de lieu'],
                ]),
            ImageField::new('symbol',($pageName === Crud::PAGE_INDEX ? 'symbole' : 'Télécharger le symbole représentant le lieu sur la carte'))
                ->setUploadDir($uploadPath)
                ->setBasePath('uploads/locations')
                ->setUploadedFileNamePattern('[name][randomhash].[extension]')
                ->setHelp(sprintf('<span style="font-weight: 600; color: blue;"><i class="fa fa-circle-info"></i>&nbsp;L\'image sera automatiquement converti en format png avec une hauteur de 24 pixels. Privilégiez une image plutôt carré avec un fond transparent si posdible.'))
                ->setFormTypeOption('required' , ($pageName === Crud::PAGE_NEW ? true : false)),
            DateTimeField::new('dateModificationLocationType', 'Dernière modification')->onlyOnIndex(),
            TextField::new('userModificationLocationType', 'Utilisateur')->onlyOnIndex()
        ];       
        return $fields;
    }

    /**
     * Deletes the entity instance from the database.
     *
     * This method checks if the LocationType is associated with any EventLocation entities.
     * If they are, it prevents deletion and sets an appropriate flash message.
     *
     * @param AdminContext $context The admin context containing the entity to deleted.
     * @return mixed The result of the delete operation or a redirect response
     * 
     */
    public function delete(AdminContext $context)
    {
        $locationType = $context->getEntity()->getInstance();

        // Verify if there are related items
        $hasRelatedItems = $this->entityManager->getRepository(EventLocation::class)
            ->count(['typeLocation' => $locationType]) > 0;

        // If there are related items, prevent deletion and display an error message
        if ($hasRelatedItems) {
            $this->addFlash('danger', 'Impossible de supprimer cet élément car il est lié à un ou plusieurs éléments Lieux. il faut d\'abord supprimer ou reaffecter les éléméents Lieux concernés');
            $url = $this->container->get(AdminUrlGenerator::class)
                ->setController(self::class)
                ->setAction(Action::INDEX)
                ->generateUrl();
            return $this->redirect($url);
        }
        return parent::delete($context);
    }
}