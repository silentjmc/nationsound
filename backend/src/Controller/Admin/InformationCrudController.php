<?php

namespace App\Controller\Admin;

use App\Entity\Information;
use App\Repository\InformationRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use App\Service\PositionService;
use App\Service\Direction;
use App\Service\PublishService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use Symfony\Component\HttpFoundation\Response;

class InformationCrudController extends AbstractCrudController
{
    private EntityManagerInterface $entityManager;
    private PositionService $positionService;
    private PublishService $publishService;

    public function __construct(EntityManagerInterface $entityManager, PositionService $positionService, PublishService $publishService, private readonly InformationRepository $informationSectionRepository)
    {
        $this->entityManager = $entityManager;
        $this->positionService = $positionService;
        $this->publishService = $publishService;
    }

    public static function getEntityFqcn(): string
    {
        return Information::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $entityCount = $this->informationSectionRepository->count([]);
        // New actions
        $moveTop = Action::new('moveTop', false, 'fa fa-arrow-up')
            ->setHtmlAttributes(['title' => 'Mettre en haut de page'])
            ->linkToCrudAction('moveTop')
            ->displayIf(fn ($entity) => $entity->getPositionInformation() > 0);
    
        $moveUp = Action::new('moveUp', false, 'fa fa-sort-up')
            ->setHtmlAttributes(['title' => 'Monter d\'un cran'])
            ->linkToCrudAction('moveUp')
            ->displayIf(fn ($entity) => $entity->getPositionInformation() > 0);
    
        $moveDown = Action::new('moveDown', false, 'fa fa-sort-down')
            ->setHtmlAttributes(['title' => 'Descendre d\'un cran'])
            ->linkToCrudAction('moveDown')
            ->displayIf(fn ($entity) => $entity->getPositionInformation() < $entityCount - 1);
    
        $moveBottom = Action::new('moveBottom', false, 'fa fa-arrow-down')
            ->setHtmlAttributes(['title' => 'Mettre en bas de page'])
            ->linkToCrudAction('moveBottom')
            ->displayIf(fn ($entity) => $entity->getPositionInformation() < $entityCount - 1);

        $publishAction = Action::new('publish', 'Publier', 'fa fa-eye')
            ->addCssClass('btn btn-sm btn-light text-success')
            ->setLabel(false)
            ->displayIf(fn ($entity) => !$entity->isPublishInformation())
            ->linkToCrudAction('publish')
            ->setHtmlAttributes([
                'title' => "Publier l'élément",
            ]);
    
        $unpublishAction = Action::new('unpublish', 'Dépublier', 'fa fa-eye-slash')
            ->addCssClass('btn btn-ms btn-light text-danger')
            ->setLabel(false)
            ->displayIf(fn ($entity) => $entity->isPublishInformation())
            ->linkToCrudAction('unpublish')
            ->setHtmlAttributes([
                'title' => "Dépublier l'élément",       
            ]);
        
        return $actions
            ->add(Crud::PAGE_INDEX, $publishAction) 
            ->add(Crud::PAGE_INDEX, $unpublishAction)
            ->add(Crud::PAGE_INDEX, $moveBottom)
            ->add(Crud::PAGE_INDEX, $moveDown)
            ->add(Crud::PAGE_INDEX, $moveUp)
            ->add(Crud::PAGE_INDEX, $moveTop)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('Ajouter une information');
            })
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action->setLabel('Créer l\'information');
            })
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, function (Action $action) {
                return $action->setLabel('Créer et ajouter une autre information');
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
        ->overrideTemplate('crud/index', 'admin/faq_index.html.twig')
        ->setEntityLabelInSingular('Information')
        ->setEntityLabelInPlural('Informations')
        ->setPageTitle('new', 'Ajouter une nouvelle information')
        ->setDefaultSort(['positionInformation' => 'ASC'])
        ->showEntityActionsInlined();
    }
    
    public function configureFields(string $pageName): iterable
    {
        $fields = [
            IntegerField::new('idInformation', 'Identifiant')->onlyOnIndex(),
            IntegerField::new('positionInformation', 'Position')->onlyOnIndex(),
            TextField::new('sectionInformation', 'Section')->hideOnForm(),
            AssociationField::new('sectionInformation', 'Section')->onlyOnForms()
                ->setFormTypeOptions([
                    'choice_label' => 'sectionLabel',
                    'placeholder' => 'Sélectionnez une section',
                ])
            ->setQueryBuilder(function ($queryBuilder) {
                return $queryBuilder->orderBy('entity.sectionLabel', 'ASC');
            }),
            TextField::new('titleInformation',($pageName === Crud::PAGE_INDEX ? 'Titre' : 'Titre de l\'information (faire un titre court)'))
                ->setFormTypeOptions([
                    'attr' => ['placeholder' => 'Saisissez le titre de l\'information'],
                ]),
            TextField::new('contentInformation', 'Texte')
                ->hideOnForm()
                ->stripTags(),
            TextEditorField::new('contentInformation','Contenu de l\'information')
                ->onlyOnForms()
                ->setFormTypeOptions([
                    'attr' => ['placeholder' => 'Saisissez le contenu de l\'information'],
                    ])
                ->setTrixEditorConfig(['blockAttributes' => [
                    'default' => ['tagName' => 'p'],],]),
            BooleanField::new('publishInformation','Publié')->onlyOnIndex()
                ->renderAsSwitch(false),
            BooleanField::new('publishInformation','Publié')->hideOnIndex()
                ->renderAsSwitch(true),
            DateTimeField::new('dateModificationInformation', 'Dernière modification')->onlyOnIndex(),
            TextField::new('userModificationInformation', 'Utilisateur')->onlyOnIndex(),
        ];    
        return $fields;
    }

public function moveTop(AdminContext $context): Response
    {
        $result = $this->positionService->move($context, Direction::Top);
        $this->addFlash('success', $result['message']);
        return $this->redirect($result['redirect_url']);
    }
    
    public function moveUp(AdminContext $context): Response
    {
        $result = $this->positionService->move($context, Direction::Up);
        $this->addFlash('success', $result['message']);
        return $this->redirect($result['redirect_url']);
    }
    
    public function moveDown(AdminContext $context): Response
    {
        $result = $this->positionService->move($context, Direction::Down);
        $this->addFlash('success', $result['message']);
        return $this->redirect($result['redirect_url']);
    }
    
    public function moveBottom(AdminContext $context): Response
    {
        $result = $this->positionService->move($context, Direction::Bottom);
        $this->addFlash('success', $result['message']);
        return $this->redirect($result['redirect_url']);
    }
    public function publish(AdminContext $context): Response
    {
        $result = $this->publishService->publish($context);
        $url = $result['url'];
        $this->addFlash('success', 'Information publié avec succès');
        return $this->redirect($url);
    }

    public function unpublish(AdminContext $context): Response
    {
        $result = $this->publishService->unpublish($context);
        $url = $result['url'];
        $this->addFlash('success', 'Information dépublié avec succès');            
        return $this->redirect($url);
    }
}