<?php

namespace App\Controller\Admin;

use App\Entity\Faq;
use App\Repository\FaqRepository;
use App\Service\PublishService;
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

/**
 * FaqCrudController is responsible for managing the CRUD operations for the Faq entity.
 * It extends AbstractCrudController to leverage EasyAdmin's functionality.
 * 
 * This controller customizes the default CRUD operations for FAQs, including:
 * - Custom actions for reordering (move up, down, top, bottom).
 * - Custom actions for publishing and unpublishing FAQs.
 * - Configuration of fields displayed in forms and index pages.
 * - Custom labels, titles, and templates.
 */
class FaqCrudController extends AbstractCrudController
{
    private PublishService $publishService;
    private PositionService $positionService;
    private FaqRepository $faqRepository;

    /**
     * FaqCrudController constructor.
     *
     * Initializes the controller with the necessary services.
     *
     * @param FaqRepository $faqRepository The repository for the Faq entity.
     * @param PublishService $publishService The service for handling publish actions.
     * @param PositionService $positionService The service for handling position actions.
     */
    public function __construct(FaqRepository $faqRepository, PublishService $publishService, PositionService $positionService) 
    {
        $this->faqRepository = $faqRepository;
        $this->publishService = $publishService;
        $this->positionService = $positionService;
    }

    /**
     * Returns the fully qualified class name of the entity managed by this controller.
     *
     * This method is used by EasyAdmin to determine which entity this controller is responsible for.
     *
     * @return string The fully qualified class name of the Faq entity.
     */
    public static function getEntityFqcn(): string
    {
        return Faq::class;
    }

    /**
     * Configures the actions available in the CRUD interface.
     *
     * This method sets custom labels and icons for actions such as New, Save, Edit, and Delete
     * and adds custom actions for moving FAQs up, down, to the top, and to the bottom.
     * It also configures publish and unpublish actions with specific conditions for display.
     *
     * @param Actions $actions The actions configuration object.
     * @return Actions The modified actions configuration object.
     */
    public function configureActions(Actions $actions): Actions
    {
        $entityCount = $this->faqRepository->count([]);

        // Define custom actions for moving FAQs
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

        // Define custom actions for publishing and unpublishing FAQs
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

    // Add the custom actions to the actions configuration and customize existing actions.
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

    /**
     * Configures the CRUD interface for the Faq entity.
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
        ->overrideTemplate('crud/index', 'admin/faq_index.html.twig')
        ->setEntityLabelInSingular('Question/Réponse')
        ->setEntityLabelInPlural('Questions/Réponses')
        ->setPageTitle('new', 'Ajouter une nouvelle question/réponse')
        ->setDefaultSort(['positionFaq' => 'ASC'])
        ->showEntityActionsInlined();
    }

    /**
     * Configures the fields displayed in the CRUD interface for the Faq entity.
     *
     * This method defines the fields to be displayed in the index, detail, edit, and new pages.
     *
     * @param string $pageName The name of the page being configured (e.g., 'index', 'new', 'edit').
     * @return iterable An iterable collection of field configurations.
     */
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

    /**
     * Custom action to move the FAQ to the top of the list.
     *
     * This method uses the PositionService to handle the movement logic
     * and redirects back to the index page with a success message.
     *
     * @param AdminContext $context The admin context containing the entity to move.
     * @return Response A redirect response to the index page with a success flash message.
     */
    public function moveTop(AdminContext $context): Response
    {
        $result = $this->positionService->move($context, Direction::Top);
        $this->addFlash('success', $result['message']);
        return $this->redirect($result['redirect_url']);
    }
    
    /**
     * Custom action to move the FAQ up in the list.
     *
     * This method uses the PositionService to handle the movement logic
     * and redirects back to the index page with a success message.
     *
     * @param AdminContext $context The admin context containing the entity to move.
     * @return Response A redirect response to the index page with a success flash message.
     */
    public function moveUp(AdminContext $context): Response
    {
        $result = $this->positionService->move($context, Direction::Up);
        $this->addFlash('success', $result['message']);
        return $this->redirect($result['redirect_url']);
    }
    
    /**
     * Custom action to move the FAQ down in the list.
     *
     * This method uses the PositionService to handle the movement logic
     * and redirects back to the index page with a success message.
     *
     * @param AdminContext $context The admin context containing the entity to move.
     * @return Response A redirect response to the index page with a success flash message.
     */
    public function moveDown(AdminContext $context): Response
    {
        $result = $this->positionService->move($context, Direction::Down);
        $this->addFlash('success', $result['message']);
        return $this->redirect($result['redirect_url']);
    }
    
    /**
     * Custom action to move the FAQ to the bottom of the list.
     *
     * This method uses the PositionService to handle the movement logic
     * and redirects back to the index page with a success message.
     *
     * @param AdminContext $context The admin context containing the entity to move.
     * @return Response A redirect response to the index page with a success flash message.
     */
    public function moveBottom(AdminContext $context): Response
    {
        $result = $this->positionService->move($context, Direction::Bottom);
        $this->addFlash('success', $result['message']);
        return $this->redirect($result['redirect_url']);
    }

    /**
     * Custom action to publish the FAQ.
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
        $this->addFlash('success', 'FAQ publiée avec succès');
        return $this->redirect($url);
    }

    /**
     * Custom action to unpublish the FAQ.
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
        $this->addFlash('success', 'FAQ dépubliée avec succès');
        return $this->redirect($url);
    }
}