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

        $moveTop = Action::new('moveTop', false, 'fa fa-arrow-up')
            ->setHtmlAttributes(['title' => 'Mettre en haut de page'])
            ->linkToCrudAction('moveTop')
            ->displayIf(fn ($entity) => $entity->getPosition() > 0);
    
        $moveUp = Action::new('moveUp', false, 'fa fa-sort-up')
            ->setHtmlAttributes(['title' => 'Monter d\'un cran'])
            ->linkToCrudAction('moveUp')
            ->displayIf(fn ($entity) => $entity->getPosition() > 0);
    
        $moveDown = Action::new('moveDown', false, 'fa fa-sort-down')
            ->setHtmlAttributes(['title' => 'Descendre d\'un cran'])
            ->linkToCrudAction('moveDown')
            ->displayIf(fn ($entity) => $entity->getPosition() < $entityCount - 1);
    
        $moveBottom = Action::new('moveBottom', false, 'fa fa-arrow-down')
            ->setHtmlAttributes(['title' => 'Mettre en bas de page'])
            ->linkToCrudAction('moveBottom')
            ->displayIf(fn ($entity) => $entity->getPosition() < $entityCount - 1);

        $publishAction = Action::new('publish', 'Publier', 'fa fa-eye')
            ->addCssClass('btn btn-sm btn-light text-success')
            ->setLabel(false)
            ->displayIf(fn ($entity) => !$entity->isPublish())
            ->linkToCrudAction('publish')
            ->setHtmlAttributes([
                'title' => "Publier l'élément",
            ]);
    
        $unpublishAction = Action::new('unpublish', 'Dépublier', 'fa fa-eye-slash')
            ->addCssClass('btn btn-ms btn-light text-danger')
            ->setLabel(false)
            ->displayIf(fn ($entity) => $entity->isPublish())
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
        ->setDefaultSort(['position' => 'ASC'])
        ->showEntityActionsInlined();
    }
    
    public function configureFields(string $pageName): iterable
    {
        $fields = [
            IntegerField::new('id', 'Identifiant')->onlyOnIndex(),
            IntegerField::new('position', 'Position')->onlyOnIndex(),
            TextField::new('typeSection', 'Section')->hideOnForm(),
            AssociationField::new('typeSection', 'Section')->onlyOnForms()
            ->setFormTypeOption('choice_label', 'section')
            ->setQueryBuilder(function ($queryBuilder) {
                return $queryBuilder->orderBy('entity.section', 'ASC');
            }),
            TextField::new('titre',($pageName === Crud::PAGE_INDEX ? 'Titre' : 'Titre de la carte (faire un titre court)')),
            TextField::new('description', 'Texte')
                ->hideOnForm()
                ->stripTags(),
            TextEditorField::new('description','Texte de la carte')
                ->onlyOnForms()
                ->setTrixEditorConfig(['blockAttributes' => [
                    'default' => ['tagName' => 'p'],],]),
            BooleanField::new('publish','Publié')
                ->renderAsSwitch(false),
            DateTimeField::new('dateModification', 'Dernière modification')->onlyOnIndex(),
            TextField::new('userModification', 'Utilisateur')->onlyOnIndex(),
        ];    
        return $fields;
    }

    public function moveTop(AdminContext $context)
    {
        $this->positionService->move($context, Direction::Top);
        $this->addFlash('success', 'l\'élément a bien été déplacé en haut de page.');
        return $this->redirect($context->getRequest()->headers->get('referer'));
    }
    
    public function moveUp(AdminContext $context)
    {
        $this->positionService->move($context, Direction::Up);
        $this->addFlash('success', 'l\'élément a bien été déplacé d\'un cran en haut.');
        return $this->redirect($context->getRequest()->headers->get('referer'));
    }
    
    public function moveDown(AdminContext $context)
    {
        $this->positionService->move($context, Direction::Down);
        $this->addFlash('success', 'l\'élément a bien été déplacé d\'un cran en bas.');
        return $this->redirect($context->getRequest()->headers->get('referer'));
    }
    
    public function moveBottom(AdminContext $context)
    {
        $this->positionService->move($context, Direction::Bottom);
        $this->addFlash('success', 'l\'élément a bien été déplacé en bas de page.');
        return $this->redirect($context->getRequest()->headers->get('referer'));
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