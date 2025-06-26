<?php

namespace App\Controller\Admin;

use App\Entity\Artist;
use App\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * ArtistCrudController is responsible for managing the CRUD operations for the Artist entity.
 * It extends AbstractCrudController to leverage EasyAdmin's functionality.
 * 
 * This controller customizes the default CRUD operations for FAQs, including:
 * - Configuration of fields displayed in forms and index pages.
 * - Custom labels, titles, and templates.
 */
class ArtistCrudController extends AbstractCrudController
{
    private CacheManager $cacheManager;
    private EntityManagerInterface $entityManager;
    private string $projectDir;

    /**
     * ArtistCrudController constructor.
     *
     * Initializes the controller with the necessary services.
     *
     * @param CacheManager $cacheManager LiipImagineBundle's cache manager, potentially for cache invalidation.
     * @param EntityManagerInterface $entityManager The Doctrine Entity Manager.
     * @param string $projectDir The project's root directory, autowired.
     */
    public function __construct(CacheManager $cacheManager, EntityManagerInterface $entityManager, #[Autowire('%kernel.project_dir%')] string $projectDir)
    {
        $this->cacheManager = $cacheManager;
        $this->entityManager = $entityManager;
        $this->projectDir = $projectDir;
    }

    /**
     * Returns the fully qualified class name of the entity managed by this controller.
     *
     * This method is used by EasyAdmin to determine which entity this controller is responsible for.
     *
     * @return string The fully qualified class name of the Artist entity.
     */
    public static function getEntityFqcn(): string
    {
        return Artist::class;
    }

    /**
     * Configures the actions available in the CRUD interface.
     *
     * This method sets custom labels and icons for actions such as New, Save, Edit, and Delete.
     *
     * @param Actions $actions The actions configuration object.
     * @return Actions The modified actions configuration object.
     */
    public function configureActions(Actions $actions): Actions
    {
    return $actions
        ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
            return $action->setLabel('Ajouter un artiste');
        })
        ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, function (Action $action) {
            return $action->setLabel('Créer un artiste');
        })
        ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, function (Action $action) {
            return $action->setLabel('Créer et ajouter un autre artiste');
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
     * Configures the CRUD interface for the Artist entity.
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
        ->setEntityLabelInSingular('Artiste')
        ->setEntityLabelInPlural('Artistes')
        ->setPageTitle('new', 'Ajouter un nouvel artiste')
        ->showEntityActionsInlined();
    }

    /**
     * Configures the fields displayed in the CRUD interface for the Artist entity.
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
        $uploadPath = $isProduction ? '../admin/uploads/artists' : 'public/uploads/artists';
        $fields = [
            IntegerField::new('idArtist', 'Identifiant')->onlyOnIndex(),
            TextField::new('nameArtist', 'Nom de l\'artiste ou du groupe')
                ->setFormTypeOptions([
                    'attr' => ['placeholder' => 'Saisissez le nom de l\'artiste'],
                ]),
            TextareaField::new('contentArtist', 'Description' . ($pageName === Crud::PAGE_INDEX ? '' : ' de l\'artiste ou du groupe'))
                ->setFormTypeOptions([
                    'attr' => ['placeholder' => 'Saisissez la description de l\'artiste'],
                ]),
            ImageField::new('imageArtist','Image'. ($pageName === Crud::PAGE_INDEX ? '' : ' de l\'artiste ou du groupe'))
                ->setUploadDir($uploadPath)
                ->setBasePath('uploads/artists')
                ->setUploadedFileNamePattern('[name][randomhash].[extension]')
                ->setHelp(sprintf('<span style="font-weight: 600; color: blue;"><i class="fa fa-circle-info"></i>&nbsp;L\'image sera automatiquement converti en format webp avec une hauteur de 768 pixels.'))
                ->setFormTypeOptions([
                    'required' => ($pageName === Crud::PAGE_NEW ? true : false),
                    'allow_delete'=> false
                ]),
            ImageField::new('thumbnail',' Miniature'. ($pageName === Crud::PAGE_INDEX ? '' : ' de l\'artiste ou du groupe'))
                ->setUploadDir($uploadPath)
                ->setBasePath('uploads/artists')
                ->setUploadedFileNamePattern('thumb_[name][randomhash].[extension]')
                ->setHelp(sprintf('<span style="font-weight: 600; color: blue;"><i class="fa fa-circle-info"></i>&nbsp;L\'image sera automatiquement converti en format webp avec une hauteur de 248 pixels. Privilégiez une image plutôt carré si possible.'))
                ->setFormTypeOptions([
                    'required' => false,
                    'allow_delete'=> false
                ]),
            TextField::new('typeMusic', 'Type de musique')
                ->setFormTypeOptions([
                    'attr' => ['placeholder' => 'Saisissez le type de musique de l\'artiste'],
                ]),
            DateTimeField::new('dateModificationArtist', 'Dernière modification')->onlyOnIndex(),
            TextField::new('userModificationArtist', 'Utilisateur')->onlyOnIndex(),
        ];

        return $fields;
    } 

    /**
     * Deletes the entity instance from the database.
     *
     * This method prevents the deletion of an Artist if they are currently associated with one or more Event entities. 
     * If they are, it prevents deletion and sets an appropriate flash message.
     * 
     * @param AdminContext $context The admin context containing the entity to delete.
     * @return mixed The result of the delete operation or a redirect response.
     */
    public function delete(AdminContext $context)
    {
        $artist = $context->getEntity()->getInstance();

        // Verify if there are related Following items
        $hasRelatedItems = $this->entityManager->getRepository(Event::class)
            ->count(['artist' => $artist]) > 0;

        // If related Event entities exist, prevent the deletion of this Artist.
        if ($hasRelatedItems) {
            $this->addFlash('danger', 'Impossible de supprimer cet élément car il est lié à un ou plusieurs éléments Évènements. il faut d\'abord supprimer ou reaffecter les éléméents Évènements concernés');
            
            $url = $this->container->get(AdminUrlGenerator::class)
                ->setController(self::class)
                ->setAction(Action::INDEX)
                ->generateUrl();

            return $this->redirect($url);
        }

        return parent::delete($context);
    }
}