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
//use App\Service\PushyService;
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
    //private PushyService $pushyService;
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
            IntegerField::new('id', 'Id')->onlyOnIndex(),
            ChoiceField::new('type', 'Type')
                ->setChoices([
                    'Normal' => 'primary',
                    'Important' => 'warning',
                    'Urgent' => 'danger'
                ]),
            TextField::new('title','Titre'),
            TextareaField::new('content','Contenu')->hideOnIndex(),
            BooleanField::new('publish','Publier')->onlyOnIndex()->renderAsSwitch(false),
            BooleanField::new('publish','Publier')->HideOnIndex()->renderAsSwitch(true),
            BooleanField::new('push','Notifier ?')->onlyOnIndex()->renderAsSwitch(false),
            BooleanField::new('push','Notifier ?')->HideOnIndex()->renderAsSwitch(true)
            ->setHelp("Seule la dernière actualité notifiée sera affichée sur l'application"),
            DateTimeField::new('notificationDate', 'Date de notification')->hideOnForm(),
            DateField::new('notificationEndDate', 'Fin de notification')
            ->setHelp('Date de fin d\'affichage de la notification (optionnel)')
            ->setRequired(false),
            DateTimeField::new('dateModification','Date de modification')->hideOnForm(),
            TextField::new('userModification','Utilisateur')->hideOnForm(),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $sendNotification = Action::new('sendNotification', 'Envoyer la notification', 'fa fa-bell')
            ->linkToCrudAction('sendNotification')
            ->addCssClass('btn btn-sm btn-light')
            ->setLabel(false)
            ->displayIf(fn ($entity) => !$entity->isPush());

        $publishAction = Action::new('publish', 'Publier', 'fa fa-eye')
        ->addCssClass('btn btn-sm btn-light text-success')
        ->setLabel(false)
        ->displayIf(fn ($entity) => !$entity->isPublish())
        ->linkToCrudAction('publish')
        ->setHtmlAttributes([
            'title' => "Publier l'élément",
        ]);
        $unpublishAction = Action::new('unpublish', 'Dépublier', 'fa fa-eye-slash')
        ->addCssClass('btn btn-sm btn-light text-danger')
        ->setLabel(false)
        ->displayIf(fn ($entity) => $entity->isPublish())
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
                    //->addCssClass('btn btn-sm btn-light');
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
            ->add(Crud::PAGE_INDEX, $sendNotification);    
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
        //->setFormThemes(['admin/location_form.html.twig'])
        //->overrideTemplate('crud/index', 'admin/location_index.html.twig')
        ->setEntityLabelInSingular('Actualité')
        ->setEntityLabelInPlural('Actualités')
        ->setPageTitle('new', 'Ajouter une actualité')
        ->showEntityActionsInlined();
    }

    public function sendNotification(AdminContext $context)
    {
        $news = $context->getEntity()->getInstance();
        /*$data = [
            'title' => $news->getTitle(),
            'content' => $news->getContent(),
        ];
        $this->pushyService->sendNotification($data);*/

        $news->setPush(true);
        //$news->setNotificationDate(new \DateTime());
        //$this->entityManager->persist($news);
        $this->entityManager->flush();

        $this->addFlash('success', 'Notification envoyée');
/*
             $url = $this->generateUrl('admin', [
                'crudAction' => 'index',
                'crudControllerFqcn' => self::class,
            ]);*/
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
        $result = $this->publishService->unpublish($context);
        $url = $result['url'];
        //$hasRelatedItems = $result['hasRelatedItems'];
        //if ($hasRelatedItems) {
        //    $this->addFlash('success', 'FAQ et événements liés dépubliés avec succès');
        //} else {
            $this->addFlash('success', 'Actualité dépublié avec succès');
        //}
        
        return $this->redirect($url);
    }
}