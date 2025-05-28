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

class NewsCrudController extends AbstractCrudController
{
    private EntityManagerInterface $entityManager;
    private PublishService $publishService;
    public function __construct(EntityManagerInterface $entityManager, PublishService $publishService)
    {
        $this->entityManager = $entityManager;
        $this->publishService = $publishService;
    }
    public static function getEntityFqcn(): string
    {
        return News::class;
    }
    
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

    public function configureActions(Actions $actions): Actions
    {
        // New actions
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
            ->add(Crud::PAGE_INDEX,$publishAction) 
            ->add(Crud::PAGE_INDEX,$unpublishAction)
            ->add(Crud::PAGE_INDEX, $sendNotification)
            ->add(Crud::PAGE_INDEX, $unsendNotification);   
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
        ->setEntityLabelInSingular('Actualité')
        ->setEntityLabelInPlural('Actualités')
        ->setPageTitle('new', 'Ajouter une actualité')
        ->showEntityActionsInlined();
    }

    public function sendNotification(AdminContext $context)
    {
        $news = $context->getEntity()->getInstance();
        $news->setPush(true);
        $this->entityManager->flush();
        $this->addFlash('success', 'Notification envoyée');
        $url = $this->container->get(AdminUrlGenerator::class)->setAction(Action::INDEX)->setController(self::class)->generateUrl();
        return $this->redirect($url);
    }

    public function unsendNotification(AdminContext $context)
    {
        $news = $context->getEntity()->getInstance();
        $news->setPush(false);
        $this->entityManager->flush();
        $this->addFlash('success', 'Notification annulée');
        $url = $this->container->get(AdminUrlGenerator::class)->setAction(Action::INDEX)->setController(self::class)->generateUrl();
        return $this->redirect($url);
    }

    public function publish(AdminContext $context): Response
    {
        $result = $this->publishService->publish($context);
        $url = $result['url'];
        $this->addFlash('success', 'Actualité publié avec succès');
        return $this->redirect($url);
    }

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