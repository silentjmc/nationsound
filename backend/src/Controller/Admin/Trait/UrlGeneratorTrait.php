<?php

namespace App\Controller\Admin\Trait;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

trait UrlGeneratorTrait
{
    private AdminUrlGenerator $adminUrlGenerator;

    public function __construct(AdminUrlGenerator $adminUrlGenerator)
    {
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    protected function addUrl(string $controllerClass): string
    {
        return $this->adminUrlGenerator
            ->setController($controllerClass)
            ->setAction(Action::NEW)
            ->generateUrl();
    }
}