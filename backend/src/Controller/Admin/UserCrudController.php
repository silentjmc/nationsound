<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class UserCrudController extends AbstractCrudController
{

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }
    

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
        ->addFormTheme('admin/form.html.twig')
        ->setEntityLabelInSingular('Utilisateur')
        ->setEntityLabelInPlural('Utilisateurs')
        ->setPageTitle('new', 'Ajouter un nouvel utilisateur')
        ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
        ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
            return $action->setLabel('Ajouter un utilisateur');
        })
        ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, function (Action $action) {
            return $action->setLabel('Créer l\'utilisateur');
        })
        ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, function (Action $action) {
            return $action->setLabel('Créer et ajouter un autre utilisateur');
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

    public function configureFields(string $pageName): iterable
    {
        $fields = [];
        if ($pageName === Crud::PAGE_INDEX) { 
            $fields = [
                TextField::new('email','Email'),
                TextField::new('lastname','Nom'),
                TextField::new('firstname','Prénom'),
                TextField::new('role','Rôle'),
                BooleanField::new('isVerified','Utilisateur vérifié')->renderAsSwitch(false)];

        } else {    
        $fields = [
            EmailField::new('email','Email')
                ->setFormTypeOptions([
                    'attr' => [
                        'placeholder' => 'Saisissez l\'email de l\'utilisateur'
                    ],
                ]),
            TextField::new('lastname','Nom')
                ->setFormTypeOptions([
                    'attr' => [
                        'placeholder' => 'Saisissez le nom de l\'utilisateur'
                    ],
                ]),
            TextField::new('firstname','Prénom')
                ->setFormTypeOptions([
                    'attr' => [
                        'placeholder' => 'Saisissez le prénom de l\'utilisateur'
                    ],
                ]),
            AssociationField::new('role','Rôle')
                ->setFormTypeOption('placeholder', 'Choisissez le rôle de l\'utilisateur')
                ->setFormTypeOption('choice_label', 'role'),
            BooleanField::new('isVerified','Utilisateur vérifié'),
        ];}
        return $fields;
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var User $user */
        $user = $entityInstance;
        
        // Retrieve the old role before modification
        $originalUser = $entityManager->getUnitOfWork()->getOriginalEntityData($user);
        $originalRole = $originalUser['role'];
        $originalIsVerified = $originalUser['isVerified'];

        if ($originalRole->getRole() === 'Administrateur') {
            $adminCount = $entityManager->getRepository(User::class)->count(['role' => $originalRole]);

            if ($adminCount <= 1) {
                $hasChanged = false;

                if ($user->getRole()->getRole() !== 'Administrateur') {
                    $user->setRole($originalRole);
                    $hasChanged = true;
                }

                if ($originalIsVerified && !$user->isVerified()) {
                    $user->setIsVerified(true);
                    $hasChanged = true;
                }

                if ($hasChanged) {
                    $this->addFlash(
                        'danger',
                        'Impossible de modifier le rôle ou le statut de vérification du dernier administrateur. Veuillez d\'abord créer un autre administrateur.'
                    );
                    return;
                }
            }
        }
        parent::updateEntity($entityManager, $entityInstance);
    }

    public function delete(AdminContext $context)
    {
        /** @var User $user */
        $principal = $context->getEntity()->getInstance();
        $userRole = $principal->getRole();

        $hasRelatedItems = $this->entityManager->getRepository(User::class)
        ->count(['role' => $principal]) > 0;

        if ($hasRelatedItems && $userRole->getRole() === 'Administrateur' ) {
            $this->addFlash('danger', 'Impossible de supprimer cet utilisateur car c\'est le dernier administrateur du système. Veuillez d\'abord créer un autre administrateur.');
            
            $url = $this->container->get(AdminUrlGenerator::class)
                ->setController(self::class)
                ->setAction(Action::INDEX)
                ->generateUrl();

            return $this->redirect($url);
        }
        return parent::delete($context);
    }
}