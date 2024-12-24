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

class ArtistCrudController extends AbstractCrudController
{
    private CacheManager $cacheManager;
    private EntityManagerInterface $entityManager;
    private string $projectDir;

    public function __construct(CacheManager $cacheManager, EntityManagerInterface $entityManager, #[Autowire('%kernel.project_dir%')] string $projectDir)
    {
        $this->cacheManager = $cacheManager;
        $this->entityManager = $entityManager;
        $this->projectDir = $projectDir;
    }

    public static function getEntityFqcn(): string
    {
        return Artist::class;
    }

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

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
        ->addFormTheme('admin/form.html.twig')
        ->setEntityLabelInSingular('Artiste')
        ->setEntityLabelInPlural('Artistes')
        ->setPageTitle('new', 'Ajouter un nouvel artiste')
        ->showEntityActionsInlined();
    }

    public function configureFields(string $pageName): iterable
    {
        // Determines the upload path according to the environment
        $isProduction = str_contains($this->projectDir, 'public_html/symfony');
        $uploadPath = $isProduction ? '../admin/uploads/artists' : 'public/uploads/artists';
        $fields = [
            IntegerField::new('id', 'Identifiant')->onlyOnIndex(),
            TextField::new('name', 'Nom de l\'artiste ou du groupe')
                ->setFormTypeOptions([
                    'attr' => ['placeholder' => 'Saisissez le nom de l\'artiste'],
                ]),
            TextareaField::new('description', 'Description' . ($pageName === Crud::PAGE_INDEX ? '' : ' de l\'artiste ou du groupe'))
                ->setFormTypeOptions([
                    'attr' => ['placeholder' => 'Saisissez la description de l\'artiste'],
                ]),
            ImageField::new('image','Image'. ($pageName === Crud::PAGE_INDEX ? '' : ' de l\'artiste ou du groupe'))
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
            TextField::new('type_music', 'Type de musique')
                ->setFormTypeOptions([
                    'attr' => ['placeholder' => 'Saisissez le type de musique de l\'artiste'],
                ]),
            DateTimeField::new('dateModification', 'Dernière modification')->onlyOnIndex(),
            TextField::new('userModification', 'Utilisateur')->onlyOnIndex(),
        ];

        return $fields;
    } 

    public function delete(AdminContext $context)
    {
        /** @var Artist $artist */
        $artist = $context->getEntity()->getInstance();

        // Verify if there are related Following items
        $hasRelatedItems = $this->entityManager->getRepository(Event::class)
            ->count(['artist' => $artist]) > 0;

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
