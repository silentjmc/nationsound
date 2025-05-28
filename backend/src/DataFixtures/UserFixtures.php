<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $users = [
            [
                'email' => 'admin@email.com',
                'password' => '$2y$13$9JiOO7W0UTSX.neJnq7VAu7QD5nALSv514nzU7fnilJr4OOAejEvC',
                'lastname' => 'Lemaitre',
                'firstname' => 'Maël',
                'roleId' => 1,
                'isVerified' => true,
                'registrationDate' => null
            ],
            [
                'email' => 'commercial@email.com',
                'password' => '$2y$13$mPWkfIteR6It4s7Ggt5Uw.i3cqU7zP/e6U9VTR.pFQCcGCZskfBA2',
                'lastname' => 'Durand',
                'firstname' => 'Jean',
                'roleId' => 2,
                'isVerified' => true,
                'registrationDate' => '2024-11-28 14:28:10'
            ],
            [
                'email' => 'marketing@email.com',
                'password' => '$2y$13$0N9ZfloWageZVPnX4gc4WO/UYl001/EFFSqDgzoKevgLZEk9lyHn.',
                'lastname' => 'Dupond',
                'firstname' => 'Alain',
                'roleId' => 3,
                'isVerified' => true,
                'registrationDate' => '2024-11-28 14:37:39'
            ],
            [
                'email' => 'redacteur@email.com',
                'password' => '$2y$13$rP7buGAHi4ZhN/Z14DdskOmHdttnhs2YVmEQTf1vKD2lPqxBgqufS',
                'lastname' => 'Martin',
                'firstname' => 'Michel',
                'roleId' => 4,
                'isVerified' => true,
                'registrationDate' => '2024-11-28 14:50:43'
            ],
        ];

        foreach ($users as $userData) {
            $user = new User();
            $user->setEmail($userData['email']);
            $user->setPassword($userData['password']);
            $user->setLastname($userData['lastname']);
            $user->setFirstname($userData['firstname']);
            $user->setIsVerified($userData['isVerified']);
            
            // Récupérer le rôle correspondant
            $role = $manager->getRepository(Role::class)->find($userData['roleId']);
            if ($role) {
                $user->setRoleUser($role);
            }

            // Gérer la date d'inscription
            if ($userData['registrationDate']) {
                $user->setRegistrationDate(new \DateTime($userData['registrationDate']));
            }

            $manager->persist($user);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            RoleFixtures::class,
        ];
    }
}
