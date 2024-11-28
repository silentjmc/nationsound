<?php

namespace App\DataFixtures;

use App\Entity\EventType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class EventTypeFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $types = [
            [
                'type' => 'Concert',
                'dateModification' => '2024-11-28 17:25:12',
                'userModification' => 'Maël Lemaitre'
            ],
            [
                'type' => 'Rencontre',
                'dateModification' => '2024-11-28 17:25:19',
                'userModification' => 'Maël Lemaitre'
            ],
        ];

        foreach ($types as $typeData) {
            $eventType = new EventType();
            $eventType->setType($typeData['type']);
            $eventType->setDateModification(new \DateTime($typeData['dateModification']));
            $eventType->setUserModification($typeData['userModification']);
            
            $manager->persist($eventType);
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
