<?php

namespace App\Tests\Fixtures;

use App\Entity\User;
use App\Entity\Role; // Make sure to import the Role entity
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface; // Import this

class TestUserFixtures extends Fixture implements DependentFixtureInterface // Implement DependentFixtureInterface
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Get the 'Administrateur' role created by RoleFixtures
        // Make sure the string 'admin_role' matches the reference name in RoleFixtures
        /** @var Role $adminRole */
        $adminRole = $this->getReference(TestRoleFixtures::ADMIN_ROLE_REFERENCE); // Use the reference name from RoleFixtures

        // Create an admin user
        $adminUser = new User();
        $adminUser->setEmail('admin@email.com');
        $adminUser->setFirstname('Admin'); // Assuming these are required fields
        $adminUser->setLastname('User');   // Assuming these are required fields
        $adminUser->setPassword(
            $this->passwordHasher->hashPassword(
                $adminUser,
                'password' // Or a dynamic password
            )
        );
        $adminUser->setRoleUser($adminRole); // THIS IS THE CRUCIAL PART: Assign the Role object
        $adminUser->setIsVerified(true);
        $manager->persist($adminUser);
        $this->addReference('user_admin', $adminUser); // Add a reference for the admin user

        // Create other users if needed, ensuring they also have a role
        // For example, a "user" role:
        // /** @var Role $userRole */
        // $userRole = $this->getReference(RoleFixtures::USER_ROLE_REFERENCE); // Assuming you have this
        // $regularUser = new User();
        // $regularUser->setEmail('user@email.com');
        // $regularUser->setFirstname('Regular');
        // $regularUser->setLastname('User');
        // $regularUser->setPassword($this->passwordHasher->hashPassword($regularUser, 'password'));
        // $regularUser->setRoleUser($userRole);
        // $manager->persist($regularUser);
        // $this->addReference('user_regular', $regularUser);

        $manager->flush();
    }

    /**
     * This method tells DoctrineFixturesBundle that UserFixtures depends on RoleFixtures.
     * RoleFixtures will be loaded BEFORE UserFixtures.
     */
    public function getDependencies(): array
    {
        return [
            TestRoleFixtures::class,
        ];
    }
}