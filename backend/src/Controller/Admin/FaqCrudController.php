<?php

namespace App\Controller\Admin;

use App\Entity\Faq;
use App\Repository\FaqRepository;
use App\Service\PublishService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\Response;
use App\Service\Direction;
use App\Service\PositionService;

class FaqCrudController extends AbstractCrudController
{
    private EntityManagerInterface $entityManager;
    private PublishService $publishService;
    private PositionService $positionService;

    public function __construct(EntityManagerInterface $entityManager, private readonly FaqRepository $faqRepository, PublishService $publishService, PositionService $positionService) 
    {
        $this->entityManager = $entityManager;
        $this->publishService = $publishService;
        $this->positionService = $positionService;
    }

    public static function getEntityFqcn(): string
    {
        return Faq::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $entityCount = $this->faqRepository->count([]);

        // New actions
        $moveTop = Action::new('moveTop', false, 'fa fa-arrow-up')
            ->setHtmlAttributes(['title' => 'Move to top'])
            ->linkToCrudAction('moveTop')
            ->displayIf(fn ($entity) => $entity->getPositionFaq() > 0);
    
        $moveUp = Action::new('moveUp', false, 'fa fa-sort-up')
            ->setHtmlAttributes(['title' => 'Move up'])
            ->linkToCrudAction('moveUp')
            ->displayIf(fn ($entity) => $entity->getPositionFaq() > 0);
    
        $moveDown = Action::new('moveDown', false, 'fa fa-sort-down')
            ->setHtmlAttributes(['title' => 'Move down'])
            ->linkToCrudAction('moveDown')
            ->displayIf(fn ($entity) => $entity->getPositionFaq() < $entityCount - 1);
    
        $moveBottom = Action::new('moveBottom', false, 'fa fa-arrow-down')
            ->setHtmlAttributes(['title' => 'Move to bottom'])
            ->linkToCrudAction('moveBottom')
            ->displayIf(fn ($entity) => $entity->getPositionFaq() < $entityCount - 1);

        $publishAction = Action::new('publish', 'Publier', 'fa fa-eye')
            ->addCssClass('btn btn-sm btn-light text-success')
            ->setLabel(false)
            ->displayIf(fn ($entity) => !$entity->isPublishFaq())
            ->linkToCrudAction('publish')
            ->setHtmlAttributes([
                'title' => "Publier l'élément",
            ]);

        $unpublishAction = Action::new('unpublish', 'Dépublier', 'fa fa-eye-slash')
            ->addCssClass('btn btn-ms btn-light text-danger')
            ->setLabel(false)
            ->displayIf(fn ($entity) => $entity->isPublishFaq())
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
            return $action->setLabel('Ajouter une nouvelle question/réponse');
        })
        ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, function (Action $action) {
            return $action->setLabel('Créer la question/réponse');
        })
        ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, function (Action $action) {
            return $action->setLabel('Créer et ajouter une autre question/réponse');
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
        ->setEntityLabelInSingular('Question/Réponse')
        ->setEntityLabelInPlural('Questions/Réponses')
        ->setPageTitle('new', 'Ajouter une nouvelle question/réponse')
        ->setDefaultSort(['positionFaq' => 'ASC'])
        ->showEntityActionsInlined();
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IntegerField::new('idFaq', 'Identifiant')->onlyOnIndex(),
            IntegerField::new('positionFaq', 'position')->onlyOnIndex(),
            TextField::new('question')
                ->setFormTypeOptions([
                    'attr' => ['placeholder' => 'Saisissez une question'],
                ]),
            TextareaField::new('reponse')
                ->setFormTypeOptions([
                    'attr' => ['placeholder' => 'Saisissez la réponse à la question'],
                ]),
            BooleanField::new('publishFaq','Publié')->onlyOnIndex()
                ->renderAsSwitch(false),
            BooleanField::new('publishFaq','Publié')->hideOnIndex()
                ->renderAsSwitch(true),
            DateTimeField::new('dateModificationFaq', 'Dernière modification')->onlyOnIndex(),
            TextField::new('userModificationFaq', 'Utilisateur')->onlyOnIndex(),
        ];
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
        $this->addFlash('success', 'FAQ publié avec succès');
        return $this->redirect($url);
    }

    public function unpublish(AdminContext $context): Response
    {
        $result = $this->publishService->unpublish($context);
        $url = $result['url'];
        $this->addFlash('success', 'FAQ dépublié avec succès');
        return $this->redirect($url);
    }
}
