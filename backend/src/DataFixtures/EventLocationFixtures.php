<?php

namespace App\DataFixtures;

use App\Entity\EventLocation;
use App\Entity\LocationType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class EventLocationFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $locations = [
            [
                'locationName' => 'Scène Electric Dreams',
                'latitude' => 48.60597440462200,
                'longitude' => 2.35025882720950,
                'description' => 'Scène Electro',
                'dateModification' => '2024-11-28 17:43:25',
                'userModification' => 'Maël Lemaitre',
                'publish' => true,
                'typeLocationId' => 1
            ],
            [
                'locationName' => 'Pompiers',
                'latitude' => 48.61108201882900,
                'longitude' => 2.35321998596190,
                'description' => 'Point Secours',
                'dateModification' => '2024-11-28 17:33:16',
                'userModification' => 'Maël Lemaitre',
                'publish' => false,
                'typeLocationId' => 2
            ],
            [
                'locationName' => 'Gourmet Groove',
                'latitude' => 48.60988186459800,
                'longitude' => 2.34639644622800,
                'description' => 'Food truck',
                'dateModification' => '2024-11-28 17:33:49',
                'userModification' => 'Maël Lemaitre',
                'publish' => false,
                'typeLocationId' => 3
            ],
            [
                'locationName' => 'Pavillon Bleu',
                'latitude' => 48.60548644968700,
                'longitude' => 2.32697725296020,
                'description' => 'Point Rencontre',
                'dateModification' => '2024-11-28 17:34:36',
                'userModification' => 'Maël Lemaitre',
                'publish' => false,
                'typeLocationId' => 5
            ],
            [
                'locationName' => 'Toilettes',
                'latitude' => 48.60338360040200,
                'longitude' => 2.34120368957520,
                'description' => 'W.C',
                'dateModification' => '2024-11-28 17:35:48',
                'userModification' => 'Maël Lemaitre',
                'publish' => false,
                'typeLocationId' => 4
            ],
            [
                'locationName' => 'Harmonic Haven',
                'latitude' => 48.59784527192100,
                'longitude' => 2.32447743415830,
                'description' => 'Scene World Music',
                'dateModification' => '2024-11-28 17:45:29',
                'userModification' => 'Maël Lemaitre',
                'publish' => true,
                'typeLocationId' => 1
            ],
        ];

        foreach ($locations as $locationData) {
            $eventLocation = new EventLocation();
            $eventLocation->setLocationName($locationData['locationName']);
            $eventLocation->setLatitude($locationData['latitude']);
            $eventLocation->setLongitude($locationData['longitude']);
            $eventLocation->setDescription($locationData['description']);
            $eventLocation->setDateModification(new \DateTime($locationData['dateModification']));
            $eventLocation->setUserModification($locationData['userModification']);
            $eventLocation->setPublish($locationData['publish']);
            
            // Récupérer le type de localisation correspondant
            $type = $manager->getRepository(LocationType::class)->find($locationData['typeLocationId']);
            if ($type) {
                $eventLocation->setTypeLocation($type);
            }
            
            $manager->persist($eventLocation);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LocationTypeFixtures::class,
        ];
    }
}
