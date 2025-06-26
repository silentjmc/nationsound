<?php

namespace App\Controller\Admin;

use App\Entity\News;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Symfony\Component\HttpFoundation\Response;
use App\Service\PublishService;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

/*
 * NewsCrudController is responsible for managing the CRUD  operations of News entities.
 * It extends AbstractCrudController to leverage EasyAdmin's functionality.
 * 
 * this controller customizes the default CRUD operations for News, including:
 * - Configuration of fields displayed in forms and index pages.
 * 
 * 
 */
class NewsCrudController extends AbstractCrudController
{
    private EntityManagerInterface $entityManager;
    private PublishService $publishService;

    /**
     * NewsCrudController constructor.
     *
     * Initializes the controller with the necessary services.
     *
     * @param EntityManagerInterface $entityManager The Doctrine entity manager.
     * @param PublishService $publishService The service responsible for publishing news.
     */
    public function __construct(EntityManagerInterface $entityManager, PublishService $publishService)
    {
        $this->entityManager = $entityManager;
        $this->publishService = $publishService;
    }

    /**
     * Returns the fully qualified class name of the entity managed by this controller.
     * 
     * This method is used by EasyAdmin to determine which entity this controller is responsible for.
     *
     * @return string The fully qualified class name of the News entity.
     */
    public static function getEntityFqcn(): string
    {
        return News::class;
    }
    
    /**
     * Configures the fields displayed in the CRUD interface for the News entity.
     *
     * This method defines the fields to be displayed in the index, detail, edit, and new pages.
     *
     * @param string $pageName The name of the page being configured (e.g., 'index', 'edit', 'new').
     * @return iterable An iterable collection of field configurations.
     */
    public function configureFields(string $pageName): iterable
    {
        return [
            IntegerField::new('idNews', 'Id')->onlyOnIndex(),
            ChoiceField::new('typeNews', 'Type')
                ->setFormTypeOptions([
                    'attr' => ['placeholder' => 'Saississez le type de l\'actualité'],
                    ])
                ->setChoices([
                    'Normal' => 'primary',
                    'Important' => 'warning',
                    'Urgent' => 'danger'
                ]),
            TextField::new('titleNews','Titre')
                ->setFormTypeOptions([
                    'attr' => ['placeholder' => 'Saississez le titre de l\'actualité'],
                ]),
            TextareaField::new('contentNews','Contenu')->hideOnIndex(),
            BooleanField::new('publishNews','Publier')->onlyOnIndex()->renderAsSwitch(false),
            BooleanField::new('publishNews','Publier')->HideOnIndex()->renderAsSwitch(true),
            BooleanField::new('push','Notifier ?')->onlyOnIndex()->renderAsSwitch(false),
            BooleanField::new('push','Notifier ?')->HideOnIndex()->renderAsSwitch(true)
            ->setHelp("Seule la dernière actualité notifiée sera affichée sur l'application"),
            DateTimeField::new('notificationDate', 'Date de notification')->hideOnForm(),
            DateField::new('notificationEndDate', 'Fin de notification')
            ->setHelp('Date de fin d\'affichage de la notification (optionnel)')
            ->setRequired(false),
            DateTimeField::new('dateModificationNews','Date de modification')->hideOnForm(),
            TextField::new('userModificationNews','Utilisateur')->hideOnForm(),
        ];
    }

