<?php

namespace App\Controller\Admin;

use App\Entity\Partner;
use App\Entity\PartnerType;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

/**
 * PartnerTypeCrudController is responsible for managing PartnerType entities in the admin panel.
 * It extends AbstractCrudController to provide CRUD operations for PartnerType entities.
 * It includes custom configurations for fields, actions, and entity updates.
 */
class PartnerTypeCrudController extends AbstractCrudController
{
    private EntityManagerInterface $entityManager;

    /**
     * PartnerTypeCrudController constructor.
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
     * @return string The fully qualified class name of the PartnerType entity.
     */
    public static function getEntityFqcn(): string
    {
        return PartnerType::class;
    }

    /**
     * Configures the actions available for the PartnerType entity.
     *
     * This method sets custom labels and icons for actions such as New, Save, Edit, and Delete.
     *
     * @param Actions $actions The actions configuration object.
     * @return Actions The configured actions object.
     */
    public function configureActions(Actions $actions): Actions
    {
    return $actions
        ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
            return $action->setLabel('Ajouter un type de partenaire');
        })
        ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, function (Action $action) {
            return $action->setLabel('Créer le type de partenaire');
        })
        ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, function (Action $action) {
            return $action->setLabel('Créer et ajouter un autre type de partenaire');
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
     * Configures the CRUD settings for the PartnerType entity.
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
        ->setEntityLabelInSingular('Type de partenaire')
        ->setEntityLabelInPlural('Type de partenaires')
        ->setPageTitle('new', 'Ajouter un nouveau type de partenaire')
        ->showEntityActionsInlined();
    }
    
    /**
     * Configures the fields displayed in the CRUD interface for the PartnerType entity.
     *
     * This method defines the fields to be displayed in the index, detail, edit, and new pages.
     *
     * @param string $pageName The name of the page being configured (index, detail, edit, new).
     * @return iterable An iterable collection of field configurations.
     */
    public function configureFields(string $pageName): iterable
    {
        return [
            IntegerField::new('idPartnerType', 'Identifiant')->onlyOnIndex(),
            TextField::new('titlePartnerType', 'Type de partenaire')
                ->setFormTypeOptions([
                    'attr' => ['placeholder' => 'Saississez le type du partenaire'],
                ]),
            DateTimeField::new('dateModificationPartnerType', 'Dernière modification')->onlyOnIndex(),
            TextField::new('userModificationPartnerType', 'Utilisateur')->onlyOnIndex(),
        ];
    }

    /**
     * Deletes a PartnerType entity.
     *
     * This method checks if there are any related Partner entities before allowing deletion.
     * If there are related Partner entities, it prevents deletion and displays an error message.
     *
     * @param AdminContext $context The admin context containing the entity to delete.
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function delete(AdminContext $context)
    {
        $partnerType = $context->getEntity()->getInstance();

        // Check if there are any related Partner entities before allowing deletion
        $hasRelatedItems = $this->entityManager->getRepository(Partner::class)
            ->count(['typePartner' => $partnerType]) > 0;

        // If there are related Partner entities, prevent deletion and display an error message
        if ($hasRelatedItems) {
            $this->addFlash('danger', 'Impossible de supprimer cet élément car il est lié à un ou plusieurs éléments Partenaires. il faut d\'abord supprimer ou reaffecter les éléméents Partenaires concernés'); 
            $url = $this->container->get(AdminUrlGenerator::class)
                ->setController(self::class)
                ->setAction(Action::INDEX)
                ->generateUrl();

            return $this->redirect($url);
        }
        return parent::delete($context);
    }
}