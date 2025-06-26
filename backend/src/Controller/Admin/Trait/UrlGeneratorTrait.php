<?php

namespace App\Controller\Admin\Trait;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

/**
 * UrlGeneratorTrait provides a method to generate URLs for adding new entities in the admin panel.
 * It uses the AdminUrlGenerator to create URLs based on the specified controller class.
 */
trait UrlGeneratorTrait
{
    private AdminUrlGenerator $adminUrlGenerator;

    /**
     * UrlGeneratorTrait constructor.
     *
     * Initializes the trait with an instance of AdminUrlGenerator.
     *
     * @param AdminUrlGenerator $adminUrlGenerator The admin URL generator to be used for generating URLs.
     */
    public function __construct(AdminUrlGenerator $adminUrlGenerator)
    {
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    /**
     * Generates a URL for adding a new entity of the specified controller class.
     *
     * This method uses the AdminUrlGenerator to create a URL that points to the 'new' action
     * of the specified controller class.
     *
     * @param string $controllerClass The fully qualified class name of the controller.
     * @return string The generated URL for adding a new entity.
     */
    protected function addUrl(string $controllerClass): string
    {
        return $this->adminUrlGenerator
            ->setController($controllerClass)
            ->setAction(Action::NEW)
            ->generateUrl();
    }
}