    /**
     * Configures the actions available in the CRUD interface.
     *
     * This method sets custom labels and icons for actions such as New, Save, Edit, and Delete
     * and adds custom actions for sending and unsending notifications,
     * publishing and unpublishing news.
     * It also configures publish and unpublish actions with specific conditions for display.
     *
     * @param Actions $actions The actions configuration object.
     * @return Actions The configured actions object.
     */
    public function configureActions(Actions $actions): Actions
    {
        // Define custom actions for sending and unsending notifications
        $sendNotification = Action::new('sendNotification', 'Envoyer la notification', 'fa fa-bell')
            ->linkToCrudAction('sendNotification')
            ->addCssClass('btn btn-sm btn-light')
            ->setLabel(false)
            ->displayIf(fn ($entity) => !$entity->isPush() && $entity->isPublishNews())
            ->setHtmlAttributes([
            'title' => "Envoyer la notificationn",
        ]);

        $unsendNotification = Action::new('unsendNotification', 'Annuler la notification', 'fa fa-bell-slash')
            ->linkToCrudAction('unsendNotification')
            ->addCssClass('btn btn-sm btn-light')
            ->setLabel(false)
            ->displayIf(fn ($entity) => $entity->isPush())        
            ->setHtmlAttributes([
            'title' => "Annuler la notification",
        ]);

        // Define custom actions for publishing and unpublishing news
        $publishAction = Action::new('publish', 'Publier', 'fa fa-eye')
        ->addCssClass('btn btn-sm btn-light text-success')
        ->setLabel(false)
        ->displayIf(fn ($entity) => !$entity->isPublishNews())
        ->linkToCrudAction('publish')
        ->setHtmlAttributes([
            'title' => "Publier l'élément",
        ]);
        $unpublishAction = Action::new('unpublish', 'Dépublier', 'fa fa-eye-slash')
        ->addCssClass('btn btn-sm btn-light text-danger')
        ->setLabel(false)
        ->displayIf(fn ($entity) => $entity->isPublishNews())
        ->linkToCrudAction('unpublish')
        ->setHtmlAttributes([
            'title' => "Dépublier l'élément",       
        ]);

        // Add the custom actions to the actions configuration and customize existing actions.
        return $actions
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
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
            return $action->setLabel('Ajouter une actualité');
            })
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action->setLabel('Créer l\'actualité');
            })
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, function (Action $action) {
                return $action->setLabel('Créer et ajouter une autre actualité');
            })
            ->add(Crud::PAGE_INDEX,$publishAction) 
            ->add(Crud::PAGE_INDEX,$unpublishAction)
            ->add(Crud::PAGE_INDEX, $sendNotification)
            ->add(Crud::PAGE_INDEX, $unsendNotification);   
    }

    /**
     * Configures the CRUD interface for the News entity.
     *
     * This method sets the form theme, entity labels, page titles, and inlined actions.
     *
     * @param Crud $crud The CRUD configuration object.
     * @return Crud The configured CRUD object.
     */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
        ->setEntityLabelInSingular('Actualité')
        ->setEntityLabelInPlural('Actualités')
        ->setPageTitle('new', 'Ajouter une actualité')
        ->showEntityActionsInlined();
    }

    /**
     * Custom action to send a notification for the selected news entity.
     * 
     * This method sets the push flag to true, indicating that the notification should be sent,
     * and then flushes the changes to the database.
     * It also adds a success flash message and redirects to the index page.
     *
     * @param AdminContext $context The context of the admin action.
     * @return Response A redirect response to the index page.
     */
    public function sendNotification(AdminContext $context)
    {
        $news = $context->getEntity()->getInstance();
        $news->setPush(true);
        $this->entityManager->flush();
        $this->addFlash('success', 'Notification envoyée');
        $url = $this->container->get(AdminUrlGenerator::class)->setAction(Action::INDEX)->setController(self::class)->generateUrl();
        return $this->redirect($url);
    }

    /**
     * Custom action to unsend a notification for the selected news entity.
     * 
     * This method sets the push flag to false, indicating that the notification should be cancelled,
     * and then flushes the changes to the database.
     * It also adds a success flash message and redirects to the index page.
     *
     * @param AdminContext $context The context of the admin action.
     * @return Response A redirect response to the index page.
     */
    public function unsendNotification(AdminContext $context)
    {
        $news = $context->getEntity()->getInstance();
        $news->setPush(false);
        $this->entityManager->flush();
        $this->addFlash('success', 'Notification annulée');
        $url = $this->container->get(AdminUrlGenerator::class)->setAction(Action::INDEX)->setController(self::class)->generateUrl();
        return $this->redirect($url);
    }

    /**
     * Custom action to publish the News.
     * 
     * This method uses the PublishService to handle the publishing logic
     * and redirects back to the index page with a success flash message.
     *
     * @param AdminContext $context The context of the admin action.
     * @return Response A redirect response to the index page with a success flash message.
     */
    public function publish(AdminContext $context): Response
    {
        $result = $this->publishService->publish($context);
        $url = $result['url'];
        $this->addFlash('success', 'Actualité publié avec succès');
        return $this->redirect($url);
    }

    /**
     * Custom action to unpublish the News.
     * 
     * This method uses the PublishService to handle the unpublishing logic
     * and redirects back to the index page with a success flash message.
     *
     * @param AdminContext $context The admin context containing the entity to unpublish.
     * @return Response A redirect response to the index page with a success flash message.
     */
    public function unpublish(AdminContext $context): Response
    {
        $wasPushEnabledBeforeServiceCall = $context->getEntity()->getInstance()->isPush();
        $result = $this->publishService->unpublish($context);
        $url = $result['url'];
        if ($wasPushEnabledBeforeServiceCall) {
            $this->addFlash('success', 'Actualité dépublié avec succès et la notification est annulée');  
        } else {
            $this->addFlash('success', 'Actualité dépublié avec succès');
        }    
        return $this->redirect($url);
    }
}