<?php

namespace App\DataFixtures;

use App\Entity\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class RoleFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $roles = [
            'Administrateur',
            'Commercial',
            'Marketing',
            'Rédacteur',
            'En attente',
        ];

        foreach ($roles as $name) {
            $role = new Role();
            $role->setRole($name);
            $manager->persist($role);
        }

        $manager->flush();
    }
}
