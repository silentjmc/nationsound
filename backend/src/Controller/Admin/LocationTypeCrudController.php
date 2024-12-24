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
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class LocationTypeCrudController extends AbstractCrudController
{
    private EntityManagerInterface $entityManager;
    private string $projectDir;

    public function __construct(EntityManagerInterface $entityManager, #[Autowire('%kernel.project_dir%')] string $projectDir)
    {
        $this->entityManager = $entityManager;
        $this->projectDir = $projectDir;
    }

    public static function getEntityFqcn(): string
    {
        return LocationType::class;
    }

    public function configureActions(Actions $actions): Actions
    {
    return $actions
        ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
            return $action->setLabel('Ajouter un typer de lieu');
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

    public function configureFields(string $pageName): iterable 
    {
        // Determines the upload path according to the environment
        $isProduction = str_contains($this->projectDir, 'public_html/symfony');
        $uploadPath = $isProduction ? '../admin/uploads/locations' : 'public/uploads/locations';
        $fields = [
            IntegerField::new('id', 'Identifiant')->onlyOnIndex(),
            TextField::new('type','Type de lieu')
                ->setFormTypeOptions([
                    'attr' => ['placeholder' => 'Saississez le type de lieu'],
                ]),
            ImageField::new('symbol',($pageName === Crud::PAGE_INDEX ? 'symbole' : 'Télécharger le symbole représentant le lieu sur la carte'))
                ->setUploadDir($uploadPath)
                ->setBasePath('uploads/locations')
                ->setUploadedFileNamePattern('[name][randomhash].[extension]')
                ->setHelp(sprintf('<span style="font-weight: 600; color: blue;"><i class="fa fa-circle-info"></i>&nbsp;L\'image sera automatiquement converti en format png avec une hauteur de 24 pixels. Privilégiez une image plutôt carré avec un fond transparent si posdible.'))
                ->setFormTypeOption('required' , ($pageName === Crud::PAGE_NEW ? true : false)),
            BooleanField::new('eventHostable', 'Lieu d\'événement')->renderAsSwitch(false)->onlyOnIndex(),
            BooleanField::new('eventHostable', 'Lieu d\'événement')
                ->setHelp('Ce type de lieu peut-il accueillir des événements ?'),
            DateTimeField::new('dateModification', 'Dernière modification')->onlyOnIndex(),
            TextField::new('userModification', 'Utilisateur')->onlyOnIndex()
        ];       
        return $fields;
    }
    public function delete(AdminContext $context)
    {
        /** @var LocationType $locationType */
        $locationType = $context->getEntity()->getInstance();

        // Verify if there are related items
        $hasRelatedItems = $this->entityManager->getRepository(EventLocation::class)
            ->count(['typeLocation' => $locationType]) > 0;

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










