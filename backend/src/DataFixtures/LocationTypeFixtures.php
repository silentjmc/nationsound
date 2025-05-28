<?php

namespace App\DataFixtures;

use App\Entity\LocationType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class LocationTypeFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $types = [
            [
                'type' => 'Scènes',
                'symbol' => 'musice3afc4c99e4dee432ca4ae6813b7b9bd56c4ade6.png',
                'dateModification' => '2024-11-28 17:27:10',
                'userModification' => 'Maël Lemaitre'
            ],
            [
                'type' => 'Point Secours',
                'symbol' => 'hospital65c0a591bcca78927c598f4cffd629998aef4c61.png',
                'dateModification' => '2024-11-28 17:27:45',
                'userModification' => 'Maël Lemaitre'
            ],
            [
                'type' => 'Restaurations',
                'symbol' => 'food72e1d68ad56d62899f1af8c80daf17715e3ae4f8.png',
                'dateModification' => '2024-11-28 17:28:24',
                'userModification' => 'Maël Lemaitre'
            ],
            [
                'type' => 'Toilettes',
                'symbol' => 'wc71a20400b6479cbd7dd42e0f13de8972857a6afd.png',
                'dateModification' => '2024-11-28 17:28:37',
                'userModification' => 'Maël Lemaitre'
            ],
            [
                'type' => 'Pavillons',
                'symbol' => 'house7e746f4331ae34ec989e1f4a6c589b70855946d0.png',
                'dateModification' => '2024-11-28 17:28:55',
                'userModification' => 'Maël Lemaitre'
            ],
        ];

        foreach ($types as $typeData) {
            $locationType = new LocationType();
            $locationType->setNameLocationType($typeData['type']);
            $locationType->setSymbol($typeData['symbol']);
            $locationType->setDateModificationLocationType(new \DateTime($typeData['dateModification']));
            $locationType->setUserModificationLocationType($typeData['userModification']);
            
            $manager->persist($locationType);
        }

        $manager->flush();
    }
/*
    public function getDependencies()
    {
        return [
            UserFixtures::class,
        ];
    }*/
}
