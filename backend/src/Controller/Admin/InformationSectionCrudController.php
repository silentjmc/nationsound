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
use Symfony\Component\HttpFoundation\Response;

/*
 * InformationSectionCrudController is responsible for managing InformationSection entities in the admin panel.
 * It extends AbstractCrudController to provide CRUD operations for InformationSection entities.
 * It includes custom configurations for fields, actions, and entity updates.
 */
class InformationSectionCrudController extends AbstractCrudController
{
    private EntityManagerInterface $entityManager;
    private PositionService $positionService;

    /**
     * InformationSectionCrudController constructor.
     *
     * Initializes the controller with the EntityManagerInterface.
     *
     * @param EntityManagerInterface $entityManager The Doctrine entity manager.
     * @param PositionService $positionService The service for managing positions of sections.
     * @param InformationSectionRepository $informationSectionRepository The repository for InformationSection entities.
     */
    public function __construct(EntityManagerInterface $entityManager, PositionService $positionService,private readonly InformationSectionRepository $informationSectionRepository)
    {
        $this->entityManager = $entityManager;
        $this->positionService = $positionService;
        
    }

    /**
     * Returns the fully qualified class name of the entity managed by this controller.
     *
     * @return string The fully qualified class name of the InformationSection entity.
     */
    public static function getEntityFqcn(): string
    {
        return InformationSection::class;
    }

    /**
     * Configures the actions available in the CRUD interface.
     *
     * This method sets custom labels and icons for actions such as New, Save, Edit, and Delete
     * and adds custom actions for moving sections up, down, to the top, and to the bottom.
     *
     * @param Actions $actions The actions configuration object.
     * @return Actions The modified actions configuration object.
     */
    public function configureActions(Actions $actions): Actions
    {
        $entityCount = $this->informationSectionRepository->count([]);

        // Define custom actions for moving sections
        $moveTop = Action::new('moveTop', false, 'fa fa-arrow-up')
            ->setHtmlAttributes(['title' => 'Mettre en haut de page'])
            ->linkToCrudAction('moveTop')
            ->displayIf(fn ($entity) => $entity->getPositionInformationSection() > 0);
    
        $moveUp = Action::new('moveUp', false, 'fa fa-sort-up')
            ->setHtmlAttributes(['title' => 'Monter d\'un cran'])
            ->linkToCrudAction('moveUp')
            ->displayIf(fn ($entity) => $entity->getPositionInformationSection() > 0);
    
        $moveDown = Action::new('moveDown', false, 'fa fa-sort-down')
            ->setHtmlAttributes(['title' => 'Descendre d\'un cran'])
            ->linkToCrudAction('moveDown')
            ->displayIf(fn ($entity) => $entity->getPositionInformationSection() < $entityCount - 1);
    
        $moveBottom = Action::new('moveBottom', false, 'fa fa-arrow-down')
            ->setHtmlAttributes(['title' => 'Mettre en bas de page'])
            ->linkToCrudAction('moveBottom')
            ->displayIf(fn ($entity) => $entity->getPositionInformationSection() < $entityCount - 1);

        // Add the custom actions to the actions configuration and customize existing actions.
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

    /**
     * Configures the CRUD interface for the InformationSection entity.
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
        ->setEntityLabelInSingular('Section d\'information')
        ->setEntityLabelInPlural('Section d\'information')
        ->setPageTitle('new', 'Ajouter une nouvelle section')
        ->setDefaultSort(['positionInformationSection' => 'ASC'])
        ->showEntityActionsInlined();
    }

    /** 
     * Configures the fields displayed in the CRUD interface for the InformationSection entity.
     *
     * This method defines the fields to be displayed in the index, detail, edit, and new pages.
     *
     * @param string $pageName The name of the page being configured (e.g., 'index', 'new', 'edit').
     * @return iterable An iterable collection of field configurations.
     */
    public function configureFields(string $pageName): iterable
    {
        return [
            IntegerField::new('idInformationSection', 'Identifiant')->onlyOnIndex(),
            IntegerField::new('positionInformationSection', 'Position')->onlyOnIndex(),
            TextField::new('sectionLabel',($pageName === Crud::PAGE_INDEX ? 'Section' : 'Section (partie dans la page informations)'))
                ->setFormTypeOptions([
                    'attr' => ['placeholder' => 'Saississez le nom de la section dans l\'administration'],
                ]),
            TextField::new('titleInformationSection',($pageName === Crud::PAGE_INDEX ? 'Titre' : 'Titre dans la page informations'))
                ->setFormTypeOptions(['attr' => ['placeholder' => 'Saississez le titre de l\'information dans le site'],
            ]),
            TextareaField::new('contentInformationSection',($pageName === Crud::PAGE_INDEX ? 'Sous-texte' : 'Sous-texte de la section dans la page Informations'))
                ->setFormTypeOptions([
                    'attr' => ['placeholder' => 'Saississez le contenu de l\'information'],
                ]),
            DateTimeField::new('dateModificationInformationSection', 'Dernière modification')->onlyOnIndex(),
            TextField::new('userModificationInformationSection', 'Utilisateur')->onlyOnIndex(),
        ];
    }

    /**
     * Custom action to move a section to the top of the list.
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
     * Custom action to move a section up in the list.
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
     * Custom action to move a section down in the list.
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
     * Custom action to move a section to the bottom of the list.
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
     * Deletes the entity instance from the database.
     *
     * This method checks if the section is linked to any Information entities before allowing deletion.
     * If they are, it prevents deletion and sets an appropriate flash message.
     *
     * @param AdminContext $context The admin context containing the entity to delete.
     * @return mixed The result of the delete operation or a redirect response.
     */
    public function delete(AdminContext $context)
    {
        /** @var InformationSection $section */
        $section = $context->getEntity()->getInstance();

        // Verify if there are related items
        $hasRelatedItems = $this->entityManager->getRepository(Information::class)
            ->count(['sectionInformation' => $section]) > 0;

        // If there are related Information entities, prevent deletion and display an error message
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