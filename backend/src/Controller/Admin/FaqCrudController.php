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

enum Direction
{
    case Top;
    case Up;
    case Down;
    case Bottom;
}

class FaqCrudController extends AbstractCrudController
{
    private EntityManagerInterface $entityManager;
    private PublishService $publishService;

    public function __construct(EntityManagerInterface $entityManager, private readonly FaqRepository $faqRepository, PublishService $publishService) 
    {
        $this->entityManager = $entityManager;
        $this->publishService = $publishService;
    }

    public static function getEntityFqcn(): string
    {
        return Faq::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $entityCount = $this->faqRepository->count([]);

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

        $moveTop = Action::new('moveTop', false, 'fa fa-arrow-up')
            ->setHtmlAttributes(['title' => 'Move to top'])
            ->linkToCrudAction('moveTop')
            ->displayIf(fn ($entity) => $entity->getPosition() > 0);
    
        $moveUp = Action::new('moveUp', false, 'fa fa-sort-up')
            ->setHtmlAttributes(['title' => 'Move up'])
            ->linkToCrudAction('moveUp')
            ->displayIf(fn ($entity) => $entity->getPosition() > 0);
    
        $moveDown = Action::new('moveDown', false, 'fa fa-sort-down')
            ->setHtmlAttributes(['title' => 'Move down'])
            ->linkToCrudAction('moveDown')
            ->displayIf(fn ($entity) => $entity->getPosition() < $entityCount - 1);
    
        $moveBottom = Action::new('moveBottom', false, 'fa fa-arrow-down')
            ->setHtmlAttributes(['title' => 'Move to bottom'])
            ->linkToCrudAction('moveBottom')
            ->displayIf(fn ($entity) => $entity->getPosition() < $entityCount - 1);
    return $actions
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
        })
        ->add(Crud::PAGE_INDEX,$publishAction) 
        ->add(Crud::PAGE_INDEX,$unpublishAction);    
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
        ->addFormTheme('admin/form.html.twig')
        ->overrideTemplate('crud/index', 'admin/faq_index.html.twig')
        ->setEntityLabelInSingular('Question/Réponse')
        ->setEntityLabelInPlural('Questions/Réponses')
        ->setPageTitle('new', 'Ajouter une nouvelle question/réponse')
        ->setDefaultSort(['position' => 'ASC'])
        ->showEntityActionsInlined();
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IntegerField::new('id', 'Identifiant')->onlyOnIndex(),
            IntegerField::new('position', 'position')->onlyOnIndex(),
            TextField::new('question'),
            TextareaField::new('reponse'),
            BooleanField::new('publish','Publié')
                ->renderAsSwitch(false),
            DateTimeField::new('dateModification', 'Dernière modification')->onlyOnIndex(),
            TextField::new('userModification', 'Utilisateur')->onlyOnIndex(),
        ];
    }


    public function moveTop(AdminContext $context): Response
    {
        return $this->move($context, Direction::Top);
    }
    
    public function moveUp(AdminContext $context): Response
    {
        return $this->move($context, Direction::Up);
    }
    
    public function moveDown(AdminContext $context): Response
    {
        return $this->move($context, Direction::Down);
    }
    
    public function moveBottom(AdminContext $context): Response
    {
        return $this->move($context, Direction::Bottom);
    }
    
    private function move(AdminContext $context, Direction $direction): Response
    {
        $object = $context->getEntity()->getInstance();
        $newPosition = match($direction) {
            Direction::Top => 0,
            Direction::Up => $object->getPosition() - 1,
            Direction::Down => $object->getPosition() + 1,
            Direction::Bottom => -1,
        };
    
        $object->setPosition($newPosition);
        $this->entityManager->flush();
    
        $this->addFlash('success', 'The element has been successfully moved.');
    
        return $this->redirect($context->getRequest()->headers->get('referer'));
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
            //$hasRelatedItems = $result['hasRelatedItems'];
            //if ($hasRelatedItems) {
            //    $this->addFlash('success', 'FAQ et événements liés dépubliés avec succès');
            //} else {
                $this->addFlash('success', 'FAQ dépublié avec succès');
            //}
            
            return $this->redirect($url);
        }
}
