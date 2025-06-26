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

/*
 * UserCrudController is responsible for managing user entities in the admin panel.
 * It extends AbstractCrudController to provide CRUD operations for User entities.
 * It includes custom configurations for fields, actions, and entity updates.
 */
class UserCrudController extends AbstractCrudController
{
    private EntityManagerInterface $entityManager;
    
    /**
     * UserCrudController constructor.
     *
     * Initializes the controller with the EntityManagerInterface.
     *
     * @param EntityManagerInterface $entityManager The Doctrine entity manager.
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * Returns the fully qualified class name of the entity managed by this controller.
     *
     * @return string The fully qualified class name of the User entity.
     */
    public static function getEntityFqcn(): string
    {
        return User::class;
    }
    
    /**
     * Configures the CRUD settings for the User entity.
     *
     * This method sets the form theme, entity labels, page titles, and inlined actions.
     *
     * @param Crud $crud The CRUD configuration object.
     * @return Crud The configured CRUD object.
     */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
        ->addFormTheme('admin/form.html.twig')
        ->setEntityLabelInSingular('Utilisateur')
        ->setEntityLabelInPlural('Utilisateurs')
        ->setPageTitle('new', 'Ajouter un nouvel utilisateur')
        ->showEntityActionsInlined();
    }

    /**
     * Configures the actions available in the CRUD interface.
     *
     * This method customizes the edit and delete actions for the index page,
     * removing the new action to prevent creating new users from the index.
     *
     * @param Actions $actions The actions configuration object.
     * @return Actions The configured actions object.
     */
    public function configureActions(Actions $actions): Actions
    {
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
        ->remove(Crud::PAGE_INDEX, Action::NEW);   
    }

    /**
     * Configures the fields displayed in the CRUD interface.
     *
     * This method defines the fields for both index and detail pages,
     * including email, name, role, and verification status.
     *
     * @param string $pageName The name of the page being configured (index or detail).
     * @return iterable An iterable collection of field configurations.
     */
    public function configureFields(string $pageName): iterable
    {
        $fields = [];
        if ($pageName === Crud::PAGE_INDEX) { 
            $fields = [
                TextField::new('email','Email'),
                TextField::new('lastname','Nom'),
                TextField::new('firstname','Prénom'),
                TextField::new('roleUser','Rôle'),
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
            AssociationField::new('roleUser','Rôle')
                ->setFormTypeOption('placeholder', 'Choisissez le rôle de l\'utilisateur')
                ->setFormTypeOption('choice_label', 'role'),
            BooleanField::new('isVerified','Utilisateur vérifié'),
        ];}
        return $fields;
    }

    /**
     * Updates the entity instance before persisting it to the database.
     *
     * This method checks if the user is the last administrator and prevents changes
     * to their role or verification status if they are. It also sets appropriate flash messages.
     *
     * @param EntityManagerInterface $entityManager The Doctrine entity manager.
     * @param mixed $entityInstance The entity instance being updated.
     */
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $user = $entityInstance;
        
        // Retrieve the original state of the user before any modifications from the current form submission.
        $originalUser = $entityManager->getUnitOfWork()->getOriginalEntityData($user);
        $originalRole = $originalUser['roleUser'];
        $originalIsVerified = $originalUser['isVerified'];

        // Check if the user is the last administrator.
        if ($originalRole->getRole() === 'Administrateur') {
            $adminCount = $entityManager->getRepository(User::class)->count(['role' => $originalRole]);

            if ($adminCount <= 1) {
                $hasChanged = false;

                if ($user->getRoleUser()->getRole() !== 'Administrateur') {
                    $user->setRoleUser($originalRole);
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

    /**
     * Deletes the entity instance from the database.
     *
     * This method checks if the user is the last administrator before allowing deletion.
     * If they are, it prevents deletion and sets an appropriate flash message.
     *
     * @param AdminContext $context The admin context containing the entity to delete.
     * @return mixed The result of the delete operation or a redirect response.
     */
    public function delete(AdminContext $context)
    {
        $principal = $context->getEntity()->getInstance();
        $userRole = $principal->getRoleUser();

        // Check if the user has related items (other users with the same role).
        $hasRelatedItems = $this->entityManager->getRepository(User::class)
        ->count(['roleUser' => $principal]) > 0;

        // If the user is the last administrator, prevent deletion and set a flash message.
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