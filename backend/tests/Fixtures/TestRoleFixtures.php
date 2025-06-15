<?php

namespace App\Tests\Fixtures;

use App\Entity\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TestRoleFixtures extends Fixture
{
    public const ADMIN_ROLE_REFERENCE = 'admin_role';
    public const USER_ROLE_REFERENCE = 'user_role'; // If you have other roles

    public function load(ObjectManager $manager): void
    {
        $adminRole = new Role();
        $adminRole->setRole('Administrateur');
        $manager->persist($adminRole);
        $this->addReference(self::ADMIN_ROLE_REFERENCE, $adminRole); // Define the reference

        // If you have other roles
        // $userRole = new Role();
        // $userRole->setRole('Utilisateur');
        // $manager->persist($userRole);
        // $this->addReference(self::USER_ROLE_REFERENCE, $userRole);

        $manager->flush();
    }
}