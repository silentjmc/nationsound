<?php

namespace App\Controller\Admin;

use App\Entity\Information;
use App\Entity\InformationSection;
use App\Repository\InformationSectionRepository;
use App\Service\Direction;
use App\Service\PositionService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class InformationSectionCrudController extends AbstractCrudController
{
    private EntityManagerInterface $entityManager;
    private PositionService $positionService;

    public function __construct(EntityManagerInterface $entityManager, PositionService $positionService,private readonly InformationSectionRepository $informationSectionRepository)
    {
        $this->entityManager = $entityManager;
        $this->positionService = $positionService;
        
    }

    public static function getEntityFqcn(): string
    {
        return InformationSection::class;
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
    return $actions
        ->add(Crud::PAGE_INDEX, $moveBottom)
        ->add(Crud::PAGE_INDEX, $moveDown)
        ->add(Crud::PAGE_INDEX, $moveUp)
        ->add(Crud::PAGE_INDEX, $moveTop)
        ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
            return $action->setLabel('Ajouter une nouvelle section');
        })
        ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, function (Action $action) {
            return $action->setLabel('Créer la section');
        })
        ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, function (Action $action) {
            return $action->setLabel('Créer et ajouter une autre section');
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
        ->setEntityLabelInSingular('Section d\'information')
        ->setEntityLabelInPlural('Section d\'information')
        ->setPageTitle('new', 'Ajouter une nouvelle section')
        ->setDefaultSort(['position' => 'ASC'])
        ->showEntityActionsInlined();
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            IntegerField::new('id', 'Identifiant')->onlyOnIndex(),
            IntegerField::new('position', 'Position')->onlyOnIndex(),
            TextField::new('section',($pageName === Crud::PAGE_INDEX ? 'Section' : 'Section (partie dans la page informations)')),
            TextField::new('title',($pageName === Crud::PAGE_INDEX ? 'Titre' : 'Titre dans la page informations')),
            TextareaField::new('description',($pageName === Crud::PAGE_INDEX ? 'Sous-texte' : 'Sous-texte de la section dans la page Informations')),
            DateTimeField::new('dateModification', 'Dernière modification')->onlyOnIndex(),
            TextField::new('userModification', 'Utilisateur')->onlyOnIndex(),
        ];
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

    public function delete(AdminContext $context)
    {
        /** @var PartnerType $principal */
        $section = $context->getEntity()->getInstance();

        // Vérifier s'il existe des éléments Suivant liés
        $hasRelatedItems = $this->entityManager->getRepository(Information::class)
            ->count(['typeSection' => $section]) > 0;

        if ($hasRelatedItems) {
            $this->addFlash('danger', 'Impossible de supprimer cet élément car il est lié à un ou plusieurs éléments Informations. il faut d\'abord supprimer ou reaffecter les éléménts Informations concernés');
            
            $url = $this->container->get(AdminUrlGenerator::class)
                ->setController(self::class)
                ->setAction(Action::INDEX)
                ->generateUrl();

            return $this->redirect($url);
        }

        return parent::delete($context);
    }

}
