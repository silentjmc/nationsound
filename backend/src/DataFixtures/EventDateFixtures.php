<?php

namespace App\DataFixtures;

use App\Entity\EventDate;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class EventDateFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $dates = [
            [
                'date' => '2025-08-01',
                'dateModification' => '2024-11-28 17:09:25',
                'userModification' => 'Maël Lemaitre'
            ],
            [
                'date' => '2025-08-02',
                'dateModification' => '2024-11-28 17:09:36',
                'userModification' => 'Maël Lemaitre'
            ],
            [
                'date' => '2025-08-03',
                'dateModification' => '2024-11-28 17:09:44',
                'userModification' => 'Maël Lemaitre'
            ],
        ];

        foreach ($dates as $dateData) {
            $eventDate = new EventDate();
            $eventDate->setDate(new \DateTime($dateData['date']));
            $eventDate->setDateModification(new \DateTime($dateData['dateModification']));
            $eventDate->setUserModification($dateData['userModification']);
            
            $manager->persist($eventDate);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
        ];
    }
}
